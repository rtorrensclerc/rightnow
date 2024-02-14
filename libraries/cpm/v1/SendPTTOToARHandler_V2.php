<?php

/**
 * Skeleton Opportunity cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_2 as RNCPHP;

require_once "Labels.php";
require_once "Blowfish.php";
require_once "ConnectUrl.php";


class SendPPTOToARHandler_V2

    CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    //URL de Test
    //CONST URL          = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    //URL de Producción:
    //CONST URL          = "http://190.14.56.27:8080//dts/rn_integracion/rntelejson.php";


    static function HandleOpportunity($runMode, $action, $opportunity, $cycle)
    {

        $j=0;
        $i=0;
        $Numero_Factura='';
        $Numero_Presupuesto='';
        $LineNumber=0;
        $Correlativo=1;
        //if ($cycle !== 0) return;

        $opportunity = RNCPHP\Opportunity::fetch($opportunity->ID);
        self::insertPrivateNote($opportunity, "oportunidad " . $opportunity->ID );
        $opportunity->Save(RNCPHP\RNObject::SuppressAll);
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
        try
        {

          $a_orderItems          = RNCPHP\OP\OrderItems::find("Opportunity.ID ='{$opportunity->ID}'");
          foreach ($a_orderItems as $item)
          {
            if ($item->Enabled === false )
              continue;
            $a_tmp_result['Inventory_item_id']  = $item->Product->InventoryItemId;
            $a_tmp_result['line_id']            = $item->ID;
            $a_tmp_result['ordered_quantity']   = $item->QuantitySelected;
            $a_tmp_result['line_selling_price'] = $item->ConfirmedSellPrice;

            //Valor Unitario con descuento.
            if (!empty($item->DiscountSellPrice))
              $amount_discount = ($item->DiscountSellPrice * $item->UnitTempSellPrice)/100;
            else
              $amount_discount = 0;
            $a_tmp_result['unit_selling_price'] = $item->UnitTempSellPrice - $amount_discount;

            //$a_list_items[] = $a_tmp_result;
            // Calcula el numero de facturas a imprimir

            $a_list_items[$j][$i] = $a_tmp_result;
            $i++;
            if(!($i%15))
            {
                $j++;
                $i=0;

            }
            $LineNumber++;
          }


           // self::insertPrivateNote($opportunity, "json enviado:Lineas" . $LineNumber . "-Facturas" . count($a_list_items)  );
            $numero_facturas=count($a_list_items);
          /*  for($x=0;$x<count($a_list_items);$x++)
            {
              self::insertPrivateNote($opportunity, "json enviado: " . json_encode( $a_list_items[$x])  );
            }
*/
          for($x=0;$x<$numero_facturas ;$x++)
          {
            $z= $a_list_items[$x];

            if ($numero_facturas>=1)
            {
              $Numero_Presupuesto=$opportunity->ID . '-' . $Correlativo  . '/' . $numero_facturas;
              $Correlativo++;
            }
            else {
              $Numero_Presupuesto=$opportunity->ID ;
            }

          $array_post    = array("usuario" => "Integer",
                                  "accion"  => "setInvoiceAR",
                                  "order_detail" => array(
                                    "ref_id_ppto_rn"         => $Numero_Presupuesto,
                                    "client_rut"             => $opportunity->Organization->CustomFields->c->rut,
                                    "deliver_to_customer_id" => $opportunity->Organization->CustomFields->c->id_cliente,
                                    "Bill_to"                => $opportunity->CustomFields->OP->Direccion->d_id,
                                    "hh"                     => $opportunity->CustomFields->c->id_hh,
                                    "Batch_source_id"        => null,
                                    "Cust_trx_type_id"       => null,
                                    "Terms_id"               => 1226,
                                    "Customer_id"            => null,
                                    "salesreps_id"           => $opportunity->CustomFields->Comercial->Ejecutivo->sales_rep_id,
                                    //"salesreps_id"           => $opportunity->CustomFields->Comercial->Vendedor->CustomFields->c->resource_id, //Cambiar por relacion de objeto
                                    "Purchase_order_ref"     => $opportunity->CustomFields->c->oc_number,
                                    "list_products"          => $z
                                  ));

          $json_data_post = json_encode($array_post);

          self::insertPrivateNote($opportunity, "json enviado: ". $json_data_post);
         
          self::insertPrivateNote($opportunity, "URL : ". $cfg->Value);
          $json_data_post = Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
          $json_data_post = base64_encode($json_data_post);
          $postArray      = array ('data' => $json_data_post);
          $result         = ConnectUrl::requestPost($cfg->Value2, $postArray);

          self::insertPrivateNote($opportunity, "json devuelto: ". $result);
          //self::insertPrivateNote($opportunity, "json devuelto2: ". ConnectUrl::getResponseError());
          
          if ($result != false) // No hubo error en el servicio de actualización de precios
          {
            $arr_json  = json_decode($result, true);


            //No fallo el JSON Decode
            if ($arr_json != false)
            {
              if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json)) ))
              {
                $respuesta  = base64_decode($arr_json['respuesta']);
                switch ($arr_json['resultado'])
                {
                  case true:
                      //$json_resp       = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                      $json_resp       = $respuesta;
                      $array_data_resp = json_decode(utf8_encode($json_resp), true); //Transformar a array

                      if (!is_array($array_data_resp))
                      {
                        $message = "ERROR, problema al decofificar Respuesta ";
                        break;
                      }

                      if (!empty($array_data_resp['id_invoice_ar']))
                      {
                        if($x==0){
                            $Numero_Factura=$array_data_resp['id_invoice_ar'];
                        }
                        else {
                            $Numero_Factura=$Numero_Factura . '-' . $array_data_resp['id_invoice_ar'];
                        }

                        self::insertPrivateNote($opportunity, $Numero_Factura);
                        $opportunity->CustomFields->c->id_ar        =$Numero_Factura;

                        $opportunity->StatusWithType->Status->ID    = 11; // Estado Cerrado - Facturado
                        $opportunity->CustomFields->c->flag_send_ar = false;
                        $message                                    = "Factura ingresada con exito !!";

                        break;
                      }
                      else
                      {
                        //Codigo para exponer los arreglos
                        $a_products = $array_data_resp;
                        $var = print_r($a_products, true);
                        $message = "ID de Factura viene vacio OBJ: ".$var;
                        break;
                      }


                      break;
                  case false:
                      //$message = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                      $message = $respuesta;
                      break;
                  default:
                      $message = "ERROR: Estructura JSON No valida R".PHP_EOL;
                      $message .= "JSON: ". $result;
                      break;
                }
                self::insertPrivateNote($opportunity, $message);
              }
              else
              {
                $message = "ERROR: Estructura JSON No valida, no se encontro 'resultado' ni 'respuesta' ". PHP_EOL;
                self::insertPrivateNote($opportunity, $message);
                $opportunity->save();
              }
            }
            else
            {
              $message = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
              self::insertPrivateNote($opportunity, $message);
              $opportunity->save();
            }
          }
          else
          {
              $message = "ERROR-->: ".ConnectUrl::getResponseError();
              self::insertPrivateNote($opportunity, $message);

              $opportunity->save();
          }

        }
      }
        catch (RNCPHP\ConnectAPIError $err )
        {
           $message = "Error ".$e->getMessage();
           self::insertPrivateNote($opportunity, "Error Query: ".$message);
           self::insertBanner($opportunity, $bannerNumber);
           $opportunity->save();
        }


    }

    static function insertPrivateNote($opportunity, $textoNP)
    {
      try
      {
         $opportunity->Notes                 = new RNCPHP\NoteArray();
         $opportunity->Notes[0]              = new RNCPHP\Note();
         $opportunity->Notes[0]->Text        = $textoNP;
         $opportunity->Save(RNCPHP\RNObject::SuppressAll);
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
          return false;
      }
    }

    static function insertBanner($opportunity, $typeBanner, $texto = '')
    {
        if (!is_numeric($typeBanner) and $typeBanner > 3 and $typeBanner < 0)
            $typeBanner = 1;

        $texto = '';
        if ($typeBanner == 3)
            $texto = "Error respuesta OM";

        $opportunity->Banner->Text           = $texto;
        $opportunity->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
        $opportunity->Save(RNCPHP\RNObject::SuppressAll);
    }

}
