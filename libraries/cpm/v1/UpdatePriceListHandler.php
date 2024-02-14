<?php

/**
 * Skeleton Opportunity cpm handler.
 */

namespace Custom\Libraries\CPM\v1;



require_once "Labels.php";
require_once "Blowfish.php";
require_once "ConnectUrl.php";
use RightNow\Connect\v1_3 as RNCPHP;

class UpdatePriceListHandler
{
    CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    //URL de Test
    //CONST URL          = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    
    //URL de Producción:
    //CONST URL          = "http://190.14.56.27:8080//dts/rn_integracion/rntelejson.php";


    static function HandleOpportunity($runMode, $action, $opportunity, $cycle)
    {
        
        if ($cycle !== 0) return;
        
        $opportunity = RNCPHP\Opportunity::fetch($opportunity->ID);
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
        $cfg3 = RNCPHP\Configuration::fetch( 1000025 ); //CUSTOM_CFG_PORCENTAJE_DOLAR  PORCENTAJE DE CALCULO DOLAR ejemplo +3%


        // $i=0;
        try
        {

          if($opportunity->CustomFields->c->source_opp->ID==305)
          {
  
            $tokenA=self::getToken();
            
            $a_jsonToken = json_decode($tokenA, TRUE);
            $token = $a_jsonToken["access_token"];
            $jsonDataEncoded='{}';
            
            //self::insertPrivateNote($opportunity, "-------apiCloudMD-------------------->service->> ". $token);
            $url=$cfg2->Value;

           
              $service=ConnectUrl::requestCURLJsonRaw($cfg2->Value ."/apiCloudMD/getUSDValue", $jsonDataEncoded, $token);
              
              $data=json_decode($service);
            if($opportunity->StatusWithType->Status->ID==180 or $opportunity->StatusWithType->Status->ID==9)
            {
              $opportunity->CustomFields->c->dolar_value=$data->dolar->values->CONVERSION_RATE ;
              //self::insertPrivateNote($opportunity, "--------------------------->service getUSDValue->> ". $service);
              $opportunity->save();
            }
  
           

            $a_orderItems          = RNCPHP\OP\OrderItems::find("Opportunity.ID =" . $opportunity->ID);
            $jsonDataEncoded  = '{"condition":"\'-1\'';
             
              
           
            foreach ($a_orderItems as $key =>  $item)
            {
              if ($item->Enabled === false )
                continue;
                
              $jsonDataEncoded  = $jsonDataEncoded . ',\'' . $item->Product->CodeItem . '\'';

            }

            $jsonDataEncoded  = $jsonDataEncoded .'"}';
           
            //self::insertPrivateNote($opportunity, "--------------------------->apiCloudMD->> ". $jsonDataEncoded);
            $service=ConnectUrl::requestCURLJsonRaw($cfg2->Value ."/apiCloudMD/getItemsUSDValue", $jsonDataEncoded, $token);
            //self::insertPrivateNote($opportunity, "--------------------------->apiCloudMD  getItemsUSDValue->> ". $service );
            
            // Aca deberia actualizar el dolar  y recalcular precio
           
            $data=json_decode($service);
            
           

            $opportunity->name='PPTO EN Dolares - ' . $opportunity->ID  . '-' . date('Y-m-d');
            $opportunity->save();
            //self::insertPrivateNote($opportunity, "----------------apiCloudMD----------->service->> ". $service);
        
            if($data->dolar->values->VALOR_US>0)
            {
                
               
                $a_orderItems[0]->dolar_value=$data->dolar->values->VALOR_US*((100+$cfg3->Value)/100);
                $a_orderItems[0]->temp_stock=$data->dolar->values->SALDO;
                $a_orderItems[0]->item_dolar_value=number_format($data->dolar->values->VALOR_US*((100+$cfg3->Value)/100) ,2);
                //$a_orderItems[0]->UnitTempSellPrice=$a_orderItems[0]->dolar_value*$opportunity->CustomFields->c->usd_value;
                $a_orderItems[0]->Save();
                //$a_orderItems->save();
                //self::insertPrivateNote($opportunity, "ACTUALIZA DOLAR " . $a_orderItems[0]->UnitTempSellPrice . ' ' . $a_orderItems[0]->UnitTempSellPrice);
                $opportunity->Save();
                
            }
            else
            {
              foreach ($data->dolar->values as $key => $valor)
              {
                //self::insertPrivateNote($opportunity, "valor ->> ". json_encode($valor));
                foreach ($a_orderItems as $item)
                {
                  if ($item->Enabled === false )
                    continue;
                    
                  if($item->Product->CodeItem==$valor->CODIGO_PRODUCTO)
                  {
                    //$item->dolar_value=$valor->VALOR_US*(100+$cfg3->Value)/100;
                    $item->temp_stock=$valor->SALDO;
                    $dolar_value= number_format($valor->VALOR_US*(100+$cfg3->Value)/100 ,2);
                    //self::insertPrivateNote($opportunity, "valor UNICO USD ------->> ". $dolar_value);
                    $item->item_dolar_value= $dolar_value;
                    $item->save();
                   
                  }

                }
              }
              $opportunity->save();
            }
            //self::insertPrivateNote($opportunity, "LISTO--->> ");


            $org_rut = $opportunity->Organization->CustomFields->c->rut;

            if($org_rut)
            { 
              $a_request = array(
                  "RUT" => $org_rut
              );
              $jsonDataEncoded  = json_encode($a_request);
              //self::insertPrivateNote($opportunity, "JSON ->> ".$jsonDataEncoded );
              $url=$cfg2->Value . '/apiCloudMD/getRutStatusSAI';
              $service=ConnectUrl::requestCURLJsonRaw($url, $jsonDataEncoded, $token);
              //self::insertPrivateNote($opportunity, "JSON ->> ".$service );
              
              $url=$cfg2->Value . '/apiCloudMD/UpdateRutData';
              $ActualizaRut=ConnectUrl::requestCURLJsonRaw($url, $jsonDataEncoded, $token);
              
              $estadocliente=json_decode($service);
              
              
              $opportunity->CustomFields->c->id_venus=$estadocliente->Customer->CustomerData->Customer->tBLOQUEADO;
              if($opportunity->CustomFields->c->id_venus=='SI')
              {
                
                foreach($estadocliente->Invoice->InvoiceData->Invoices as $s)        
                {
                  /*$origin = date_create($s->DUE_DATE);
                  $now = date('d-m-y');
                  $interval = date_diff($origin,$target);
                  echo  $interval .'<br>' ;
                  */
                  
            //self::insertPrivateNote($opportunity, "valor UNICO ->> ". $valor->VALOR_US );
                  $data=explode('/',$s->DUE_DATE);
                  $dateString = $data[2].'-'.$data[1].'-'.$data[0];
                  $currentDate = date('Y-m-d'); // Fecha actual en formato 'Y-m-d'
            
                  $dateTimestamp = strtotime($dateString);
                  $currentTimestamp = time();
            
                  $secondsDifference = $dateTimestamp - $currentTimestamp;
                  $monthsDifference = floor($secondsDifference / (30 * 24 * 60 * 60));
                  //echo "<br>[" . $monthsDifference . "]" . $s->AMOUNT . ' ';
            
                  if ($monthsDifference >= -1) {
                     //self::insertPrivateNote($opportunity, "valor UNICO ->> ". $valor->VALOR_US );
                     //self::insertPrivateNote($opportunity, "NO SUMA  ->> ".$s->AMOUNT  );
                  } else {
                      $TOTAL=$TOTAL+$s->AMOUNT;
                    //self::insertPrivateNote($opportunity, "SUMA  ->> ".$s->AMOUNT .' a '. $TOTAL );
                  }
                  
                }
                if ($TOTAL>0)
                {
                    self::insertBanner($opportunity, 3,"Cliente Bloquedo . Deuda " .number_format($TOTAL));
                }
                else
                {
                    self::insertBanner($opportunity, 3,"Cliente Bloquedo");
                }
              }
              else
              {
                self::insertBanner($opportunity, 1,"");
              }

              switch ($estadocliente->Customer->CustomerData->Customer->tTERMINO_PAGO)
              {
                case 'Contado':
                  $opportunity->CustomFields->c->payment_conditions->ID=72;
                  break;
                case '30 Dias':
                  $opportunity->CustomFields->c->payment_conditions->ID=71;
                  break;
                default:
                  $opportunity->CustomFields->c->payment_conditions->ID=72;
                  break; 

              }
            }

            
            $opportunity->Save();
          }
          else
          {
            //$i=1;
            //self::insertPrivateNote($opportunity, "1-->opportunity->ID: ". json_encode($opportunity->ID));

            $a_orderItems          = RNCPHP\OP\OrderItems::find("Opportunity.ID =" . $opportunity->ID );
            //$i=2;
            //self::insertPrivateNote($opportunity, "2 - a_orderItems: ". json_encode($a_orderItems));
            foreach ($a_orderItems as $item)
            {
              if ($item->Enabled === false )
                continue;
              $a_tmp_result['Inventory_item_id'] = $item->Product->InventoryItemId;
              $a_list_items[] = $a_tmp_result;
            }
            //$i=3;
            //self::insertPrivateNote($opportunity, "3 -a_list_items: ". json_encode($a_list_items));
            $array_post     = array("usuario" => "Integer",
                                    "accion"  => "updateItemsPrice",
                                    "order_detail" => array(
                                      "list_products"         => $a_list_items
                                    ));

            $json_data_post = json_encode($array_post);

            //self::insertPrivateNote($opportunity, "json enviado: ". $json_data_post );
            //$i=4;
            //self::insertPrivateNote($opportunity, "URL : ". $cfg->Value);
        
            $json_data_post = Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
            $json_data_post = base64_encode($json_data_post);
            $postArray      = array ('data' => $json_data_post);
            $result         = ConnectUrl::requestPost($cfg->Value, $postArray);

            //self::insertPrivateNote($opportunity, "json devuelto: ". $result);

        
            if ($result != false) // No hubo error en el servicio de actualización de precios
            {
              $arr_json  = json_decode($result, true);
              //$respuesta = base64_decode($arr_json['respuesta']);
              //$json      = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
              //self::insertPrivateNote($opportunity, "json Repuesta: ". $json);

              //No fallo el JSON Decode
              if ($arr_json != false)
              {
                if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json)) ))
                {
                  $respuesta  = base64_decode($arr_json['respuesta']);
                  switch ($arr_json['resultado'])
                  {
                    case true:
                        $json_resp       = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                        $array_data_resp = json_decode(utf8_encode($json_resp), true); //Transformar a array

                        if (!is_array($array_data_resp))
                        {
                          $message = "ERROR, problema al decofificar Respuesta ";
                          break;
                        }


                        //Codigo para exponer los arreglos
                        $a_products = $array_data_resp['order_detail']['list_products'];
                        $var = print_r($a_products, true);
                        //self::insertPrivateNote($opportunity, "OBJ LINE: ". $var);

                    
                        //Codigo de actualización de precios

                        $exist_lines = false;
                        //$i=5; 
                        foreach ($a_products as $key => $product) {
                          
                          $exist_lines = true;
                          $itemID                        = $product['Inventory_item_id'];
                          //self::insertPrivateNote($opportunity, "1111 Salvando : ". $product['unit_selling_price']);
                          //$i=6; 
                          //Actualizando precio producto
                          if($product['unit_selling_price']>=0)
                          {
                          //  $i=7; 
                          $obj_product                   = RNCPHP\OP\Product::first("InventoryItemId =" . $itemID );
                          $obj_product->UnitSellingPrice = $product['unit_selling_price'];
                          $obj_product->Save();
                         // $i=8; 
                          //Actualizando precio linea
                          $idOP                          = $opportunity->ID;
                          $obj_line                      = RNCPHP\OP\OrderItems::first("Product.ID =".  $obj_product->ID . " and Opportunity.ID=" . $idOP);
                          $obj_line->UnitTempSellPrice   = $product['unit_selling_price'];
                          $obj_line->Save();
                          //$i=9; 
                        
                          }
                        }
                        //$i=10;
                        if ($exist_lines === true)
                          $message = "Los precios de los productos y las lineas han sido actualizados con exito";
                        else
                          $message = "No se encontraron lineas para actualizar";

                        break;
                    case false:
                        $message = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
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
                  //$message .= "JSON: ". $result;
                  self::insertPrivateNote($opportunity, $message);
                }
              }
              else
              {
                $message = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
                self::insertPrivateNote($opportunity, $message);
              }
            }
            else
            {
                $message = "ERROR: ".ConnectUrl::getResponseError();
                self::insertPrivateNote($opportunity, $message);
            }

          }
        }
        catch (RNCPHP\ConnectAPIError $err )
        {
           $bannerNumber=1;
           $message = "Error " . $err->getMessage() ;
           self::insertPrivateNote($opportunity, "Error Query: ". $i . " " .$message);
           self::insertBanner($opportunity, $bannerNumber);
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

        $opportunity->Banner->Text           = $texto;
        $opportunity->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
        $opportunity->Save(RNCPHP\RNObject::SuppressAll);
    }

    
    static function getToken()
    {
        try 
        {
            //$url = "https://api.dimacofi.cl/token";
            $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
            $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
            $data           = array("grant_type" => "client_credentials");
            $consumerKey    = "yh8wgLIb4RLIHwQ868CIifi2EYca"; // Prod 
            $consumerSecret = "bfaZkjfdIWoEtiXoDbo4E_EPpAka"; // Prod
         
            $url_token=$cfg2->Value . '/token';
            
            $service = ConnectUrl::requestCURLByPost($url_token, $data, $consumerKey . ":" . $consumerSecret);

            if (is_bool($service)) 
            {
                $this->errorMessage = "Error obteniendo token Dimacofi " . $this->CI->ConnectUrl->getResponseError();
                return FALSE;
            } 
            else 
            {
                return $service;
            }
        } 
        catch (RNCPHP\ConnectAPIError $err) 
        {
            $this->errorMessage  = "Codigo : " . $err->getCode() . " ";// . $err->getMessage();
            return FALSE;
        }
    }

}
