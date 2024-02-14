<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;

require_once "ConnectUrl.php";


class SendLead
{
    //URL de TEST:
    //CONST URL  = "http://api-test.dimacofi.cl/crm/1.0.1/salesApi/resources/leads";
    //CONST URL  = "http://api-test.dimacofi.cl/crm/1.0.1/salesApi/resources/leads";
    //URL de Producción:
    CONST URL = "https://api.dimacofi.cl/crm/salesApi/resources/leads";

    static function execute($runMode, $action, $incident, $cycle)
    {
      self::insertPrivateNote($incident, "incidente: ". $incident->ID);
        try
        {

          $description = "";
          if (count($incident->Threads) > 0)
          {
            foreach ($incident->Threads as $thread)
            {
              if ($thread->EntryType->ID === 3)
              {
                $description = $thread->Text;
                break;
              }
            }
          }

          $a_json                   = array();
          $a_json['title']          = "Contacto Comercial";
          $a_json['description']    = $description;
          $a_json['assignedArea']   = "SANTIAGO";
          $a_json['bussinessType']  = "VENTA_EQUIPOS";

          //información de contacto
          $a_json['contactName']    = $incident->PrimaryContact->Contact->Name->First." ".$incident->PrimaryContact->Contact->Name->Last;
          //
          $email                    = $incident->PrimaryContact->Emails[0]->Address;
          if (empty($email))
          $email                  = $incident->PrimaryContact->Login;

          $a_json['contactMail']    = $email;
          $a_json['contactPhone']   = $incident->PrimaryContact->Phones[0]->Number;
          $a_json['customerName']   = ($incident->PrimaryContact->Organization)?$incident->PrimaryContact->Organization->Name:'';
          $a_json['customerNumber'] = ($incident->PrimaryContact->Organization)?$incident->PrimaryContact->Organization->CustomFields->c->rut:'';
          //$a_json['ownerLead']      = "300000001312311";300000003143066
          $a_json['ownerLead']      = "300000003143066";
          $a_json['channelType']    = "WEB";
          $a_json['stateName']      = "REGION METROPOLITANA";
          $a_json['cityName']       = "Las Condes";
          $a_json['idticket']       = $incident->ID;

          $jsonDataEncoded = json_encode($a_json);
          /*Inicio Json enviado*/
		      self::insertPrivateNote($incident, "json enviado: ". $jsonDataEncoded);
          /*Fin Json enviado*/
          $tokenHeader = ConnectUrl::geToken();

          if ($tokenHeader === false)
          {
            self::insertPrivateNote($incident, "Error Token ".$message);
            return;
          }

          self::insertPrivateNote($incident, "Token ".$tokenHeader);

          $result = ConnectUrl::requestCURLJsonRaw(self::URL, $jsonDataEncoded, $tokenHeader);
          if ($result !== false)
          {
            self::insertPrivateNote($incident, "json recibido: ". $result);
            $incident->StatusWithType->Status->ID = 2; //solucionado
            $incident->Save();
          }
          else
          {
            $message = "ERROR: ".ConnectUrl::getResponseError();
            self::insertPrivateNote($incident, "Error Curl: ". $message);
          }

        }
        catch (RNCPHP\ConnectAPIError $err )
        {
             $message = "Error ".$e->getMessage();
             self::insertPrivateNote($incident, "Error Query: ".$message);
             self::insertBanner($incident, $bannerNumber);
        }

    }

    static function insertPrivateNote($incident, $textoNP)
    {
        try
        {
            $incident->Threads = new RNCPHP\ThreadArray();
            $incident->Threads[0] = new RNCPHP\Thread();
            $incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
            $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
            $incident->Threads[0]->Text = $textoNP;
            $incident->Save(RNCPHP\RNObject::SuppressAll);
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $incident->Subject = "Error" . $err->getMessage();
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            return false;
        }
    }

    static function insertBanner($incident, $typeBanner, $texto = '')
    {
        if (!is_numeric($typeBanner) and $typeBanner > 3 and $typeBanner < 0)
            $typeBanner = 1;

        $texto = '';
        if ($typeBanner == 3)
            $texto = "Error respuesta OM";

        $incident->Banner->Text = $texto;
        $incident->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
        $incident->Save(RNCPHP\RNObject::SuppressAll);

    }

}
