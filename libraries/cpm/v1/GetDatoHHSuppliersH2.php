<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;


require_once "Labels.php";
require_once "Blowfish.php";
require_once "ConnectUrl.php";

class GetDatoHHSuppliersH2
{
    const KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    //CONST URL_GET_HH   = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    //CONST URL_WSO2 = "https://api.dimacofi.cl/apiCloudMD/getRutStatusSAI";

    static function HandleIncident($runMode, $action, $incident, $cycle)
    {
        if ($cycle !== 0) return;
        $bannerNumber = 0;


        try 
        {

            $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
            $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
            $url=$cfg->Value;
            $url2=$cfg2->Value;

            $tokenHeader=ConnectUrl::geToken();

            self::insertPrivateNote($incident, "Toket : " . $tokenHeader);
            $org_rut = $incident->PrimaryContact->Organization->CustomFields->c->rut;
            $a_request = array(
                "RUT" => $org_rut
            );

            $json_request = json_encode($a_request);
            $response     =ConnectUrl::requestCURLJsonRaw($url2, $json_request, $token); 
            self::insertPrivateNote($incident, "response : " . $response);
            // Obtiene valor de HH
            $id_hh      = $incident->CustomFields->c->id_hh;
            $array_post = array(
                'usuario' => 'appmind',
                'accion'  => 'info_hh2',
                'datos'   => array(
                    'id_hh'   => $id_hh
                )
            );

            $json_data_post = json_encode($array_post);
            self::insertPrivateNote($incident, "JSON enviado : " . $json_data_post);

            $json_data_post = Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
            $json_data_post = base64_encode($json_data_post);

            $postArray      = array('data' => $json_data_post);
            $result         = ConnectUrl::requestPost($url, $postArray);


            if ($result != FALSE) 
            {
                $arr_json = json_decode($result, TRUE);
                if ($arr_json != false) 
                {
                    if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json)))) 
                    {
                        $respuesta  = base64_decode($arr_json['respuesta']);

                        switch ($arr_json['resultado']) 
                        {
                            case "true":

                                $json_hh = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                                self::insertPrivateNote($incident, "JSON recibido :" . $json_hh);

                                $array_hh_data = json_decode(utf8_encode($json_hh), TRUE);


                                if (!is_array($array_hh_data)) 
                                {
                                    $message = "ERROR: Estructura JSON encriptado No valida " . PHP_EOL;
                                    $message .= "JSON: " . $json_hh;
                                    $bannerNumber = 3;
                                    break;
                                }

                                $array_hh_data           = $array_hh_data['respuesta'];
                                $hh_marca                = $array_hh_data['Marca'];
                                $hh_sla                  = $array_hh_data['SLA'];
                                $sla_hh_rsn              = $array_hh_data['RSN'];
                                $hh_modelo               = $array_hh_data['Modelo'];
                                $hh_convenio             = $array_hh_data['Convenio'];
                                $array_hh_contadores     = $array_hh_data['Contadores'];
                                $array_hh_direccion_id   = $array_hh_data['Direccion'];
                                $hh_tipo_contrato        = $array_hh_data['TipoContrato'];
                                $serie_hh                = $array_hh_data['Serie'];
                                $numero_delfos           = $array_hh_data['delfos'];
                                $bool_convenio_insumos   = $array_hh_data['convenio_insumos'];
                                $bool_convenio_corchetes = $array_hh_data['convenio_corchetes'];
                                $inventoryItemId         = $array_hh_data['inventory_item_id'];
                                $codeItem                = $array_hh_data['code_item'];
                                $a_suppliers             = $array_hh_data['suppliers'];
                                $a_suppliers_full        = $array_hh_data['suppliers_full'];
                                $Rut                     = $array_hh_data['Rut'];

                                $hh_result             = self::saveInfoHHinsumos($incident, $hh_marca, $hh_modelo, $hh_sla, $sla_hh_rsn, $hh_convenio, $hh_tipo_contrato, $array_hh_contadores, $array_hh_direccion_id, $serie_hh, $numero_delfos, $bool_convenio_insumos, $bool_convenio_corchetes, $inventoryItemId, $codeItem, $a_suppliers, $a_suppliers_full, $Rut);


                                if ($hh_result == FALSE) 
                                {
                                    $message = "ERROR: En guardado de HH";
                                    $bannerNumber = 3;
                                } 
                                else 
                                {
                                    $bannerNumber = 1;
                                    $message = "Los datos de HH han sido ingresados correctamente";
                                }

                                break;


                            case "false":
                                $message      = "ERROR: Servicio responde con fallo " . PHP_EOL;
                                $bannerNumber = 3;
                                break;
                            default:
                                $message       = "ERROR: Respuesta fallida " . PHP_EOL;
                                $json_hh       = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                                $array_hh_data = json_decode(utf8_encode($json_hh), true);
                                $message      .= "JSON: " . $array_hh_data['msg'];
                                $bannerNumber  = 3;
                                break;
                        }
                    } 
                    else 
                    {
                        $message      = "ERROR: Estructura JSON No valida " . PHP_EOL;
                        $message     .= "JSON: " . $result;
                        $bannerNumber = 3;
                    }
                } 
                else 
                {
                    $message      = "ERROR: Problema en la decodificación del JSON " . PHP_EOL . "Respuesta: " . $result . PHP_EOL;
                    $bannerNumber = 3;
                }
            } 
            else 
            {
                $message      = "ERROR: " . ConnectUrl::getResponseError();
                $bannerNumber = 3;
            }

            if (!empty($message))
                self::insertPrivateNote($incident, $message);

            if (!empty($bannerNumber))
                self::insertBanner($incident, $bannerNumber);

        } 
        catch (RNCPHP\ConnectAPIError  $e) 
        {
            self::insertPrivateNote($incident, "Error " . $e->getMessage());
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
        catch (RNCPHP\ConnectAPIError $err) 
        {
            $incident->Threads                   = new RNCPHP\ThreadArray();
            $incident->Threads[0]                = new RNCPHP\Thread();
            $incident->Threads[0]->EntryType     = new RNCPHP\NamedIDOptList();
            $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
            $incident->Threads[0]->Text          = "Error " . $err->getMessage();
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

        $incident->Banner->Text           = $texto;
        $incident->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
        $incident->Save(RNCPHP\RNObject::SuppressAll);
    }

    static function saveInfoHHinsumos($incident, $marca, $modelo, $sla, $sla_rsn, $bool_convenio, $hh_tipo_contrato,$array_contadores, $array_direcciones, $serie_hh, $numero_delfos,$bool_convenio_insumos, $bool_convenio_corchetes, $inventoryItemId, $codeItem,$a_suppliers,$a_suppliers_full,$Rut)
    {
      try
      {
        self::insertPrivateNote($incident, "GetDatoHH Insumos");
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
        $id_ebs_direccion                               = (int) $array_direcciones['ID_direccion'];
        // Campos nuevos
        $incident->CustomFields->c->convenio_corchetes  = (int) $bool_convenio_corchetes;
        $incident->CustomFields->c->convenio_insumos    = (int) $bool_convenio_insumos;
        $incident->CustomFields->c->order_number_om_ref = $Rut;

        // Campos nueva integración y corrección CPMs
        $incident->CustomFields->c->inventory_item_id = (int) $inventoryItemId;

        $a_json_hh = array(
            "counters"  => $array_contadores,
            "suppliers" => $a_suppliers_full
        );

        $incident->CustomFields->c->json_hh            = json_encode($a_json_hh);

        if (!is_array($array_direcciones))
        {
          self::insertPrivateNote($incident, "Objeto direcciones viene vacio: " . print_r($array_direcciones, TRUE));
          //RNCPHP\ConnectAPI::rollback();
          return FALSE;
        }


        $array_Direccion_obj = RNCPHP\DOS\Direccion::find("d_id = {$id_ebs_direccion} LIMIT 1");
        if (count($array_Direccion_obj) > 0)
            $incident->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
        else
        {
            self::insertPrivateNote($incident, "Dirección ID {$id_ebs_direccion} enviada por ws no se encuentra en Oracle RightNow");
           // RNCPHP\ConnectAPI::rollback();
            return FALSE;
        }


        if ($bool_convenio_insumos === FALSE)
          $incident->StatusWithType->Status->ID = 185; // Evaluación convenio
        else
          $incident->StatusWithType->Status->ID = 129; // Información validada
        
        $incident->Save(); // Este save disparará la regla que guardará el HH

        return $incident->ID;

      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        RNCPHP\ConnectAPI::rollback();
        self::insertPrivateNote($incident, "Codigo : ".$err->getCode()." ".$err->getMessage());
        return false;
      }
    }
}
