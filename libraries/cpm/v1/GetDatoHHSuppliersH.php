<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;
use RightNow\Connect\v1_3 as RNCPHP;

//use RightNow\Connect\Crypto\v1_3 as Crypto;

require_once "Labels.php";
require_once "Blowfish.php";
require_once "ConnectUrl.php";
require_once "HHInsumosModel.php";

class GetDatoHHSuppliersH
{
    CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    //CONST URL_GET_HH   = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    //CONST URL_GET_HH   = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    //CONST URL_GET_HH   = "http://soportedimacoficl--tst2.custhelp.com/cc/AjaxCustom/testInsumosHH";


    static function HandleIncident($runMode, $action, $incident, $cycle)
    {
        if ($cycle !== 0) return;
        $bannerNumber = 0;


        try
        {
          //$cfg2   = RNCPHP\Configuration::fetch( CUSTOM_CFG_WS_URL );
          //$URL_WS = "http://190.14.56.27/public/rn_integracion/rntelejson.php";
          //$URL_WS = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
          $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
          $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
          $url=$cfg->Value;
          //obtiene valor de HH
          $id_hh          = $incident->CustomFields->c->id_hh; //168665
          if(strlen($id_hh) < 1)
            {
                $id_hh = $incident->Asset->SerialNumber;
                $incident->CustomFields->c->id_hh = $id_hh;
                self::insertPrivateNote($incident, "GetDatoHHSuppliersH El incidente requeiere tener la relación con su HH [" . $id_hh  ."]");
                return FALSE;
            }
            else
            {
                $id_hh                            = (int) $id_hh;
                $incident->CustomFields->c->id_hh = $id_hh;
                self::insertPrivateNote($incident, "Buscando GetDatoHHSuppliersH");
            }

          $array_post     = array('usuario' => 'appmind',
                                  'accion' => 'info_hh2',
                                  'datos'=> array('id_hh'=> $id_hh)
                                  );
          $json_data_post = json_encode($array_post);
          self::insertPrivateNote($incident, "json enviado :".$json_data_post);
          $json_data_post = Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
          $json_data_post = base64_encode($json_data_post);

          $postArray      = array ('data' => $json_data_post);

          //$result         = ConnectUrl::requestPost(self::URL_GET_HH, $postArray);
          $result         = ConnectUrl::requestPost($url, $postArray);

          if ($result != false)
          {
              $arr_json = json_decode($result, true);
              if ($arr_json != false)
              {
                  if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json)) ))
                  {
                    $respuesta  = base64_decode($arr_json['respuesta']);

                    switch ($arr_json['resultado'])
                    {
                        case "true":

                            $json_hh = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                            self::insertPrivateNote($incident, "json recibido :".$json_hh);

                            $array_hh_data = json_decode(utf8_encode($json_hh),true);


                            if (!is_array($array_hh_data))
                            {
                                $message = "ERROR: Estructura JSON encriptado No valida ".PHP_EOL;
                                $message .= "JSON: ".$json_hh;
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
                            self::insertPrivateNote($incident, $Rut);

                            $inc_hh                = new HHInsumosModel($incident);
                            $hh_result             = $inc_hh->saveInfoHHinsumos($hh_marca, $hh_modelo, $hh_sla, $sla_hh_rsn, $hh_convenio, $hh_tipo_contrato,
                                                              $array_hh_contadores, $array_hh_direccion_id, $serie_hh, $numero_delfos,
                                                              $bool_convenio_insumos, $bool_convenio_corchetes, $inventoryItemId, $codeItem,
                                                              $a_suppliers,$a_suppliers_full,$Rut);


                            if ($hh_result == false)
                            {
                                $message = "ERROR: ". $inc_hh->getLastError();
                                $bannerNumber = 3;
                            }
                            else {
                                $bannerNumber = 1;
                                $message = "Los datos de HH han sido ingresados correctamente";
                            }

                            break;


                        case False:
                            $message      = "ERROR: Servicio responde con fallo ".PHP_EOL;
                            $bannerNumber = 3;
                            break;
                        default:
                            $message       = "ERROR: Respuesta fallida ".PHP_EOL;
                            $json_hh       = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                            $array_hh_data = json_decode(utf8_encode($json_hh),true);
                            $message      .= "JSON: ". $array_hh_data['msg'];
                            $bannerNumber  = 3;
                            break;
                    }
                  }
                  else
                  {
                    $message      = "ERROR: Estructura JSON No valida ". PHP_EOL;
                    $message     .= "JSON: ". $result;
                    $bannerNumber = 3;
                  }
              }
              else
              {
                  $message      = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
                  $bannerNumber = 3;
              }
          }
          else
          {
              $message      = "ERROR: ".ConnectUrl::getResponseError();
              $bannerNumber = 3;
          }

          if (!empty($message))
            self::insertPrivateNote($incident, $message);

          if (!empty($bannerNumber))
            self::insertBanner($incident, $bannerNumber);
        }
        catch (RNCPHP\ConnectAPIError  $e)
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
          $incident->Threads                   = new RNCPHP\ThreadArray();
          $incident->Threads[0]                = new RNCPHP\Thread();
          $incident->Threads[0]->EntryType     = new RNCPHP\NamedIDOptList();
          $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
          $incident->Threads[0]->Text          = "Error" . $err->getMessage();
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
            $texto = "HH no pudo ser asignada";

        $incident->Banner->Text           = $texto;
        $incident->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
        $incident->Save(RNCPHP\RNObject::SuppressAll);

    }




}
