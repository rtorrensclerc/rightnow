<?php

/**
 * Skeleton incident cpm handler.
 * cpm que crea el reporte de prediccion con el HH indicado
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;

require_once "Labels.php";
require_once "ConnectUrl.php";

class CreatePredictionSuplies
{
    //CONST URL_GET_HH = "https://api.dimacofi.cl/DDDM/DDDM_CreaReporte";
    
    
    static function HandleIncident($runMode, $action, $incident, $cycle)
    {
        if ($cycle !== 0)
            return;
        $bannerNumber = 0;
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
        
       
        
        try {
            // Obtiene valor de HH en el ticket
            $id_hh      = $incident->CustomFields->c->id_hh;
            $array_post = array(
                'hh' => $id_hh
            );
            
            $json_data_post = json_encode($array_post);
            
            
            $postArray = array(
                'data' => $json_data_post
            );
            $url=$cfg2->Value .'/DDDM/DDDM_CreaReporte';
            self::insertPrivateNote($incident, 'call-> ' . $url);
            $result    = ConnectUrl::requestPost($url, $postArray);
            
            
            if ($result != FALSE) {
                $arr_json = json_decode($result, true);
                if ($arr_json != true) {
                    $message      = "ERROR: Problema en la decodificación del JSON " . PHP_EOL . "Respuesta: " . $result . PHP_EOL;
                    $bannerNumber = 3;
                }
                
            } else {
                $message      = "ERROR: " . ConnectUrl::getResponseError();
                $bannerNumber = 3;
            }
            
            
            if (!empty($message))
                self::insertPrivateNote($incident, $message);
            
            
        }
        catch (RNCPHP\ConnectAPIError $e) {
            self::insertPrivateNote($incident, "Error " . $e->getMessage());
        }
    }
    
    //Esto guarda el texto en "Notas Privadas"
    static function insertPrivateNote($incident, $textoNP)
    {
        try {
            $incident->Threads                   = new RNCPHP\ThreadArray();
            $incident->Threads[0]                = new RNCPHP\Thread();
            $incident->Threads[0]->EntryType     = new RNCPHP\NamedIDOptList();
            $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
            $incident->Threads[0]->Text          = $textoNP;
            $incident->Save(RNCPHP\RNObject::SuppressAll);
        }
        catch (RNCPHP\ConnectAPIError $err) {
            $incident->Threads                   = new RNCPHP\ThreadArray();
            $incident->Threads[0]                = new RNCPHP\Thread();
            $incident->Threads[0]->EntryType     = new RNCPHP\NamedIDOptList();
            $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
            $incident->Threads[0]->Text          = "Error " . $err->getMessage();
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            return FALSE;
        }
    }
    
    
}
