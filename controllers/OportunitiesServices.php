<?php



namespace Custom\Controllers;
use RightNow\Connect\v1_2 as RNCPHP;
require_once( get_cfg_var("doc_root")."/ConnectPHP/Connect_init.php" );



class OportunitiesServices extends \RightNow\Controllers\Base
{

    CONST USER = "UserDimacofi";
    static $msgError="";
    function __construct()
    {
        parent::__construct();

      
        /*$this->load->model('custom/ws/TecnicosModel'); //Modelo para acceder a tecnicos y modelo
        $this->load->model('custom/ws/TicketModel'); //Modelo para acceder a tecnicos y modelo
        $this->load->model('custom/ws/EnviromentConditions'); //Modelo para acceder a tecnicos y modelo
        $this->load->model('custom/ws/OpportunityModel');
        */



        $cfg2 = RNCPHP\Configuration::fetch( CUSTOM_CFG_WS_URL );

        $this->URL_GET_HH = $cfg2->Value;
		
    }
    private function sendResponse($response)
    {
        switch ($this->typeFormat) {
            case 'json':
                header('Content-Type: application/json');
                echo $response;
                break;
            default:
                header('Content-Type: application/json');
                echo $response;
                break;
        }
        die();
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


    private function responseError($type, $message = false)
    {

        $array_error = array ('resultado' => false, 'respuesta' => array(), 'POST' => $_POST['data']);
        $response = '';

        switch ($type) {
            case 1:
                $response =  array('Error' => 1, 'Glosa' => 'Solicitud Inesperada');
                break;
            case 2:
                $response =  array('Error' => 2, 'Glosa' => 'Cadena inesperada - Problemas en desencriptación');
                break;
            case 3:
                $response =  array('Error' => 3, 'Glosa' => 'Estructura no válida en la variable enviada');
                break;
            case 4:
                $response =  array('Error' => 4, 'Glosa' => (!empty($message)) ? $message :'Ha ocurrido un problema inesperado en la consulta'    );
                break;
            case 5:
                $response =  array('Error' => 5, 'Glosa' => 'Usuario Invalido');
                break;
            case 6:
                $response =  array('Error' => 6, 'Glosa' => 'Accion desconocida');
                break;
            case 7:
                $response =  array('Error' => 7, 'Glosa' => 'ID de tecnico no es de tipo numerico');
                break;
            case 8:
                $response =  array('Error' => 8, 'Glosa' => 'ID de ticket desconocido o no presente en Oracle RightNow');
                break;
            case 9:
                $response =  array('Error' => 9, 'Glosa' => 'ID de ticket no valido, no se encuentra en estado previo requerido');
                break;
            case 10:
                $response =  array('Error' => 7, 'Glosa' => 'Estado no es de tipo booleano');
                break;
            case 11:
				$response =  array('Error' => 11, 'Glosa' => 'HH Invalida');
				break;
			case 12:
				$response =  array('Error' => 12, 'Glosa' => 'Orden Activacion Invalida');
				break;
			case 13:
				$response =  array('Error' => 13, 'Glosa' => 'Orden Activacion o HH no puede ir Vacia');
	            break;
            default:
                $response =  array('Error' => 1, 'Glosa' => 'Solicitud Inesperada');
                break;
        }



        if ($this->responseEncripted == true)
        {
            $response = $this->blowfish->encrypt(json_encode($response), self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
            $array_error['respuesta'] = base64_encode($response);
            $responseEncode = json_encode($array_error);
        }
        else
        {
            $array_error['respuesta'] = $response;
            $responseEncode = json_encode($array_error);
        }

        return $responseEncode;
    }


    /* Servicio para actualizar PTTO desde WSO2 */
    public function setOAPTTO()
    {
      //$data_post  = $_POST;
      $data = json_decode(file_get_contents('php://input'), true);

      $array_data = $data;
      //$this->sendResponse(json_encode($data));
      
      
      if (is_array($array_data) and ($array_data != false))
      {
        
        
        $array_data=$data;
      }
      else
      {
       $data=json_decode(' {"usuario":"UserDimacofi","accion":"setOAPTTO","status":"true","estado":"PENDIENTE_FINANZAS","oa":"67147","mensaje":"OA generada exitosamente","referencia_externa":"RNPTTO-23294"}',true);
            $array_data=$data;
            
      }


      
      if (is_array($array_data) and ($array_data != false))
        {
           
            $indiceDatos = 'datos';
            $indiceAccion = 'accion';
            $indiceUsuario = 'usuario';
            
            if (!array_key_exists($indiceAccion, $array_data) and !array_key_exists($indiceUsuario, $array_data) )
            {
                $response = $this->responseError(3);
                $this->sendResponse($response);
            }


            if ($array_data[$indiceUsuario] != self::USER)
            {
                $response = $this->responseError(5);
                $this->sendResponse($response);
            }

            if ($array_data[$indiceAccion] != "setOAPTTO" and $array_data[$indiceAccion] != "setOAPTTOOK" and $array_data[$indiceAccion] != "setOAPTTONOOK" )
            {
                $response = $this->responseError(6);
                $this->sendResponse($response);
            }

            
            switch($array_data[$indiceAccion] )
            {

              case "setOAPTTO":
                    $idx_status='status';
                    $idx_estado='estado';
                    $idx_oa = 'oa';
                    $idx_mensaje = 'mensaje';
                    $idx_referencia_externa = 'referencia_externa';

                    $status = $array_data[$idx_status];
                    $estado = $array_data[$idx_estado];
                    $oa = $array_data[$idx_oa];  
                    $mensaje = $array_data[$idx_mensaje];  
                    $referencia_externa = $array_data[$idx_referencia_externa];  

                    $todos=explode("-",$referencia_externa);
                    $ptto=$todos[1];
                  

                    
                    $opportunity = RNCPHP\Opportunity::fetch($ptto);
                    //$this->insertPrivateNote($opportunity, "MENSAJE [ " . json_encode($array_data) . "]");
                    //$this->IncidentNota($orderMessage,$incident);
                    //$this->sendResponse(json_encode($data) . " ID  " .  $nro_referencia . " OM_Number " . $orderOmNumber. " OM_Message " . $orderMessage );
                  
                    if ($status=='true') 
                    {
                     // $$opportunity->CustomFields->c->order_number_om = $orderOmNumber;
               
                      $this->insertPrivateNote($opportunity, $mensaje .' '. $oa);
                      $opportunity->CustomFields->c->id_ar=$oa;
                      $opportunity->save();
                    }
                    else
                    {
                  
                      $this->insertPrivateNote($opportunity, "setOAPTTO  " . $mensaje);
                    }
                    $array_result = array('resultado' => $status);
                    $result = json_encode($array_result);
                    $this->sendResponse($result);
                    break;
                case "setOAPTTOOK":
                  $idx_status='status';
                  $idx_estado='estado';
                  $idx_oa = 'oa';
                  $idx_mensaje = 'mensaje';
                  $idx_referencia_externa = 'referencia_externa';

                  $status = $array_data[$idx_status];
                  $estado = $array_data[$idx_estado];
                  $oa = $array_data[$idx_oa];  
                  $mensaje = $array_data[$idx_mensaje];  
                  $referencia_externa = $array_data[$idx_referencia_externa];  

                  $todos=explode("-",$referencia_externa);
                  $ptto=$todos[1];
                

                  
                  $opportunity = RNCPHP\Opportunity::fetch($ptto);
                  //$this->IncidentNota($orderMessage,$incident);
                  //$this->sendResponse(json_encode($data) . " ID  " .  $nro_referencia . " OM_Number " . $orderOmNumber. " OM_Message " . $orderMessage );
                
                  if ($status=='true') 
                  {
                   // $$opportunity->CustomFields->c->order_number_om = $orderOmNumber;
             
                    $this->insertPrivateNote($opportunity, $mensaje .' '. $oa);
                    $opportunity->StatusWithType->Status->ID=14;
                    $opportunity->save();
                  }
                  else
                  {
                
                    $this->insertPrivateNote($opportunity, "setOAPTTOOK " . $mensaje);
                    $opportunity->save();
                  }
                  $array_result = array('resultado' => $status);
                  $result = json_encode($array_result);
                  $this->sendResponse($result);
                  break;
                case "setOAPTTONOOK":
                  $idx_status='status';
                    $idx_estado='estado';
                    $idx_oa = 'oa';
                    $idx_mensaje = 'mensaje';
                    $idx_referencia_externa = 'referencia_externa';

                    $status = $array_data[$idx_status];
                    $estado = $array_data[$idx_estado];
                    $oa = $array_data[$idx_oa];  
                    $mensaje = $array_data[$idx_mensaje];  
                    $referencia_externa = $array_data[$idx_referencia_externa];  

                    $todos=explode("-",$referencia_externa);
                    $ptto=$todos[1];
                  

                    
                    $opportunity = RNCPHP\Opportunity::fetch($ptto);
                    //$this->IncidentNota($orderMessage,$incident);
                    //$this->sendResponse(json_encode($data) . " ID  " .  $nro_referencia . " OM_Number " . $orderOmNumber. " OM_Message " . $orderMessage );
                  
                    if ($status=='false') 
                    {
                     // $$opportunity->CustomFields->c->order_number_om = $orderOmNumber;
               
                      $this->insertPrivateNote($opportunity, $mensaje .' '. $oa);
                      
                      $opportunity->StatusWithType->Status->ID=15;
                      $opportunity->save();
                    }
                    else
                    {
                  
                      $this->insertPrivateNote($opportunity, "setOAPTTONOOK " . $mensaje);
                      $opportunity->save();
                    }
                    $array_result = array('resultado' => $status);
                    $result = json_encode($array_result);
                    $this->sendResponse($result);
                  break;
                  default:
                  $this->insertPrivateNote($opportunity, "ERROR en mensaje " . json_encode($array_data));
                  break;
            }

        }
        else
        {
            
          $array_result = array('resultado' => false);
          $result = json_encode($array_result);
          $this->sendResponse($result);
        }

    }

    

    public function SendPPTOToAR()
    {

        $this->load->library('ConnectUrl');
        $j=0;
        $i=0;
        $Numero_Factura='';
        $Numero_Presupuesto='';
        $LineNumber=0;
        $Correlativo=1;

        $op_id = getUrlParm('op_id');

        $opportunity = RNCPHP\Opportunity::fetch( $op_id);
        
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
        try
        {
        $this->insertPrivateNote($opportunity, "oportunidad " . $op_id  . "  URL " . $cfg->Value) ;
        $opportunity->Save(RNCPHP\RNObject::SuppressAll);
        


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
            for($x=0;$x<$numero_facturas ;$x++)
            {
              $z= $a_list_items[$x];
                
              if ($numero_facturas>=1)
              {
                $Numero_Presupuesto=$opportunity->ID . '-' . $Correlativo  . '/' . $numero_facturas;
                $Correlativo++;
              }
              else 
              {
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
  
                $this->insertPrivateNote($opportunity, "json enviado: ". $json_data_post);
              

                $json_data_post = base64_encode($json_data_post);
                $postArray      = array ('data' => $json_data_post);
        
               $result = $this->connecturl->requestPost($cfg->Value, $postArray);

  
                $this->insertPrivateNote($opportunity, "json devuelto: ". $result );
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
  
                              $this->insertPrivateNote($opportunity, $Numero_Factura);
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
                      $this->insertPrivateNote($opportunity, $message);
                    }
                    else
                    {
                      $message = "ERROR: Estructura JSON No valida, no se encontro 'resultado' ni 'respuesta' ". PHP_EOL;
                      $this->insertPrivateNote($opportunity, $message);
                      $opportunity->save();
                    }
                  }
                  else
                  {
                    $message = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
                    $this->insertPrivateNote($opportunity, $message);
                    $opportunity->save();
                  }
                }
                else
                {
                    //$message = "ERROR-->: ".ConnectUrl::getResponseError();
                    $this->insertPrivateNote($opportunity, $message);
  
                    $opportunity->save();
                }
  
              }

            }
            catch (RNCPHP\ConnectAPIError $err )
            {
              //$message = "Error ".$err->getMessage();
              self::insertPrivateNote($opportunity, "Error Query: ".$message);
            
              $opportunity->save();
            }

            $this->sendResponse(json_encode($opportunity));
    }
}     