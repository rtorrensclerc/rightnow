<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_2 as RNCPHP;

require_once "Labels.php";
require_once "Blowfish.php";
require_once "ConnectUrl.php";


class SendOrderToOMHandler
{
    CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    //URL de TEST:
    //CONST URL          = "http://190.14.56.27/public/rn_integracion/rntelejson.php";
    //URL de Producción:
    //CONST URL          = "http://190.14.56.27:8080//dts/rn_integracion/rntelejson.php";
    //CONST URL   = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";


    static function HandleIncident($runMode, $action, $incident, $cycle)
    {

        if ($cycle !== 0) return;
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
        $incident = RNCPHP\Incident::fetch($incident->ID);
        if ($incident->CustomFields->c->order_number_om)
        {
                self::insertPrivateNote($incident, " NO se envia a OM , Ya existe Pedido OM en proceso");
                return;
        }
        try
        {
            $HH                    = $incident->CustomFields->c->id_hh;
            $rutClient             = '';
            $fatherIncident        = '';
            $type_order            = '';
            $refNumber             = $incident->ReferenceNumber;
            $shipping_instructions = $incident->CustomFields->c->shipping_instructions;
            $type_contract         = $incident->CustomFields->c->tipo_contrato;
            $request_date_om       = $incident->CustomFields->c->request_date_om;
            if (!empty($incident->CustomFields->DOS->Direccion))
              $rutClient      = $incident->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut;

              /*  20210216 - RTC -  Se modifica codigo para que en insumos envie el mismo indentificador como referencia
                                        "ref_number_order"      => $refNumber,
                                        "ref_number_ticket"     => $fatherIncident,
              */
            switch ($incident->Disposition->ID) {
              case 24:
                   $fatherIncident = $incident->ReferenceNumber;
                break;
              case 41:
                $fatherIncident = $incident->CustomFields->OP->Incident->ReferenceNumber;
               break;
              case 47:
                $fatherIncident = $incident->CustomFields->OP->Incident->ReferenceNumber;
               break;
              default:
                if (!empty($incident->CustomFields->OP->Incident)) 
                {
                  // Insumos Múltiples
                  if ($incident->Disposition->ID === 70) 
                    $fatherIncident = $incident->ReferenceNumber;
                  else
                    $fatherIncident = $incident->CustomFields->OP->Incident->ReferenceNumber;
                }
                else if ($incident->Disposition->ID === 24)
                {
                  $fatherIncident = $incident->ReferenceNumber;
                }
                break;
            }  
            
            

/*
            if (!empty($incident->CustomFields->OP->Incident)) 
            {
              // Insumos Múltiples
              if ($incident->Disposition->ID === 70) 
                $fatherIncident = $incident->ReferenceNumber;
              else
                $fatherIncident = $incident->CustomFields->OP->Incident->ReferenceNumber;
            }
            else if ($incident->Disposition->ID === 24)
            {
              $fatherIncident = $incident->ReferenceNumber;
            }
            */

            if (!empty($incident->Disposition))
            {
              $idDisposition   = $incident->Disposition->ID;
              switch ($idDisposition) {
                case 41:
                  $type_order = 'servicio';
                  break;
                case 40:
                  $type_order = 'taller';
                  break;
                case 47:
                  $type_order = 'cargo';
                  break;
                case 24:
                  $type_order = 'insumos';
                  $request_date_om = time();
                  break;
                case 70:
                  $type_order = 'insumos';
                  $request_date_om = time();
                  break;
                default:
                  $type_order = '';
                  break;
              }
            }

            $shipToCustomerID      = $incident->CustomFields->DOS->Direccion->d_id;
            $a_orderItems          = RNCPHP\OP\OrderItems::find("Incident.ID ='{$incident->ID}'");
            $a_list_items          = array();

            $haveQuantity = false;
            foreach ($a_orderItems as $item)
            {
              if ($item->QuantitySelected > 0)
              {
                $haveQuantity = true;
              }

              if ($item->Enabled === false )
                continue;
              $a_tmp_result['line_id']           = $item->ID;
              $a_tmp_result['Inventory_item_id'] = $item->Product->InventoryItemId;
              $a_tmp_result['ordered_quantity']  = $item->QuantitySelected;
              $a_tmp_result['line_type_id']      = $item->Product->CategoryItem->LookupName;
              $a_list_items[] = $a_tmp_result;
            }

            //Lineas en 0
            if ($haveQuantity === false)
            {
              self::insertPrivateNote($incident, "El caso no se puede enviar a OM, porque las líneas tienen cantidades en 0");
              self::insertBanner($incident, 3);
              return;
            }
            $Instrucciones=$shipping_instructions;
            if($idDisposition==24 and strlen($Instrucciones)==0 )
            {
                  $Instrucciones=$Instrucciones . '. Entregar a: '.  $incident->PrimaryContact->LookupName  . ' Fono : ' . $incident->PrimaryContact->Phones[0]->Number;
            }

            if($incident->CustomFields->OP->Incident->CustomFields->c->ar_flow->ID==282)
            {
              $incident->CustomFields->OP->Incident->AssignedTo->Account=$incident->AssignedTo->Account;
              $incident->CustomFields->OP->Incident->Save(RNCPHP\RNObject::SuppressAll);
            }
            
            $array_post     = array("usuario" => "Integer",
                                    "accion"  => "setOrderOM",
                                    "order_detail" => array(
                                      "ref_number_order"      => $refNumber,
                                      "ref_number_ticket"     => $fatherIncident,
                                      "client_rut"            => $rutClient,
                                      "type_order"            => $type_order,
                                      "type_contract"         => $type_contract,
                                      "hh"                    => $HH,
                                      "shipping_instructions" => $Instrucciones,
                                      "ship_to_customer_id"   => $shipToCustomerID,
                                      "request_name"          => $incident->AssignedTo->Account->DisplayName,
                                      "is_web"                => ($incident->Source->ID === 3019)?true:false,
                                      "contact" => array(
                                          "id"         => $incident->PrimaryContact->ID,
                                          "first_name" => $incident->PrimaryContact->Name->First,
                                          "last_name"  => $incident->PrimaryContact->Name->Last,
                                          "email"      => $incident->PrimaryContact->Emails[0]->Address,
                                          "phone"      => $incident->PrimaryContact->Phones[0]->Number
                                      ),
                                      "request_id"            => $incident->AssignedTo->Account->CustomFields->c->resource_id,
                                      "request_date_om"       => date("Ymd H:i:s",$request_date_om),
                                      "list_products"         => $a_list_items
                                    ));


            $json_data_post = json_encode($array_post);
            /*Inicio Json enviado*/
			      self::insertPrivateNote($incident, "json enviado: ". $json_data_post);
            /*Fin Json enviado*/



            $json_data_post = Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
            $json_data_post = base64_encode($json_data_post);

            $postArray = array ('data' => $json_data_post);

            //$result = ConnectUrl::requestPost(self::URL, $postArray);
            /* if($incident->Disposition->ID<>24)
             {
              if(
                $incident->CustomFields->c->flag_restriction_ok === null ||
                $incident->CustomFields->c->flag_amount_ok === null ||
                $incident->CustomFields->c->flag_budget_ok === null
              ){
                self::insertPrivateNote($incident, " NO se envia a OM , Evaluacion sin terminar");
                return;
              }
             }
             */
            /*
              Preguntar por los tres flag que no esten nulos 
            */

            if ($incident->CustomFields->c->order_number_om)
            {
                    self::insertPrivateNote($incident, "2 NO se envia a OM , Ya existe Pedido OM en proceso");
                    return;
            }
            else
            {
                $result = ConnectUrl::requestPost($cfg->Value, $postArray);
            }
            
            //$result = ConnectUrl::requestPost('https://api.dimacofi.cl/cloud/mq/transaction', $postArray);
            self::insertPrivateNote($incident, "--->> " . $result);
            
            if ($result != false)
            {
                $arr_json = json_decode($result, true);

                if ($arr_json != false)
                {
                    $respuesta  = $arr_json['result'];
                  
                        //$json = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                        //self::insertPrivateNote($incident, "json respuesta 1:".$arr_json['resultado']);
                        //return;
                        if($respuesta=="OK")
                        {
                          $message = $respuesta  = $arr_json['message'];
                          $bannerNumber = 1;
                          self::insertPrivateNote($incident, $message);
                          self::insertBanner($incident, $bannerNumber);
                          $orderOmNumber="P" . time();
                          $incident->CustomFields->c->order_number_om = $orderOmNumber;
                          $incident->Save(RNCPHP\RNObject::SuppressAll);
                        }
                        else 
                        {
                          $message = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
                          $bannerNumber = 3;
                          //self::insertPrivateNote($incident, $message);
                          //self::insertBanner($incident, $bannerNumber);
                        }
               
                }
                else
                {
                    $message = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
                    $bannerNumber = 3;
                }

            }
            else {
                $message = "ERROR: ".ConnectUrl::getResponseError();
                $bannerNumber = 3;
            }


            self::insertPrivateNote($incident, $message);
            self::insertBanner($incident, $bannerNumber);

        }
        catch (RNCPHP\ConnectAPIError $err )
        {
             $message = "Error ".$e->getMessage();
             self::insertPrivateNote($incident, "Error Query: ".$message);
             self::insertBanner($incident, $bannerNumber);
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
