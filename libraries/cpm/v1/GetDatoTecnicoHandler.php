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

class GetDatoTecnicoHandler
{
    CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    // CONST URL_GET_HH   = "http://movil.dimacofi.cl/dts/rn_integracion/rntelejson.php";
    //CONST URL_GET_HH   = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    //CONST URL_GET_HH   = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";

    static function HandleIncident($runMode, $action, $incident, $cycle)
    {

        if ($cycle !== 0) return;


        $incident = RNCPHP\Incident::fetch($incident->ID);

        try
        {
            $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
            $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
            $url=$cfg->Value;
            
            $HH = $incident->CustomFields->c->id_hh;

            $array_post     = array('usuario' => 'appmind',
                                    'accion' => 'info_hh',
                                    'datos'=> array('id_hh'=> $HH)
                                    );

            $json_data_post = json_encode($array_post);
            $json_data_post = Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
            $json_data_post = base64_encode($json_data_post);

            $postArray = array ('data' => $json_data_post);

            $result = ConnectUrl::requestPost($url, $postArray);

            if ($result != false) {
                $arr_json = json_decode($result, true);
                if ($arr_json != false)
                {
                    if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json)) ))
                    {

                        $respuesta  = base64_decode($arr_json['respuesta']);
                        switch ($arr_json['resultado'])
                        {
                            case True:

                                $json_hh = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                                $array_hh_data = json_decode(utf8_encode($json_hh), true); //Transformar a array
                                if (!is_array($array_hh_data))
                                {
                                    $message = "ERROR: Estructura JSON encriptado No valida ".PHP_EOL;
                                    $message .= "JSON: ".$json_hh;
                                    $bannerNumber = 3;
                                    break;
                                }

                                $array_hh_data = $array_hh_data['respuesta'];
                                $array_tecnico = $array_hh_data['Tecnico'];
                                $array_hh_direccion =  $array_hh_data['Direccion'];

                                $indiceIdTecnico = 'ID_IBS';
                                $indiceIdDireccion = 'ID_direccion';


                                if (array_key_exists($indiceIdTecnico, $array_tecnico) and $array_tecnico[$indiceIdTecnico] == "-1" and !empty($array_tecnico[$indiceIdTecnico]))
                                {
                                    $message = "No se pudo ingresar el técnico, puesto que por WS viene vacio";
                                    $bannerNumber = 3;
                                    break;
                                }

                                if (array_key_exists($indiceIdDireccion, $array_hh_direccion) and $array_hh_direccion[$indiceIdDireccion ] == "-1" and !empty($array_hh_direccion[$indiceIdDireccion]))
                                {
                                    $message = "No se pudo ingresar el técnico, puesto que por WS viene sin direccion asociada";
                                    $bannerNumber = 3;
                                    break;
                                }


                                $inc_hh = new HHIncidentModel($incident);
                             //$hh_result = $inc_hh->saveInfoTecnico( $array_tecnico, $array_hh_direccion);
								if($incident->Subject== "Incidente de visita - Cotizador" and $incident->StatusWithType->Status->ID !=150){//si asunto es ..cotizador Y estado es Distinto Despacho entregado//EN trabajo de desarrollo
								//if($incident->Subject== "Incidente de visita - Cotizador")                               {

								  $array_tecnico['ID_IBS']='100003442';

                                  $hh_result = $inc_hh->saveInfoTecnico( $array_tecnico, $array_hh_direccion);
                                }
                                else 
                                {
                                  $hh_result = $inc_hh->saveInfoTecnico( $array_tecnico, $array_hh_direccion);
                                }


                                if ($hh_result == false)
                                {
                                    $message = "Error: ".$inc_hh->getLastError();
                                    $bannerNumber = 3;
                                }
                                else 
                                {
                                    $bannerNumber = 1;
                                    $message = "Los datos de Tecnico han sido ingresados correctamente";
                                }

                                break;
                            case False:
                                $message = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                                $bannerNumber = 3;
                                break;
                            default:
                                $message = "ERROR: Estructura JSON No valida ".PHP_EOL;
                                $message .= "JSON: ". $result;
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
                    $message = "ERROR 'GetDatoTecnicoHandler': Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
                    $bannerNumber = 3;
                }
            }
            else 
            {
                $message = "ERROR 'GetDatoTecnicoHandler': ".ConnectUrl::getResponseError();
                $bannerNumber = 3;
            }
            self::insertPrivateNote($incident, $message);
            self::insertBanner($incident, $bannerNumber);
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
            $texto = "Tecnico no pudo ser asignado";

        $incident->Banner->Text = $texto;
        $incident->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
        $incident->Save(RNCPHP\RNObject::SuppressAll);

    }



}
