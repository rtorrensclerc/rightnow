<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;
use RightNow\Connect\v1_3 as RNCPHP;


require_once "Labels.php";
require_once "Blowfish.php";
require_once "ConnectUrl.php";

class GetDatoHHHandler2
{
    CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    //CONST URL_GET_HH   = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    // CONST URL_API      = "http://api-test.dimacofi.cl/oracle/DatosHH2/";
    static function HandleIncident($runMode, $action, $incident, $cycle)
    {

        if ($cycle !== 0) return;
        $incident = RNCPHP\Incident::fetch($incident->ID);
        $bannerNumber = 0;
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
        $url=$cfg->Value;
        try
        {
            $id_hh       = $incident->CustomFields->c->id_hh;
            $tokenHeader = ConnectUrl::geToken();

            if((int) $id_hh >= 200)
            {
                $array_post = array(
                    "usuario" => "appmind",
                    "accion"  => "info_hh",
                    "datos"   => array(
                        "id_hh" => $id_hh
                    )
                );

                $json_data_post = json_encode($array_post);
                self::insertPrivateNote($incident, "'GetDatoHHHandler2' JSON por codificar " . $json_data_post);

                $json_data_post2 = base64_encode($json_data_post);
                self::insertPrivateNote($incident, "'GetDatoHHHandler2' JSON Codificado " .  $json_data_post2);                           

                $postArray = array("data" => $json_data_post2);
                $result    = ConnectUrl::requestPost($url, $postArray);

                if ($result != FALSE) 
                {
                    $arr_json = json_decode($result, true);
                    if ($arr_json != FALSE)
                    {
                        if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json))))
                        {
                            $respuesta  = base64_decode($arr_json['respuesta']);

                            switch ($arr_json['resultado'])
                            {
                                case "true":

                                    $json_hh = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                                    $array_hh_data = json_decode(utf8_encode($json_hh),true);


                                    if (!is_array($array_hh_data))
                                    {
                                        $message = "ERROR: Estructura JSON encriptado No valida ".PHP_EOL;
                                        $message .= "JSON: ".$json_hh;
                                        $bannerNumber = 3;
                                        break;
                                    }

                                    $array_hh_data         = $array_hh_data['respuesta'];
                                    $hh_marca              = $array_hh_data['Marca'];
                                    $hh_sla                = $array_hh_data['SLA'];
                                    $sla_hh_rsn            = $array_hh_data['RSN'];

                                    $hh_modelo             = $array_hh_data['Modelo'];
                                    $hh_convenio           = $array_hh_data['Convenio'];

                                    $array_hh_contadores   = $array_hh_data['Contadores'];
                                    $array_hh_direccion_id = $array_hh_data['Direccion'];
                                    $hh_tipo_contrato      = $array_hh_data['TipoContrato'];
                                    $serie_hh              = $array_hh_data['Serie'];
                                    $numero_delfos         = $array_hh_data['delfos'];
                                    $item_type             = $array_hh_data['Tipo_Articulo'];
                                    $Rut                   = $array_hh_data['Rut'];

                                    $hh_result  = self::saveInfoHH($incident, $hh_marca, $hh_modelo, $hh_sla,$sla_hh_rsn, $hh_convenio, $hh_tipo_contrato ,$array_hh_contadores, $array_hh_direccion_id,$serie_hh,$numero_delfos,$item_type,$Rut);

                                    if ($hh_result == FALSE)
                                    {
                                        $message = "'GetDatoHHHandler2' ERROR: en guardado de HH ";
                                        $bannerNumber = 3;
                                    }
                                    else 
                                    {
                                        $bannerNumber = 1;
                                        $message      = "'GetDatoHHHandler2' Los datos de HH han sido ingresados correctamente";
                                    }

                                    break;
                                case "false":
                                    $message      = "'GetDatoHHHandler2' ERROR: Servicio responde con fallo ".PHP_EOL;
                                    $bannerNumber = 3;
                                    break;
                                default:
                                    $message      = "'GetDatoHHHandler2' ERROR: Respuesta fallida ". ConnectUrl::getResponseError().PHP_EOL;
                                    $message      .= "JSON: ". $result;
                                    $bannerNumber = 3;
                                    break;
                            }


                        }
                        else 
                        {
                            $message = "'GetDatoHHHandler2' ERROR: Estructura JSON No valida ". PHP_EOL;
                            $message .= "JSON: ". $result;
                            $bannerNumber = 3;
                        }
                    }
                    else
                    {
                        $message = "'GetDatoHHHandler2' ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
                        $bannerNumber = 3;
                    }
                }
                else 
                {
                    $message      = "'GetDatoHHHandler2' ERROR: " . ConnectUrl::getResponseError() . ". TIEMPO TOTAL " . ConnectUrl::getResponseTime();
                    $bannerNumber = 3;
                }
                self::insertPrivateNote($incident, $message);
                self::insertBanner($incident, $bannerNumber);

            }
            else 
            {
                $incident->CustomFields->c->marca_hh  = "TERMICA";
                $incident->CustomFields->c->modelo_hh = "TERMICA";
                $incident->Save(RNCPHP\RNObject::SuppressAll);
            }

        }
        catch (Exception $e)
        {
             $message = "'GetDatoHHHandler2' Error ".$e->getMessage();
             self::insertPrivateNote($incident, $message);
        }

    }

    static function insertPrivateNote($incident, $textoNP)
    {
        try
        {
            $incident->Threads                   = new RNCPHP\ThreadArray();
            $incident->Threads[0]                = new RNCPHP\Thread();
            $incident->Threads[0]->EntryType     = new RNCPHP\NamedIDOptList();
            $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
            $incident->Threads[0]->Text          = $textoNP;
            $incident->Save(RNCPHP\RNObject::SuppressAll);
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $incident->Subject = "'GetDatoHHHandler2' Error " . $err->getMessage();
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            return FALSE;
        }
    }

    static function insertBanner($incident, $typeBanner, $texto = '')
    {
        if (!is_numeric($typeBanner) and $typeBanner > 3 and $typeBanner < 0)
            $typeBanner = 1;

        $texto = '';
        if ($typeBanner == 3)
            $texto = "HH no pudo ser asignada";

        $incident->Banner->Text = $texto;
        $incident->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
        $incident->Save(RNCPHP\RNObject::SuppressAll);

    }

    static function saveInfoHH($incident, $marca, $modelo, $sla,$sla_rsn, $bool_convenio, $hh_tipo_contrato, $array_contadores, $array_direcciones,$serie_hh,$numero_delfos,$item_type,$Rut)
    {
        try
        {
            RNCPHP\ConnectAPI::commit();
            $incident->CustomFields->c->marca_hh            = $marca;
            $incident->CustomFields->c->modelo_hh           = $modelo;
            $incident->CustomFields->c->convenio            = (int) $bool_convenio;
            $incident->CustomFields->c->tipo_contrato       = $hh_tipo_contrato;
            $incident->CustomFields->c->sla_hh              = $sla;
            $incident->CustomFields->c->sla_hh_rsn          = $sla_rsn;
            $incident->CustomFields->c->cliente_bloqueado   = (int) $array_direcciones['Bloqueado'];
            $incident->CustomFields->c->serie_maq           = $serie_hh;
            $incident->CustomFields->c->numero_delfos       = $numero_delfos;
            $incident->CustomFields->c->item_type           = $item_type;
            $incident->CustomFields->c->order_number_om_ref = $Rut;

            $a_json_hh = array(
                "counters"  => $array_contadores
            );

            $incident->CustomFields->c->json_hh            = json_encode($a_json_hh);


            $id_ebs_direccion                              = $array_direcciones['ID_direccion'];
            $array_Direccion_obj                           = RNCPHP\DOS\Direccion::find("d_id = {$id_ebs_direccion} LIMIT 1");
            if (count($array_Direccion_obj) > 0)
                $incident->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
            $incident->save();

            return  $incident->ID;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            self::insertPrivateNote($incident, "'GetDatoHHHandler2' Codigo : ".$err->getCode()." ".$err->getMessage());
            RNCPHP\ConnectAPI::rollback();
            return FALSE;
        }
    }


}
