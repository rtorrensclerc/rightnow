<?php

/**
 * Skeleton SaveTicketInstall cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;

require_once "ConnectUrl.php";

class SaveTicketInstall
{
   
    //URL de Test
    //CONST URL          = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    //URL de Producción:
    //CONST URL          = "http://190.14.56.27:8080//dts/rn_integracion/rntelejson.php";


    public static function HandleIncident( $incident)
    {

      try
      {
      
        $a_request = new \stdClass();
        $a_request->id_ticket = $incident->ReferenceNumber;
        $a_request->tipo_ticket = $incident->Disposition->ID;
        $a_request->estado = $incident->StatusWithType->Status->ID;
        $a_request->hh = $incident->CustomFields->c->id_hh;
        
        //self::insertPrivateNote($incident,'ENVIO [' .  json_encode($a_request) .']');
        $respuesta=self::SaveInstallState($a_request,$incident);
        //self::insertPrivateNote($incident,'RESPUESTA [' .  json_encode($respuesta) .']');
      }
      catch (RNCPHP\ConnectAPIError $err) 
      {
        
        $incident->CustomFields->c->shipping_instructions=$err->getMessage();
        $incident->Save(RNCPHP\RNObject::SuppressAll);
        self::insertPrivateNote($incident, "CPM Motor: " . $err->getMessage());
      }
    }



    static function SaveInstallState($datosHH,$incident)
    {
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

        
       $jsonToken = ConnectUrl::geTokenInfo();
        if($jsonToken === FALSE)
            return FALSE;

        // Se encapsula token como array
        //$a_jsonToken = json_decode($jsonToken, TRUE);

        //if (empty($a_jsonToken["access_token"]))
        //    throw new \Exception("Json de token inválido {$jsonToken}", 1);
        
        $token = $jsonToken;
        //self::insertPrivateNote($incident, "1-------------------z   >>  Token: " . $token);
        try{


            $url = $cfg2->Value."/CustomerDataInfo/SaveTicketInstall";
            $jsonDataEncoded=json_encode($datosHH);
            //self::insertPrivateNote($incident, "5url : " . $url);
            self::insertPrivateNote($incident, "6datos : [" . $jsonDataEncoded ."]");
            $service = ConnectUrl::requestCURLJsonRaw($url, $jsonDataEncoded, $token);
            //self::insertPrivateNote($incident, "ERROR : [" . self::getResponseError() ."]");
            return json_encode($service);
        }
        catch (RNCPHP\ConnectAPIError $err )
        {
            self::insertPrivateNote($incident, "Codigo : ".$err->getCode()." ".$err->getMessage());
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
