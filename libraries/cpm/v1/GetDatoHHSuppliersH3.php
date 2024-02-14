<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;


require_once "Labels.php";
require_once "Blowfish.php";
require_once "ConnectUrl.php";

class GetDatoHHSuppliersH3
{
    const KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    //CONST URL_GET_HH   = "http://190.14.56.27/public/rn_integracion/rntelejson.php";
    //const URL_GET_HH   = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php"; //PROD
    //CONST URL_WSO2 = "https://api.dimacofi.cl/apiCloudMD/getRutStatusSAI";
    

    static function HandleIncident($runMode, $action, $incident, $cycle)
    {
        

        
        if ($cycle !== 0) return;
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
        $bannerNumber = 0;
        //self::insertPrivateNote($incident, "GetDatoHHSuppliersH3");
        $incident->Save(RNCPHP\RNObject::SuppressAll);
        try
        {
            //$URL_WS = "http://190.14.56.27/public/rn_integracion/rntelejson.php";


            $token=ConnectUrl::geToken2();

           
            // Obtiene valor de HH
            // $id_hh      = $incident->CustomFields->c->id_hh;

            $id_hh = $incident->Asset->SerialNumber;
            if(strlen($id_hh) < 1)
            {
                //self::insertPrivateNote($incident, "GetDatoHHHandler3 El incidente requeiere tener la relación con su HH");
                $incident->Save(RNCPHP\RNObject::SuppressAll);
                return FALSE;
            }
            else
            {
                $id_hh                            = (int) $id_hh;
                $incident->CustomFields->c->id_hh = $id_hh;
                //self::insertPrivateNote($incident, "Buscando GetDatoHHSuppliersH3");
                $incident->Save(RNCPHP\RNObject::SuppressAll);
            }

            $array_post = array(
                'usuario' => 'Dimacofi',
                'accion'  => 'info_hh2',
                'datos'   => array(
                    'id_hh'   => $id_hh
                )
            );

            $json_data_post = json_encode($array_post);
            //self::insertPrivateNote($incident, "JSON enviado : " . $json_data_post);

            $json_data_post = Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
            $json_data_post = base64_encode($json_data_post);

            $postArray      = array('data' => $json_data_post);
            $result         = ConnectUrl::requestPost($cfg->Value, $postArray);
            //self::insertPrivateNote($incident, "JSON respuesta : " .  $result);
            $incident->Save(RNCPHP\RNObject::SuppressAll);



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
                                //self::insertPrivateNote($incident, "JSON recibido :" . $json_hh);

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
                                $contract_number       = $array_hh_data['contract_number'];
                                $solution_type         = $array_hh_data['solution_type'];
                                $sub_type              = $array_hh_data['sub_type'];
                                $priorization          = $array_hh_data['preferente'];
                                
                               

                                $hh_result = self::saveInfoHHinsumos($incident, $hh_marca, $hh_modelo, $hh_sla, $sla_hh_rsn, $hh_convenio, $hh_tipo_contrato, $array_hh_contadores, $array_hh_direccion_id, $serie_hh, $numero_delfos, $bool_convenio_insumos, $bool_convenio_corchetes, $inventoryItemId, $codeItem, $a_suppliers, $a_suppliers_full, $Rut,$contract_number,$solution_type,$sub_type);

                                //self::insertPrivateNote($incident, "saveInfoHHinsumos 1");
                                RNCPHP\ConnectAPI::commit();
                                /*
                                 Secambia para validar el rut de HH */
                                $incident_z = RNCPHP\Incident::fetch($incident->ID);
                                $org_rut=$incident_z->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut;
                                //$org_rut = $incident->PrimaryContact->Organization->CustomFields->c->rut;
                                //self::insertPrivateNote($incident, "Toket RUT : " . json_encode($incident_z->CustomFields->DOS->Direccion));
                                //self::insertPrivateNote($incident, "Toket ID  : " . json_encode($incident_z->ReferenceNumber));
                    
                                $a_request = array(
                                    "RUT" => $org_rut
                                );
                                //self::insertPrivateNote($incident, "URL  : " . self::URL_WSO2);
                                
                                
                                
                                $json_request = json_encode($a_request);
                                //self::insertPrivateNote($incident, 'RUT  : ' . $json_request);
                                $url_ws=$cfg2->Value .'/apiCloudMD/getRutStatusSAI';
                                //self::insertPrivateNote($incident, "GetDatoHHHandler [" .  $url_ws. "]");
                                $response     =ConnectUrl::requestCURLJsonRaw($url_ws, $json_request, $token); 
                                //self::insertPrivateNote($incident, "response : " .$response);
                               
                    
                                $satatusSAI=json_decode($response,false);
                                //self::insertPrivateNote($incident, "getRutStatusSAI 1");
                                RNCPHP\ConnectAPI::commit();
                               // Customer.CustomerData.Customer.tBLOQUEADO == "SI")
                    
                                //self::insertPrivateNote($incident, "response ->: " . json_encode($satatusSAI->Customer));
                                //self::insertPrivateNote($incident, "BLOQUEADO 1:" . $array_hh_direccion_id['Bloqueado']);
                                if($satatusSAI->Customer->CustomerData->Customer->tBLOQUEADO=='SI' or $satatusSAI->Customer->CustomerData->Customer->tbloqued=='Y')
                                {
                                    $array_hh_direccion_id['Bloqueado']=1;
                                }
                                else
                                {
                                    $array_hh_direccion_id['Bloqueado']=0;
                                }
                                //self::insertPrivateNote($incident, "BLOQUEADO 2:" . $array_hh_direccion_id['Bloqueado']);

                                $hh_result = self::saveInfoHHinsumos($incident, $hh_marca, $hh_modelo, $hh_sla, $sla_hh_rsn, $hh_convenio, $hh_tipo_contrato, $array_hh_contadores, $array_hh_direccion_id, $serie_hh, $numero_delfos, $bool_convenio_insumos, $bool_convenio_corchetes, $inventoryItemId, $codeItem, $a_suppliers, $a_suppliers_full, $Rut,$contract_number,$solution_type,$sub_type);

                                if($priorization=='1' )
                                {
                                    $incident->CustomFields->c->priorization='1000';
                                    $incident->Save(RNCPHP\RNObject::SuppressAll);
                                }
                                //self::insertPrivateNote($incident, "saveInfoHHinsumos 2");
                                RNCPHP\ConnectAPI::commit();
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

                                //$RUT=$incident->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut;

                                if($incident->CustomFields->c->invoice_number==0 and $hh_tipo_contrato =='Cargo')
                                {
                                    //self::insertPrivateNote($incident, "Validar Lista Blanca");
                                    
                                    $array_post     = array("rut" => $Rut    );
                                    //self::insertPrivateNote($incident, "Validar Lista Blanca  rut ----> [" . $Rut ."]");

                                   $json_data_post = json_encode($array_post);
                                    //self::insertPrivateNote($incident, "Validar Lista Blanca [" . $json_data_post ."]");
                                    
                                    $url_ws=$cfg2->Value .'/sucursalVirtual/consulta/ClientesPreferentes';
                                    $result=ConnectUrl::requestCURLJsonRaw($url_ws, $json_data_post, $token);
                                    //self::insertPrivateNote($incident, $url_ws);
                                   $respuesta=json_decode($result);
                                    //self::insertPrivateNote($incident, "Respuesta  Lista Blanca [" . $respuesta->Clientes->Cliente->status ."]");
                                        if($respuesta->Clientes->Cliente->status=='OK')
                                        {
                                            $incident->CustomFields->c->contract_number='0';
                                            $incident->Save();
                                        }
                                  
                                    
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
            /*
            if (!empty($message))
                self::insertPrivateNote($incident, $message);
*/
            $incident->Save(RNCPHP\RNObject::SuppressAll);
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

    static function saveInfoHHinsumos($incident, $marca, $modelo, $sla, $sla_rsn, $bool_convenio, $hh_tipo_contrato,$array_contadores, $array_direcciones, $serie_hh, $numero_delfos,$bool_convenio_insumos, $bool_convenio_corchetes, $inventoryItemId, $codeItem,$a_suppliers,$a_suppliers_full,$Rut,$contract_number,$solution_type,$sub_type)
    {
      try
      {
          //self::insertPrivateNote($incident, "GetDatoHH Insumos 2");
          
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
        //self::insertPrivateNote($incident, "GetDatoHH Guardando Campos");
          

      
        

        RNCPHP\ConnectAPI::commit();
        if($Rut)
        {
            $incident->CustomFields->c->order_number_om_ref = $Rut;
            //self::insertPrivateNote($incident, "RUT: " . "CustomFields.c.rut = '".$Rut ."'");
            $Organization = RNCPHP\Organization::first( "CustomFields.c.rut = '".$Rut ."'");
            $incident->Organization=$Organization;
          
        }
       
        // Campos nueva integración y corrección CPMs
        $incident->CustomFields->c->inventory_item_id = (int) $inventoryItemId;
        $incident->CustomFields->c->contract_number= $contract_number;
        $incident->CustomFields->c->solution_type= $solution_type;
        $incident->CustomFields->c->sub_type= $sub_type;

        $a_json_hh = array(
            "counters"  => $array_contadores,
            "suppliers" => $a_suppliers_full
        );

        $incident->CustomFields->c->json_hh            = json_encode($a_json_hh);

        if (!is_array($array_direcciones))
        {
          //self::insertPrivateNote($incident, "Objeto direcciones viene vacio: " . print_r($array_direcciones, TRUE));
          RNCPHP\ConnectAPI::rollback();
          return FALSE;
        }

        //self::insertPrivateNote($incident, "Validando Direccion 1");

        $array_Direccion_obj = RNCPHP\DOS\Direccion::find("d_id = {$id_ebs_direccion} LIMIT 1");
        if (count($array_Direccion_obj) > 0)
            $incident->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
        else
        {
            //self::insertPrivateNote($incident, "Dirección ID {$id_ebs_direccion} enviada por ws no se encuentra en Oracle RightNow");
            RNCPHP\ConnectAPI::rollback();
            return FALSE;
        }
        //self::insertPrivateNote($incident, "Validando Direccion 2");
        if($incident->Disposition->ID==24)
        {
            if ($bool_convenio_insumos === FALSE)
            $incident->StatusWithType->Status->ID = 185; // Evaluación convenio
            else
            $incident->StatusWithType->Status->ID = 129; // Información validada

            $incident->CustomFields->c->hh_rel_created = TRUE;
        }
       //self::insertPrivateNote($incident, "GetDatoHH Datos incidente ");
          
        RNCPHP\ConnectAPI::commit();
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
