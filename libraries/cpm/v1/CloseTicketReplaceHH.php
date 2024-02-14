<?php

/**
 * Skeleton CloseTicketReplaceHH cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_2 as RNCPHP;

require_once "ConnectUrl.php";

class CloseTicketReplaceHH
{
   
    public static function HandleIncident( $run_mode, $action, $incident, $n_cycles)
    {

      try
      {

        $asset = RNCPHP\Asset::first("SerialNumber = '" . $incident->CustomFields->c->id_hh . "'");
        //echo $incident->CustomFields->c->id_hh .'<br>';
        $incident_reemplazo=RNCPHP\Incident::first(" CustomFields.c.hh_replacement='" . $incident->CustomFields->c->id_hh ."' and  StatusWithType.Status.ID = 3 and Disposition.ID = 123");
        //$incident2=RNCPHP\Incident::first("StatusWithType.Status.ID = 3 and Disposition.ID = 123");
        //echo $incident2->CustomFields->c->hh_replacement.'<br>';
        //$str="CustomFields.c.hh_replacement='" . $incident->CustomFields->c->hh_replacement ."'";
        //echo $str .'<br>';
        //echo $incident2->CustomFields->c->id_hh .'-' . $incident2->CustomFields->c->hh_replacement;
        /*
        $incident->Save();
        */

        //$incident2 = RNCPHP\Incident::fetch($incident->ID);
        //$incident2->CustomFields->c->shipping_instructions='RESPUESTA6 [' . date("Y/m/d H:i:s")  .']';
        //$incident2->Save(RNCPHP\RNObject::SuppressAll);
        if($incident_reemplazo)
        {
          $a_request = new \stdClass();
          $a_request->hh = $incident_reemplazo->CustomFields->c->id_hh;
          $a_request->motivo = "Retiro de Equipo en falla";
          $a_request->instrucciones = "Retiro por cambio de Equipo";
          $a_request->comentarios = "Creado por API";
          $a_request->contacto =  $incident_reemplazo->PrimaryContact->Name->First . ' ' . $incident_reemplazo->PrimaryContact->Name->Last;
          $a_request->email = $incident_reemplazo->PrimaryContact->Emails[0]->Address;
          $a_request->telefono =   $incident_reemplazo->PrimaryContact->Phones[0]->Number;
          $a_request->aprobacion =  true;
          $a_request->ref_num =  $incident_reemplazo->ReferenceNumber;

          self::insertPrivateNote($incident,'ENVIO [' .  json_encode($a_request) .']');
          $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
          $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

          $url = $cfg2->Value."/sucursalVirtual/EnviaRetiroHH";
          self::insertPrivateNote($incident,'ENVIO [' . $url .']');
          $respuesta=self::crearRetiroHH($a_request);
          self::insertPrivateNote($incident,'RESPUESTA [' .  $respuesta .']');
        }
      }
      catch (RNCPHP\ConnectAPIError $err) 
      {
        
        $incident->CustomFields->c->shipping_instructions=$err->getMessage();
        $incident->Save(RNCPHP\RNObject::SuppressAll);
        self::insertPrivateNote($incident, "CPM Motor: " . $err->getMessage());
      }
    }
    static function crearRetiroHH($datosHH)
    {
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

        
       $jsonToken = ConnectUrl::geToken();
        if($jsonToken === FALSE)
            return FALSE;

        // Se encapsula token como array
        //$a_jsonToken = json_decode($jsonToken, TRUE);

        //if (empty($a_jsonToken["access_token"]))
        //    throw new \Exception("Json de token inválido {$jsonToken}", 1);
    
        $token = $jsonToken;
        try{
          $url = $cfg2->Value."/sucursalVirtual/EnviaRetiroHH";
  
          $jsonDataEncoded=json_encode($datosHH);

          $service = ConnectUrl::requestCURLJsonRaw($url, $jsonDataEncoded, $token);

          return $service;
      }
        catch (RNCPHP\ConnectAPIError $err )
        {
            $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }

    }
    static function insertPrivateNote($incident, $textoNP)
    {
        try
        {

           
          $incident->Threads = new RNCPHP\ThreadArray();
          $incident->Threads[0] = new RNCPHP\Thread();
          $incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
          $incident->Threads[0]->EntryType->ID = 1; // Used the ID here. See the Thread object for definition
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





}
