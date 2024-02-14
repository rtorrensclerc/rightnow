<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_2 as RNCPHP;

require_once "Labels.php";
require_once "Blowfish.php";
require_once "ConnectUrl.php";


class SendOrderToOMHandlerConv
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
                self::insertPrivateNote($incident, "-----    NO se envia a OM , Ya existe Pedido OM en proceso");
                self::ActualizaPreciosConvenio($incident);
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
              $a_tmp_result['UnitTempSellPrice']      = $item->UnitTempSellPrice;
              $a_list_items[] = $a_tmp_result;
            }
  

            //Lineas en 0
            if ($haveQuantity === false)
            {
              self::insertPrivateNote($incident, "El caso no se puede enviar a OM, porque las líneas tienen cantidades en 0");
              self::insertBanner($incident, 3);
              return;
            }

            if (!empty($incident->Disposition))
            {
              $idDisposition   = $incident->Disposition->ID;
            }
            $Instrucciones=$shipping_instructions;
            if($idDisposition==24 and strlen($Instrucciones)==0 )
            {
                  $Instrucciones=$Instrucciones . '. Entregar a: '.  $incident->PrimaryContact->LookupName  . ' Fono : ' . $incident->PrimaryContact->Phones[0]->Number;
            }

            $a_items                         = RNCPHP\OP\OrderItems::find("Incident.ID=" . $incident->ID);
           

           
        //aca debe haber un servicio que traiga todo lo que falta





        $jsonToken = ConnectUrl::geTokenTest();
        if($jsonToken === FALSE)
            return FALSE;

        // Se encapsula token como array
        //$a_jsonToken = json_decode($jsonToken, TRUE);

        //if (empty($a_jsonToken["access_token"]))
        //    throw new \Exception("Json de token inválido {$jsonToken}", 1);
    
        $token = $jsonToken;
       // https://api.dimacofi.cl/ApiTest/getSaiInfoCliente
         $a_request = array(
          "rut" => $rutClient,
          "dir_desp" => $incident->CustomFields->DOS->Direccion->party_site_number
        );
        $json_request = json_encode($a_request);
        self::insertPrivateNote($incident, $json_request);

        $url_getSaiInfoCliente=$cfg2->Value . ':8290/apiCloudMD/getSaiInfoCliente';
        $data= ConnectUrl::requestCURLJsonRaw($url_getSaiInfoCliente, $json_request,$token);
        self::insertPrivateNote($incident, "getSaiInfoCliente ----> ". $data);
        $data_client=json_decode($data);
        //self::insertPrivateNote($incident, "ActualizaPreciosConvenio");
        self::ActualizaPreciosConvenio($incident);


        $datospedido_arr=array(
        "cabecera"=> array(
          "Ship_to_org_id"=>$data_client->valores->values->DIR_DESP,   //direccion despacho   site_use_id
          "Version_number"=>0,        //fijo
          "Payment_term_id"=>$data_client->valores->values->TERM_ID,  // condiciones de pago de cliente   debe ir al contrato
          "Order_source_id"=>1082,    // Fijo
          "Order_type_id"=>1108,        //  Fijo tipó de trasaccion PED.VENTA_MATERIALE_DESPACHO_CON_FACTURA  1108  tipo_transaccion.id_erp
          "Price_list_id" => 1248236,   //  Fijo 
          "Deliver_to_customer_id"=> $data_client->valores->values->CUSTOMER_ID ,  //customer_id
          "Ship_to_customer_id"=> $data_client->valores->values->CUSTOMER_ID ,  //customer_id
          "Invoicing_rule_id"=>-2,    //fijo
          "Accounting_rule_id"=>1,    //fijo
          "Org_id"=>141,              //fijo 
          "Sold_to_org_id"=> $data_client->valores->values->CUSTOMER_ID ,  //CUSTOMER ID
          "Invoice_to_org_id"=>$data_client->valores->values->DIR_FAC, //, Direccionde facturacion  en el contrato direccionfacturacion.site_use_id
          "Salesrep_id" => $data_client->valores->values->VENDEDOR_ID_ERP, //id_ERP de oracle . de ejecutivo vendedor
          "Cust_po_number"=>"",
          "Transactional_curr_code"=>"CLP",
          "Orig_sys_document_ref"=>$incident->ReferenceNumber,
          "open_flag"=>"Y",
          "Tax_exempt_flag"=>"S",
          "Flow_status_code"=>"BOOKED",
          "Booked_flag"=>"Y",
          "shipping_instructions"=>"",
          "Ordered_date"=> date("Ymd")
        ),
        "detalle"=>array()
        
          );

      foreach($a_items as $item)
      {
        $linea=array();
        if($item->QuantitySelected>0)
        {
          $linea["Inventory_item_id"]=$item->Product->InventoryItemId;
          $linea["Ordered_quantity"]=$item->QuantitySelected;
          $linea["schedule_ship_date"]=date("Ymd");
          $linea["Ship_from_org_id"]=142;  // Fijo tipo_transaccion_linea.id_organizacion
          $linea["line_type_id"]=1054;   // Fijo id_erp tipo_transaccion_linea.id_erp
          $linea["attribute11"]="";
          $linea["attribute12"]="";
          $linea["attribute13"]="";
          $linea["attribute14"]="";
          $linea["attribute9"] ="";
          $linea["Orig_sys_line_ref"]=$incident->ReferenceNumber;
          $linea["ship_to_org_id"]=$data_client->valores->values->DIR_DESP; ///DIRECCIONDE DESCPACHO site_use_id
          $linea["shipping_instructions"]=$Instrucciones;
          $linea["attribute1"]="052";   // TIPO DE TRANSCCION id_rubro_erp   SAIP_TIPOTRANSACCION  tipo_transaccion.id_rubro_erp  1108
          $linea["attribute2"]="120";   //sub_rubro   SAIP_SUB_RUBRO   sub_rubro.id_erp
          $linea["Unit_selling_price"]=$item->UnitTempSellPrice;
          $linea["calculate_price_flag"]="N";
          array_push($datospedido_arr["detalle"],$linea );
        }
      }
      
          
            $json_data_tmp = json_encode($datospedido_arr);
            $data=array("data"=>$json_data_tmp);
            $json_data_post = json_encode($data);
            //*Inicio Json enviado
			      self::insertPrivateNote($incident, "json enviado XXX: ". $json_data_post);
            //Fin Json enviado

           
            if ($incident->CustomFields->c->order_number_om)
            {
                    self::insertPrivateNote($incident, "SendOrderToOMHandlerConv  NO se envia a OM , Ya existe Pedido OM en proceso");
             
            }
            else
            {
              
                //$result = ConnectUrl::requestPost('https://api.dimacofi.cl/ApiTest/Crea_Pedido_Conv_Om', $postArray);
                $url_Crea_Pedido_Conv_Om=$cfg2->Value . ':8290/apiCloudMD/Crea_Pedido_Conv_Om';
                $result = ConnectUrl::requestCURLJsonRaw($url_Crea_Pedido_Conv_Om, $json_data_post,$token);
                self::insertPrivateNote($incident, "--->> " . $result);
            }
            
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


    static function ActualizaPreciosConvenio($incident)
    {
      self::insertPrivateNote($incident, "Actualiza Precios");
      
      $a_items                         = RNCPHP\OP\OrderItems::find("Incident.ID = " .  $incident->ID);
      $ProductPrice=json_decode($incident->CustomFields->c->predictiondata);
      self::insertPrivateNote($incident,json_encode($a_items));
      self::insertPrivateNote($incident,"total ->" . count($ProductPrice->valores->values));
      
      if(count($ProductPrice->valores->values)>1)
      {

        foreach ($a_items as $item)
        {
            foreach($ProductPrice->valores->values as $value)
            {
              if($value->CODIGO_PRODUCTO==$item->Product->CodeItem)
              {
                //VALOR_CONVENIO
                $item->UnitTempSellPrice=round($value->VALOR_CONVENIO*$value->CONVERSION_RATE);
                $item->Save();
              }
            }
        }
      }
      else
      {
          foreach ($a_items as $item)
          {
              if($ProductPrice->valores->values->CODIGO_PRODUCTO==$item->Product->CodeItem)
              {
                //VALOR_CONVENIO
                $item->UnitTempSellPrice=round($ProductPrice->valores->values->VALOR_CONVENIO*$ProductPrice->valores->values->CONVERSION_RATE);
                $item->Save();
              }
          }
          
      }
      self::insertPrivateNote($incident,"total actualizados->" . count($ProductPrice->valores->values));
    }


}
