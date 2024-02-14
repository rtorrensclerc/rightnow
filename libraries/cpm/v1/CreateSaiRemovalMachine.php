<?php

/**
 * Skeleton OpportCreateSaiRemovalMachine cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;

require_once "ConnectUrl.php";

class CreateSaiRemovalMachine
{
   
    //URL de Test
    //CONST URL          = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    //URL de Producción:
    //CONST URL          = "http://190.14.56.27:8080//dts/rn_integracion/rntelejson.php";


    public static function HandleIncident( $incident)
    {

      try
      {
        
        $incident2 = RNCPHP\Incident::fetch($incident->ID);
        //$incident2->CustomFields->c->shipping_instructions='RESPUESTA6 [' . date("Y/m/d H:i:s")  .']';
        $incident2->Save(RNCPHP\RNObject::SuppressAll);
       
        $a_request = new \stdClass();
        $a_request->hh = $incident->CustomFields->c->hh_replacement;
        $a_request->motivo = "Termino Prestamo";
        $a_request->instrucciones = "Retiro Prestamo";
        $a_request->comentarios = "";
        $a_request->contacto =  $incident->PrimaryContact->Name->First . ' ' . $incident->PrimaryContact->Name->Last;
        $a_request->email = $incident->PrimaryContact->Emails[0]->Address;
        $a_request->telefono =   $incident->PrimaryContact->Phones[0]->Number;
        $a_request->aprobacion =  true;
        $a_request->ref_num =  $incident->ReferenceNumber;

        self::insertPrivateNote($incident,'ENVIO [' .  json_encode($a_request) .']');
        $respuesta=self::crearRetiroHH($a_request);
        self::insertPrivateNote($incident,'RESPUESTA [' .  $respuesta .']');
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
        catch (RNCPHP\ConnectAPIError $err)
        {
          $incident->CustomFields->c->shipping_instructions=$incident->CustomFields->c->shipping_instructions . 'Error';
          /*  $incident->Threads                   = new RNCPHP\ThreadArray();
            $incident->Threads[0]                = new RNCPHP\Thread();
            $incident->Threads[0]->EntryType     = new RNCPHP\NamedIDOptList();
            $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
            $incident->Threads[0]->Text          = "Error " . $err->getMessage();
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            */
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            return FALSE;
        }
    }





}
