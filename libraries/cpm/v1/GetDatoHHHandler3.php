<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;
use RightNow\Connect\v1_3 as RNCPHP;


require_once "Labels.php";
require_once "Blowfish.php";
require_once "ConnectUrl.php";

class GetDatoHHHandler3
{
    CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
   
    static function HandleIncident($runMode, $action, $incident, $cycle)
    {

        if ($cycle !== 0) return;
        $incident = RNCPHP\Incident::fetch($incident->ID);
        $bannerNumber = 0;
        $cfg2 = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        self::insertPrivateNote($incident, "GetDatoHHHandler3 Inicio -> " . json_encode($cfg2));
        
        try
        {
            // $id_hh       = $incident->CustomFields->c->id_hh; // TODO: Obtener el id del activo. Accediendo al asset y en caso de no tener la relación retornar mensaje. Asset.SerialNumber
            $id_hh = $incident->Asset->SerialNumber;
            
            if(strlen($id_hh) < 1)
            {
             
               
                //$ObjAsset =  RNCPHP\Asset::first("SerialNumber = " . $incident->CustomFields->c->id_hh);
        
               
                self::insertPrivateNote($incident, "'GetDatoHHHandler3' El incidente requeiere tener la relación con su HH  " . $incident->CustomFields->c->id_hh );
                return FALSE;

                
            }
            else
            {
              
              

                $id_hh                            = (int) $id_hh;
                $incident->CustomFields->c->id_hh = $id_hh;
            }
            self::insertPrivateNote($incident, "GetDatoHHHandler3 HH " .  $id_hh);

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
                self::insertPrivateNote($incident, "JSON por codificar " . $json_data_post);

                $json_data_post2 = base64_encode($json_data_post);
                self::insertPrivateNote($incident, "JSON Codificado " .  $json_data_post2);                           

                $postArray = array("data" => $json_data_post2);
                $result    = ConnectUrl::requestPost($cfg2->Value, $postArray);

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
                                    $contract_number       = $array_hh_data['contract_number'];
                                    $solution_type         = $array_hh_data['solution_type'];
                                    $sub_type              = $array_hh_data['sub_type'];

                                    $hh_result  = self::saveInfoHH($incident, $hh_marca, $hh_modelo, $hh_sla,$sla_hh_rsn, $hh_convenio, $hh_tipo_contrato ,$array_hh_contadores, $array_hh_direccion_id,$serie_hh,$numero_delfos,$item_type,$Rut,$contract_number,$solution_type,$sub_type);

                                    if ($hh_result == FALSE)
                                    {
                                        $message = "ERROR: en guardado de HH ";
                                        $bannerNumber = 3;
                                    }
                                    else 
                                    {
                                        $bannerNumber = 1;
                                        $message      = "Los datos de HH han sido ingresados correctamente";
                                    }

                                    break;
                                case "false":
                                    $message      = "ERROR: Servicio responde con fallo ".PHP_EOL;
                                    $bannerNumber = 3;
                                    break;
                                default:
                                    $message      = "ERROR: Respuesta fallida ". ConnectUrl::getResponseError().PHP_EOL;
                                    $message      .= "JSON: ". $result;
                                    $bannerNumber = 3;
                                    break;
                            }


                        }
                        else 
                        {
                            $message = "ERROR: Estructura JSON No valida ". PHP_EOL;
                            $message .= "JSON: ". $result;
                            $bannerNumber = 3;
                        }
                    }
                    else
                    {
                        $message = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
                        $bannerNumber = 3;
                    }
                }
                else 
                {
                    $message      = "ERROR: " . ConnectUrl::getResponseError() . ". TIEMPO TOTAL " . ConnectUrl::getResponseTime();
                    $bannerNumber = 3;
                }
                //self::insertPrivateNote($incident, $message);
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
             $message = "Error ".$e->getMessage();
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
            $incident->Subject = "Error " . $err->getMessage();
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

    static function saveInfoHH($incident, $marca, $modelo, $sla,$sla_rsn, $bool_convenio, $hh_tipo_contrato, $array_contadores, $array_direcciones,$serie_hh,$numero_delfos,$item_type,$Rut,$contract_number,$solution_type,$sub_type)
    {
        try
        {
            RNCPHP\ConnectAPI::commit();
            $incident->CustomFields->c->marca_hh            = $marca;
            $incident->CustomFields->c->modelo_hh           = $modelo;
            $incident->CustomFields->c->convenio            = (int) $bool_convenio;
            if(intval($contract_number)>0)
            {
                $incident->CustomFields->c->tipo_contrato       = 'Arriendo';
            }
            else
            {
                $incident->CustomFields->c->tipo_contrato       = $hh_tipo_contrato;
            }
            $incident->CustomFields->c->sla_hh              = $sla;
            $incident->CustomFields->c->sla_hh_rsn          = $sla_rsn;
            $incident->CustomFields->c->cliente_bloqueado   = (int) $array_direcciones['Bloqueado'];
            $incident->CustomFields->c->serie_maq           = $serie_hh;
            $incident->CustomFields->c->numero_delfos       = $numero_delfos;
            $incident->CustomFields->c->item_type           = $item_type;
            $incident->CustomFields->c->order_number_om_ref = $Rut;
            $incident->CustomFields->c->contract_number= $contract_number.'test2';
            $incident->CustomFields->c->solution_type= $solution_type;
            $incident->CustomFields->c->sub_type= $sub_type;
            $a_json_hh = array(
                "counters"  => $array_contadores
            );

            $incident->CustomFields->c->json_hh            = json_encode($a_json_hh);


            $id_ebs_direccion                              = $array_direcciones['ID_direccion'];
            $array_Direccion_obj                           = RNCPHP\DOS\Direccion::find("d_id = {$id_ebs_direccion} LIMIT 1");
            if (count($array_Direccion_obj) > 0)
                $incident->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
            
            $incident->CustomFields->c->hh_rel_created = TRUE;
            $incident->save();

            return  $incident->ID;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            self::insertPrivateNote($incident, "Codigo : ".$err->getCode()." ".$err->getMessage());
            RNCPHP\ConnectAPI::rollback();
            return FALSE;
        }
    }


}
