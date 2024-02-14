<?php

/**
 * Skeleton CreateSaiReemplaceHH cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_2 as RNCPHP;

require_once "ConnectUrl.php";

class CreateSaiReemplaceHH
{
   
    public static function HandleIncident( $run_mode, $action, $incident, $n_cycles)
    {

      try
      {
        $a_request = new \stdClass();
        $a_request->hh = $incident->CustomFields->c->id_hh;
        // $a_request->delfos = $incident->CustomFields->c->numero_delfos;
        $a_request->hh_nueva=$incident->CustomFields->c->hh_replacement;
        $a_request->comentarios = "Reemplazo de Equipo HH " .$incident->CustomFields->c->id_hh . ' por HH ' . $incident->CustomFields->c->hh_replacement;
        $a_request->aprobacion= true;
        $a_request->referencia_externa =  $incident->ReferenceNumber;
      
        //$incident->StatusWithType->Status->ID                  = 178  ;  //  Enviado
        $incident->CustomFields->c->requiere_taller=false;
        $orden_activacion="P" . time();
        $incident->CustomFields->c->orden_activacion =$orden_activacion;
        self::insertPrivateNote($incident,'ENVIO [' .  json_encode($a_request) .']');
        $respuesta=self::CreateSaiReemplaceHH($a_request,$incident);
        //self::insertPrivateNote($incident,'RESPUESTA [' .  $respuesta .']');
        $incident->Save();
      }
      catch (RNCPHP\ConnectAPIError $err) 
      {
        
        $incident->CustomFields->c->shipping_instructions=$err->getMessage();
        $incident->Save(RNCPHP\RNObject::SuppressAll);
        self::insertPrivateNote($incident, "CPM Motor: " . $err->getMessage());
      }
    }
    static function CreateSaiReemplaceHH($datosHH,$incident)
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
            $url = $cfg2->Value."/cloudsai/mq/sairemplazohh";
            //$url="https://api.dimacofi.cl/cloudsai/mq/sairemplazohh";
            //$url="https://api.dimacofi.cl/CloudTest/mq/sairemplazohh";
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
