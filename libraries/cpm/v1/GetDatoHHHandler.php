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
//require_once "HHIncidentModel.php";

class GetDatoHHSuppliersH
{
    CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    //CONST URL_GET_HH   = "http://190.14.56.27/public/rn_integracion/rntelejson.php";
    //CONST URL_GET_HH   = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    //CONST URL_GET_HH   = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    //CONST URL_API  = "http://api.dimacofi.cl/oracle/DatosHH2/";
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
            //obtiene valor de HH
            $id_hh = $incident->CustomFields->c->id_hh; //168665
            if(strlen($id_hh) < 1)
            {


                $id_hh = $incident->Asset->SerialNumber;
                $incident->CustomFields->c->id_hh = $id_hh;
                self::insertPrivateNote($incident, "HandleIncident El incidente requeiere tener la relación con su HH [" . $id_hh  ."]");
                return FALSE;
            }
            else
            {
                                 
                
    
                $id_hh                            = (int) $id_hh;
                $incident->CustomFields->c->id_hh = $id_hh;
                self::insertPrivateNote($incident, "Buscando GetDatoHHSuppliersH2");
            }
            ///$tokenHeader = ConnectUrl::geToken();
            if(intval($id_hh)>=200)
            {
                //Inicio - Lineas de testing
                self::insertPrivateNote($incident, "ID de HH ->".$id_hh);
                //self::insertPrivateNote($incident, "Token ".$tokenHeader);
                //$result = ConnectUrl::requestCURLRaw(self::URL_API . $id_hh, $tokenHeader);

                //self::insertPrivateNote($incident, "WSO2 API RESPONSE: ". $result);
                //Fin - Lineas de testing

                $array_post     = array('usuario' => 'appmind',
                                        'accion' => 'info_hh',
                                        'datos'=> array('id_hh'=> $id_hh)
                                        );

                $json_data_post = json_encode($array_post);
                $json_data_post = Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
                $json_data_post = base64_encode($json_data_post);

                //self::insertPrivateNote($incident, "JSON Enviado " . $$json_data_post);                       

                $postArray = array ('data' => $json_data_post);

                $result = ConnectUrl::requestPost($url, $postArray);

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

                                    //$inc_hh = new HHIncidentModel($incident);
                                    //$hh_result = $inc_hh->saveInfoHH($hh_marca, $hh_modelo, $hh_sla,$sla_hh_rsn, $hh_convenio, $hh_tipo_contrato ,$array_hh_contadores, $array_hh_direccion_id,$serie_hh,$numero_delfos);
                                    $hh_result  = self::saveInfoHH($incident, $hh_marca, $hh_modelo, $hh_sla,$sla_hh_rsn, $hh_convenio, $hh_tipo_contrato ,$array_hh_contadores, $array_hh_direccion_id,$serie_hh,$numero_delfos,$Rut);



                                    if ($hh_result == false)
                                    {
                                        $message =  "ERROR: en guardado de HH ";
                                        $bannerNumber = 3;
                                    }
                                    else {
                                        $bannerNumber = 1;
                                        $message = "Los datos de HH han sido ingresados correctamente";
                                    }

                                    break;
                                case False:
                                    $message = "ERROR: Servicio responde con fallo ".PHP_EOL;
                                    $bannerNumber = 3;
                                    break;
                                default:
                                    $message = "ERROR: Respuesta fallida ".PHP_EOL;
                                    // $json_hh = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                                    // $array_hh_data = json_decode(utf8_encode($json_hh),true);
                                    // $message .= "JSON: ". $array_hh_data['msg'];
                                    $message .= "JSON: " . $result;
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
                    //$message      = "ERROR: " . ConnectUrl::getResponseError() . ". TIEMPO TOTAL " . ConnectUrl::getResponseTime();
                    $message      = "ERROR: " . ConnectUrl::getResponseError();
                    $bannerNumber = 3;
                }
                self::insertPrivateNote($incident, $message);
                self::insertBanner($incident, $bannerNumber);

            }
            else 
            {
                $incident->CustomFields->c->marca_hh='TERMICA';
                $incident->CustomFields->c->modelo_hh='TERMICA';
                self::updateAsset($incident);
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
            $texto = "HH no pudo ser asignada";

        $incident->Banner->Text = $texto;
        $incident->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
        $incident->Save(RNCPHP\RNObject::SuppressAll);

    }

    static function saveInfoHH($incident, $marca, $modelo, $sla,$sla_rsn, $bool_convenio, $hh_tipo_contrato, $array_contadores, $array_direcciones,$serie_hh,$numero_delfos,$Rut)
    {
        try
        {
            RNCPHP\ConnectAPI::commit();
            $incident->CustomFields->c->marca_hh           = $marca;
            $incident->CustomFields->c->modelo_hh          = $modelo;
            $incident->CustomFields->c->convenio           = (int) $bool_convenio;
            $incident->CustomFields->c->tipo_contrato      = $hh_tipo_contrato;
            $incident->CustomFields->c->sla_hh             = $sla;
            $incident->CustomFields->c->sla_hh_rsn         = $sla_rsn;
            $incident->CustomFields->c->cliente_bloqueado  = (int) $array_direcciones['Bloqueado'];
            $incident->CustomFields->c->serie_maq          = $serie_hh;
            $incident->CustomFields->c->numero_delfos      = $numero_delfos;
            $incident->CustomFields->c->item_type          = $item_type;
            $incident->CustomFields->c->order_number_om_ref= $Rut;
            self::insertPrivateNote($incident, $Rut);


            $id_ebs_direccion                              = $array_direcciones['ID_direccion'];

            if($id_ebs_direccion<1)
            {
              $id_ebs_direccion=1;
            }
            self::insertPrivateNote($incident, $id_ebs_direccion);

            $array_Direccion_obj                           = RNCPHP\DOS\Direccion::find('d_id = '. $id_ebs_direccion);

            self::insertPrivateNote($incident, '-->' . $id_ebs_direccion);


            if (is_array($array_Direccion_obj) and is_object($array_Direccion_obj[0]))
                $incident->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
            $incident->save(RNCPHP\RNObject::SuppressAll);

            if (self::updateAsset($incident) === false) //Creación del activo
            {
                RNCPHP\ConnectAPI::rollback();
                self::insertPrivateNote($incident, "Error intentado registrar los Activos");
                return false;
            }


            $counter_result = self::saveCounters($incident, $array_contadores); //Creación de contadores
            if ($counter_result === false)
            {
                // RNCPHP\ConnectAPI::rollback();
                return false;
            }

            return  $incident->ID;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $error = "Codigo : ".$err->getCode()." ".$err->getMessage();
            self::insertPrivateNote($incident, $error);
            RNCPHP\ConnectAPI::rollback();
            return false;
        }
    }

    static function updateAsset($incident)
    {
        try
        {
            $asset = RNCPHP\Asset::first( "SerialNumber = '".$incident->CustomFields->c->id_hh."'");
            if (empty($asset))
            {
                $asset               = new RNCPHP\Asset;
                $nameHH              = $incident->CustomFields->c->id_hh."-".$incident->CustomFields->c->marca_hh."-".$incident->CustomFields->c->modelo_hh;
                $asset->Name         = substr($nameHH, 0, 80);
                $asset->Contact      = $incident->PrimaryContact;
                $asset->Product      = 2;
                $asset->SerialNumber = $incident->CustomFields->c->id_hh;
                $asset->save(RNCPHP\RNObject::SuppressAll);

            }
            if(intval($incident->CustomFields->c->id_hh)>=200)
            {
                $asset->CustomFields->DOS->Direccion =  $incident->CustomFields->DOS->Direccion;
            }
            else {
                $incident->CustomFields->DOS->Direccion=$asset->CustomFields->DOS->Direccion;
            }

            $asset->save(RNCPHP\RNObject::SuppressAll);
            $incident->Asset = $asset;
            $incident->save(RNCPHP\RNObject::SuppressAll);

        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $error = "Problema al generar activo | Codigo : : ".$err->getCode()." ".$err->getMessage();
            self::insertPrivateNote($incident, $error);
            return false;
        }
    }

    static function saveCounters($incident, $array_counters)
    {
      if (is_array($array_counters))
      {
        try
        {
          foreach ($array_counters as $counter)
          {
            //Contadores
            $count_id               = $counter['ID'];
            $count_tipo             = $counter['Tipo'];
            $count_valor            = $counter['Valor'];
            $contador               = new RNCPHP\DOS\Contador();
            $contador->ContadorID   = $count_id;
            $contador->Valor        = $count_valor;
            $contador->Incident     = $incident;
            $contador->TipoContador = RNCPHP\DOS\TipoContador::fetch($counter['Tipo']);
            $contador->Asset        = $incident->Asset;
            $contador->save(RNCPHP\RNObject::SuppressAll);
          }
          return true;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
          $error = "Codigo : ".$err->getCode()." ".$err->getMessage();
          self::insertPrivateNote($incident, $error);
          return false;
        }
      }
      else
      {
        $error = "Estructura no Valida en los contadores";
        self::insertPrivateNote($incident, $error);
        return false;
      }
    }

}
