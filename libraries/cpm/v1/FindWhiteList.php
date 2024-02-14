<?php

/**
 * Skeleton Opportunity cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_2 as RNCPHP;

require_once "Labels.php";
//require_once "Blowfish.php";
require_once "ConnectUrl.php";


class FindWhiteList
{
  


    static function HandleIncident($runMode, $action, $incidents, $cycle)
    {

        //if ($cycle !== 0) return;
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
        $incident = RNCPHP\Incident::fetch($incidents->ID);
        $RUT=$incident->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut;

        if($incident->CustomFields->c->invoice_number==0)
        {
            self::insertPrivateNote($incident, "Validar Lista Blanca FindWhiteList");
            
            $array_post     = array("rut" => $RUT);
            $json_data_post = json_encode($array_post);
            self::insertPrivateNote($incident, "Validar Lista Blanca [" . $json_data_post ."]");
            
            
            $result=ConnectUrl::requestCURLJsonRaw($cfg2->Value . "/sucursalVirtual/consulta/ClientesPreferentes", $json_data_post, null);
            self::insertPrivateNote($incident, $cfg2->Value . "/sucursalVirtual/consulta/ClientesPreferentes");
            self::insertPrivateNote($incident, "Respuesta  Lista Blanca [" . $result ."]");
            $respuesta=json_decode($result);
            self::insertPrivateNote($incident, "Respuesta  Lista Blanca [" . $respuesta->Clientes->Cliente->status ."]");
            if($incidents->incidents->ID==134)
            {
                if($respuesta->Clientes->Cliente->status=='OK')
                {
                    $incident->CustomFields->c->contract_number='0';
                    $incident->Save();
                }
            }
            else
            {
                if($respuesta->Clientes->Cliente->status=='OK')
                {
                    $incident->StatusWithType->Status->ID=117;
                    $incident->Save();
                }
            }
            
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
