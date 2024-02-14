<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_2 as RNCPHP;

require_once "Labels.php";
require_once "Blowfish.php";
//require_once "ConnectUrl2.php";
require_once "ConnectUrl.php";
require_once "HHIncidentModel.php";

class InstruccionesEnvioHandler
{
    CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    
    static function HandleIncident($runMode, $action, $incident, $cycle)
    {

        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
        $url=$cfg->Value;
        if ($cycle !== 0) return;

        

        $incident = RNCPHP\Incident::fetch($incident->ID);
        //self::insertPrivateNote($incident, "InstruccionesEnvioHandler");
        $HH = $incident->CustomFields->c->id_hh;
        try{
            //self::insertPrivateNote($incident, "InstruccionesEnvioHandler1");
           $array_post     = array('usuario' => 'appmind',
            'accion' => 'info_hh',
            'datos'=> array('id_hh'=> $HH)
            );
            //self::insertPrivateNote($incident, "InstruccionesEnvioHandler3" . json_encode($array_post));

            $json_data_post = json_encode($array_post);
            $json_data_post = Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
            $json_data_post = base64_encode($json_data_post);

            $postArray = array ('data' => $json_data_post);

            $result = ConnectUrl::requestPost($url, $postArray);
  
            //self::insertPrivateNote($incident, "InstruccionesEnvioHandler-> " . json_encode($result));
            if ($result != false) {
                $arr_json = json_decode($result, true);
                if ($arr_json != false)
                {
                    if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json)) ))
                    {

                        $respuesta  = base64_decode($arr_json['respuesta']);
                        if($arr_json['resultado']==true)
                        {
                            $json_hh = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                            $array_hh_data = json_decode(utf8_encode($json_hh), true); //Transformar a array
                            if (!is_array($array_hh_data))
                            {
                                $message = "ERROR: Estructura JSON encriptado No valida ".PHP_EOL;
                                $message .= "JSON: ".$json_hh;
                                $bannerNumber = 3;
                               
                            }

                            $array_hh_data = $array_hh_data['respuesta'];
                            $array_tecnico = $array_hh_data['Tecnico'];
                            //self::insertPrivateNote($incident, "InstruccionesEnvioHandler  Tecnico-> " . json_encode($array_hh_data));

                        }
                    }
                }
            }
            
            

            if($incident->CustomFields->c->support_type)            {
               
                if($incident->CustomFields->c->ar_flow->ID==282) // Repuesto Enviado a Terreno
                {
                    $incident->CustomFields->c->shipping_instructions='Despacho Proactivo Despachar a Zona de : ' . $array_tecnico['Nombre'];
                    self::insertPrivateNote($incident, "InstruccionesEnvioHandler  Tecnico -" . $incident->CustomFields->c->ar_flow->ID .'-'.  $array_tecnico['Nombre']);
                }
            }   
           /* if($incident->CustomFields->c->ar_flow)          
            {
                if($incident->CustomFields->c->ar_flow->ID==281)
                {
                    $incident->CustomFields->c->shipping_instructions='Despacho Proactivo Despachar directo al cliente';
                }
            }
            */
         }
         catch (RNCPHP\ConnectAPIError $err){
             //echo $err->getMessage();
             self::insertPrivateNote($incident, $err->getMessage());
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
            $texto = "Tecnico no pudo ser asignado";

        $incident->Banner->Text = $texto;
        $incident->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
        $incident->Save(RNCPHP\RNObject::SuppressAll);

    }



}
