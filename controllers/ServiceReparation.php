<?php

namespace Custom\Controllers;
use RightNow\Connect\v1_2 as RNCPHP;

class ServiceReparation extends \RightNow\Controllers\Base
{
    protected $typeFormat        = 'json';
    public    $a_validActions    = array (1, 2, 3);
    public $URL_GET_HH ="";
    function __construct()
    {
        parent::__construct();
        //$this->validAccountLogged();
        $cfg2 = RNCPHP\Configuration::fetch( CUSTOM_CFG_WS_URL );

        $this->URL_GET_HH = $cfg2->Value;
        $this->load->model('custom/ws/TicketReparation'); //Modelo para acceder a tecnicos y modelo
        load_curl();
    }

    static function requestCURLByPost($url, $postArray)
      {
        # Form data string
        if (is_array($postArray))
          $postString = http_build_query($postArray, '', '&');


         $ch = curl_init($url);

        # Setting our options

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 500);
        //curl_setopt($ch, 156, 500);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        # Get the response
        $response = curl_exec($ch);

        if(curl_errno($ch))
        {
          $info = curl_getinfo($ch);
          self::$msgError = curl_error($ch);
          //self::$msgError .='<br>Tiempo ' . $info['total_time'] . ' segundos en recibir la respuesta de la siguiente URL: ' . $info['url'];
          curl_close($ch);
          return false;
        }

        if ($response != false)
        {
          $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);
          if ($statusCode != '200')
          {
            self::$msgError = 'No se pudo resolver la petici?n a la URL 1, codigo de Error: '. $statusCode;
            return false;
          }
          else
            return $response;
        }
        else
        {
          curl_close($ch);
          self::$msgError = 'No se pudo resolver la petici?n a la URL 2';
          return false;
        }
      }
      static function requestFileGetContentByPost($url, $postArray)
        {

            $headers = @get_headers($url);
            $statusCode = substr($headers[0], 9, 3);
            if($statusCode != '200'){
                self::$msgError = 'No se pudo resolver la petici?n a la URL 3, codigo de Error: '. $statusCode;
                return false;
            }

            if (is_array($postArray))
                $postString =  http_build_query($postArray);

            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' =>  $postString
                )
            );

            $context  = stream_context_create($opts);
            $result   = file_get_contents($url, false, $context);



            return $result;
        }

    static function requestPost($url, $postArray, $typeRequest ='CURL')
      {
          switch ($typeRequest) {
              case 'CURL':
                  return self::requestCURLByPost($url, $postArray);
                  break;
              case 'FileGetContent':
                  return self::requestFileGetContentByPost($url, $postArray);
                  break;
              default:
                  return self::requestCURLByPost($url, $postArray);
                  break;
          }
      }
    public function setOrder()
    {
      if (empty($_POST))
      {
        $response = $this->responseError(1);
        $this->sendResponse($response);
      }

      if(empty($_POST['data']))
      {
        $response = $this->responseError(2);
        $this->sendResponse($response);
      }

      $data = trim($_POST['data']);
      $array_data = json_decode($data, true);

      if (  !is_array($array_data) or !array_key_exists('order_detail', $array_data)
          or !array_key_exists('ref_no', $array_data['order_detail'])
          or !array_key_exists('father_ref_no', $array_data['order_detail'])
          or !array_key_exists('action', $array_data['order_detail'])
          or !array_key_exists('list_items', $array_data['order_detail']))
      {
        $response = $this->responseError(3, print_r($data, true));
        $this->sendResponse($response);
      }

      $orderDetail = $array_data['order_detail'];

      if (!is_string($orderDetail['father_ref_no']) or !is_array($orderDetail['list_items']) or !is_numeric($orderDetail['action']))
      {
        $response = $this->responseError(4);
        $this->sendResponse($response);
      }

      $fatherRefNo          = $orderDetail['father_ref_no'];
      $action               = $orderDetail['action'];
      $despachar            = $orderDetail['despachar'];
      $refNo                = $orderDetail['ref_no'];
      $shippingInstructions = $orderDetail['shipping_instructions'];
      $a_items              = $orderDetail['list_items'];
      $a_productsNotFound   = $orderDetail['list_items_not_found'];
      //$type                 = 40;
      $type                 = $orderDetail['type'];


      //Verificar minimo de items
      /*
      if (count($a_items) < 1)
      {
        $response = $this->responseError(5);
        $this->sendResponse($response);
      }
      */

      //Verificar Acción
      if (!in_array($action, $this->a_validActions))
      {
        $response = $this->responseError(6);
        $this->sendResponse($response);
      }

      $a_accountValues = $this->session->getSessionData('Account_loggedValues');
      //Parche Uso de cookies
      $a_accountValues = unserialize($_COOKIE['Account_loggedValues']);

      $accountID       = $a_accountValues['ID'];        //AccountID
      $contactID       = $a_accountValues['ContactID']; //ContactID

      switch ($action) {
        case 1: //Crear
          $result  = $this->TicketReparation->createOrder($fatherRefNo, $accountID, $contactID, $shippingInstructions, $a_items,$despachar);
     
          if ($result === false)
          {
            $response = $this->responseError(7, $this->TicketReparation->getLastError());
            $this->sendResponse($response);
          }
          else
          {
            $array_response['response'] = array ('status' => true, 'ref_no' => $result);
            $responseEncode = json_encode($array_response);
            $this->sendResponse($responseEncode);
          }
          break;
        case 2: //Actualizar
          if (!is_string($orderDetail['ref_no']) or empty($orderDetail['ref_no']))
          {
            $response = $this->responseError(4);
            $this->sendResponse($response);
          }
          $result  = $this->TicketReparation->updateOrder($refNo, $accountID, $shippingInstructions, $a_items,null,$a_productsNotFound,$despachar);
          if ($result === false)
          {
            $response = $this->responseError(7, $this->TicketReparation->getLastError());
            $this->sendResponse($response);
          }
          else
          {
            $array_response['response'] = array ('status' => true, 'ref_no' => $result);
            $responseEncode = json_encode($array_response);
            $this->sendResponse($responseEncode);
          }
          break;
        case 3: //Enviar
          if (!is_string($orderDetail['ref_no']) or empty($orderDetail['ref_no']))
          {
            $response = $this->responseError(4);
            $this->sendResponse($response);
          }

          //Verifica si vienen items no encontrados
          if (!empty($a_productsNotFound))
            $result = $this->TicketReparation->updateOrder($refNo, $accountID, $shippingInstructions, $a_items, true, $a_productsNotFound);
          else
            $result = $this->TicketReparation->updateOrder($refNo, $accountID, $shippingInstructions, $a_items, true, null);

          if ($result === false)
          {
            $response = $this->responseError(7, $this->TicketReparation->getLastError());
            $this->sendResponse($response);
          }
          else
          {
            $array_response['response'] = array ('status' => true, 'ref_no' => $result);
            $responseEncode = json_encode($array_response);
            $this->sendResponse($responseEncode);
          }
          break;
        default:
          # code...
          break;
      }

    }

    public function searchProducts()
    {
      $i=0;
      $this->load->library('ConnectUrl');
      if (empty($_POST))
      {
        /*
        $response = $this->responseError(1);
        $this->sendResponse($response);
        */
        $_POST['data']='{"search_items":{"type_id":40,"q_partCode":"","q_delfosCode":"49700","q_type":2,"ref_no":"220601-000216"}}';
      }

      if(empty($_POST['data']))
      {
/*        $response = $this->responseError(2);
        $this->sendResponse($response);
        */
        $_POST['data']='{"search_items":{"type_id":40,"q_partCode":"","q_delfosCode":"","q_type":2,"ref_no":"220601-000216"}}';
      }

      $data = trim($_POST['data']);
      $array_data = json_decode($data, true);
      $array_data = $array_data['search_items'];

      if (  !is_array($array_data)
          or !array_key_exists('q_type', $array_data)
          or !array_key_exists('type_id', $array_data)
          or !array_key_exists('q_delfosCode', $array_data)
          or !array_key_exists('q_partCode', $array_data)
          )
      {
        $response = $this->responseError(3);
        $this->sendResponse($response);
      }

      $type        = $array_data['type_id'];
      $q_delfos    = $array_data['q_delfosCode'];
      $q_type      = $array_data['q_type'];
      $q_partCode  = $array_data['q_partCode'];
      $ref_no      = $array_data['ref_no'];
 
      if (empty($q_partCode) and (!is_numeric($q_partCode)) and empty($q_delfos) and (!is_numeric($q_delfos)) and empty($q_type) )
      {
        $response = $this->responseError(8);
        $this->sendResponse($response);
      }


      $this->load->model('custom/ws/ItemsProducts'); //Modelo para acceder a tecnicos y modelo
      $result = $this->ItemsProducts->search($q_delfos, $q_partCode, $q_type,$ref_no);


      if ($result === false)
      {
        $response = $this->responseError(7, $this->ItemsProducts->getLastError());
        $this->sendResponse($response);
      }
      else
      {

        $array_response['response'] = array ('status' => true, 'list_items' => $result );
        $i=0;
        foreach ($result as $key => $item) {

          if($i==0)
          {
          $lista = $lista . '{"Inventory_item_id":' . $item['InventoryItemId'] . '}';
          }
          else {
            $lista = $lista . ',{"Inventory_item_id":' . $item['InventoryItemId'] . '}';
          }
          $i++;
        }
        $array_json='{"usuario": "Integer",
                                 "accion": "ItemsStock",
                                 "order_detail": { "list_products":[' . $lista . ']}}';
        //$this->sendResponse("--->" . $array_json);
         /*$array_json='{"usuario": "Integer",
                                  "accion": "ItemsStock",
                                  "order_detail":{"list_products":[{"Inventory_item_id":52461},{"Inventory_item_id":291485},{"Inventory_item_id":9301},{"Inventory_item_id":761511}]}}';
                                  */

        $array_post=json_decode($array_json);
       /*$array_post     =  array('usuario': 'Integer',
                                'accion': 'ItemsStock',
                                'list_products':[{'Inventory_item_id':52461},{'Inventory_item_id':291485},{'Inventory_item_id':9301},{'Inventory_item_id':761511}]
                                );
        */


        $json_data_post = json_encode($array_post);
        $json_data_post = base64_encode($json_data_post);
        $postArray      = array ('data' => $json_data_post);
        $result         = $this->requestPost($this->URL_GET_HH, $postArray);
        $arr_json = json_decode($result, true);
        $respuesta  = base64_decode($arr_json['respuesta']);

        //$this->sendResponse("--w->" . $respuesta);

        $valores_stock=json_decode($respuesta,true);
        $i=0;
        foreach ($array_response["response"]["list_items"] as $key => $value) {
          //Inventory_item_id
          //InventoryItemId
          if($array_response["response"]["list_items"][$i]["InventoryItemId"]==$valores_stock['order_detail']["list_products"][$i]["Inventory_item_id"])
          {
             $array_response["response"]["list_items"][$i]["stock"]=$valores_stock['order_detail']["list_products"][$i]["stock"];
          }
          else {

             $array_response["response"]["list_items"][$i]["stock"]="0";
          }
        //  $value->Inventory_item_id
        //  $value->stock
          $i++;
        }

        $responseEncode = json_encode($array_response);
        $this->sendResponse( $responseEncode);
      }

    }

    private function responseError($type, $message = false)
    {

        $array_error['response'] = array ('status' => false, 'errors' => '');
        $response = '';

        switch ($type) {
            case 1:
                $response =  array('code' => 1, 'message' => 'Solicitud Inesperada');
                break;
            case 2:
                $response =  array('code' => 2, 'message' => 'No se encontro variable requerida');
                break;
            case 3:
                $response =  array('code' => 3, 'message' => 'Estructura minima requerida de JSON no encontrada '. $message);
                break;
            case 4:
                $response =  array('code' => 4, 'message' => 'Algunos valores encontrados, no son del tipo requerido');
                break;
            case 5:
                $response =  array('code' => 5, 'message' => 'Debe tener al menos un producto seleccionado');
                break;
            case 6:
                $response =  array('code' => 6, 'message' => 'Acción desconocida');
                break;
            case 7:
                $response =  array('code' => 7, 'message' => 'Info : '. $message);
                break;
            case 8:
                $response =  array('code' => 7, 'message' => 'Debe al menos contener un parametro de búsqueda');
                break;
            default:
                $response =  array('code' => 1, 'message' => 'Solicitud Inesperada');
                break;
        }

        $array_error['response']['errors'] = $response;
        $responseEncode = json_encode($array_error);
        return $responseEncode;
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

    private function validAccountLogged()
    {
      $account_values = $this->session->getSessionData('Account_loggedValues');
      //Parche Uso de cookies
      $account_values = unserialize($_COOKIE['Account_loggedValues']);
      if (empty($account_values) and !is_array($account_values))
        \RightNow\Utils\Url::redirectToErrorPage(4);
    }

	
	//***************************Servicios de predicion******************************

	/*
	*
	*Retorna el JSON de prediccion
	*
	*/
	
	public function getPredictionData(){
	if (empty($_POST))
      {
        $response = $this->responseError(1);
		 //$data = '{"ref_no":"191119-000024"}';
        $this->sendResponse($response);
      }

      if(empty($_POST['data']))
      {
        $response = $this->responseError(2);
        $this->sendResponse($response);
      }

      $data = trim($_POST['data']);
      $array_data = json_decode($data, true);

	 
	  if (  !is_array($array_data))
	  {
        $response = $this->responseError(3, print_r($data, true));
        $this->sendResponse($response);
      }
	  
	   $this->load->model('custom/ws/TicketModel');
	   $result = $this->TicketModel->getIncident($array_data['ref_no']);
	   
	   $idTecnico = intval($result -> order -> AssignedTo -> Account -> ID);
	   
	   
	   
	   
     //$this->sendResponse( json_encode($result -> order ->CustomFields -> c -> predictiondata));
     /*
          34     Guillermo Cardenas
          97     Sergio Neira
          108100 Jorge Gonzalez
          104023 Holdrin Soto
          88666  Johan Donquis
          70     Héctor González
          175    Rodrigo Torrens
     */
    switch ($idTecnico) {
        case 34:    // CARDENAS PALOMO, GUILLERMO
        case 108100:// GONZALEZ ESPINOZA, JORGE NICOLAS
        case 97:    // NEIRA MONTESINOS, SERGIO
        case 104023:// SOTO, HOLDRIN ENRIQUE
        case 175:   // Rodrigo Torrens Clerc
        case 88666: // DONQUIS MARTINEZ, JOHAN MANUEL
        case 70:    // GONZALEZ PAREDES, HECTOR JOSE
        case 44:    // CHAPARRO ROJAS, JAIME ENRIQUE
        case 148:   // LARA PASTEN, OSCAR IGNACIO
        case 145:   // HERRERA TRONCOSO, ALEXIS ALONSO
        case 95676: // BONILLA GIL, LUIS GABRIEL
        case 95726: // CONTRERAS ROMERO, MIGUEL ALFONSO
        case 8:     // AGUILA PÉREZ, CRISTIAN ANDRÉS
        case 13:    // AGUILERA BASAEZ, LUIS ALEJANDRO
        case 46:    // CISTERNAS ZACUR, CLAUDIO ANTONIO
        case 108296:// TREJO MILANO, JUAN MANUEL
        case 31:    // CAMPOS SALAS, ROLANDO ROBERTO
        case 84:    // MUÑOZ LACOUR, ROBERTO ALEXIS
        case 93492: // SOLIS VEJAR, RODRIGO ANDRES
        case 98:    // NOVA CUEVAS, JORGE LUIS
      

        // se elimina esta condicion 
        //$json_array = array('status' => true,'prediction' => $result ->order->CustomFields->c->predictiondata);
        $json_array = array('status' => false, 'idtecnico' => $idTecnico );
        $responseEncode = json_encode($json_array);
        $this->sendResponse( $responseEncode);
          break;
      default:
        $json_array = array('status' => false, 'idtecnico' => $idTecnico );
        $responseEncode = json_encode($json_array);
        $this->sendResponse( $responseEncode);
        break;
      }
/*
	   if($idTecnico == 34 || $idTecnico == 108100 || $idTecnico == 97 || $idTecnico == 104023 || $idTecnico == 175 ||  $idTecnico == 88666 || $idTecnico == 70 ){
	   				   
		  $json_array = array('status' => true,'prediction' => $result ->order->CustomFields->c->predictiondata);
			$responseEncode = json_encode($json_array);
			$this->sendResponse( $responseEncode);   
	   }else{
		   
		  $json_array = array('status' => false, 'idtecnico' => $idTecnico );
			$responseEncode = json_encode($json_array);
			$this->sendResponse( $responseEncode);
	   }
*/	  
	}	
		
	/*
	*
	*Retorna un Json con los repuestos que fueron solicitados
	*para prediccion
	*/
		
	
	public function productPrediction()
    {
      $i=0;
      $this->load->library('ConnectUrl');
	  $productList =  Array();

      $data = trim($_POST['data']);
      //$data = '{"estado":"OK","mensaje":"Existe Reporte","reporte":{"hh":41361,"id":27,"d":[{"C":33732,"R":3,"P":33892,"U":168,"H":1},{"C":39596,"R":7,"P":0,"U":0,"H":3},{"C":35165,"R":7,"P":0,"U":0,"H":3},{"C":36794,"R":7,"P":0,"U":0,"H":3},{"C":36078,"R":7,"P":0,"U":0,"H":2},{"C":38689,"R":7,"P":0,"U":0,"H":2},{"C":40832,"R":7,"P":0,"U":0,"H":2},{"C":38583,"R":7,"P":0,"U":0,"H":2},{"C":39640,"R":7,"P":0,"U":0,"H":1},{"C":42435,"R":7,"P":0,"U":0,"H":1},{"C":38690,"R":8,"P":0,"U":0,"H":1},{"C":39624,"R":8,"P":0,"U":0,"H":1},{"C":42112,"R":8,"P":0,"U":0,"H":1},{"C":42485,"R":8,"P":0,"U":0,"H":0},{"C":33319,"R":8,"P":0,"U":0,"H":0},{"C":42509,"R":8,"P":0,"U":0,"H":0},{"C":35258,"R":8,"P":0,"U":0,"H":0},{"C":52660,"R":8,"P":0,"U":0,"H":0},{"C":44545,"R":8,"P":0,"U":0,"H":0},{"C":43185,"R":8,"P":0,"U":0,"H":0},{"C":31835,"R":8,"P":0,"U":0,"H":0},{"C":31978,"R":8,"P":0,"U":0,"H":0},{"C":42094,"R":8,"P":0,"U":0,"H":0},{"C":35257,"R":8,"P":0,"U":0,"H":0},{"C":49958,"R":8,"P":0,"U":0,"H":0},{"C":31692,"R":8,"P":0,"U":0,"H":0},{"C":30218,"R":8,"P":0,"U":0,"H":0},{"C":33317,"R":8,"P":0,"U":0,"H":0},{"C":45430,"R":8,"P":0,"U":0,"H":0},{"C":44011,"R":8,"P":0,"U":0,"H":0},{"C":34793,"R":8,"P":0,"U":0,"H":0},{"C":50721,"R":8,"P":0,"U":0,"H":0},{"C":33647,"R":8,"P":0,"U":0,"H":0},{"C":33729,"R":8,"P":0,"U":0,"H":0},{"C":42486,"R":8,"P":0,"U":0,"H":0},{"C":2445,"R":8,"P":0,"U":0,"H":0},{"C":38978,"R":8,"P":0,"U":0,"H":0},{"C":42942,"R":8,"P":0,"U":0,"H":0},{"C":42859,"R":8,"P":0,"U":0,"H":0},{"C":31695,"R":8,"P":0,"U":0,"H":0},{"C":32054,"R":8,"P":0,"U":0,"H":0},{"C":38698,"R":8,"P":0,"U":0,"H":0},{"C":35460,"R":8,"P":0,"U":0,"H":0},{"C":43280,"R":8,"P":0,"U":0,"H":0},{"C":47013,"R":8,"P":0,"U":0,"H":0},{"C":42961,"R":8,"P":0,"U":0,"H":0},{"C":40456,"R":8,"P":0,"U":0,"H":0},{"C":42508,"R":8,"P":0,"U":0,"H":0},{"C":42735,"R":8,"P":0,"U":0,"H":0},{"C":50291,"R":8,"P":0,"U":0,"H":0},{"C":44645,"R":8,"P":0,"U":0,"H":0},{"C":40485,"R":8,"P":0,"U":0,"H":0},{"C":46335,"R":8,"P":0,"U":0,"H":0},{"C":42733,"R":8,"P":0,"U":0,"H":0},{"C":46958,"R":8,"P":0,"U":0,"H":0},{"C":46327,"R":8,"P":0,"U":0,"H":0},{"C":42562,"R":8,"P":0,"U":0,"H":0},{"C":53912,"R":8,"P":0,"U":0,"H":0},{"C":42349,"R":8,"P":0,"U":0,"H":0},{"C":42847,"R":8,"P":0,"U":0,"H":0}]}}';
	  
      $array_prediction = json_decode($data, true);
      $array_prediction = $array_prediction['reporte']['d'];

    
		
		
      
	  $this->load->model('custom/ws/ItemsProducts'); //Modelo para acceder a tecnicos y modelo
	  
	  forEach ($array_prediction as  &$value){
			  
		  $q_delfos    = $value['C'];
	 
	      
		  if ( empty($q_delfos) )
		  {
			$response = $this->responseError(8);
			$this->sendResponse($array_prediction);
		  }

		  
		  $result = $this->ItemsProducts->search($q_delfos, '', '');
			
			

		  if ($result === false)
		  {
			$response = $this->responseError(7, $this->ItemsProducts->getLastError());
			$this->sendResponse($response);
		  }
		  else
		  {

			$array_response['response'] = array ('status' => true, 'list_items' => $result );
			
			
			
			$i=0;
			
				
				
				 $array_response['response']['list_items'][$i]['utilization'] = $value['U'];
			
			      if($i==0)
				  {
				  $lista = $lista . '{"Inventory_item_id":' . $array_response[0]['InventoryItemId'] . '}';
				  }
				  else {
					$lista = $lista . ',{"Inventory_item_id":' . $array_response[0]['InventoryItemId'] . '}';
				  }
				  $i++;

		}	
		
		
			$array_json='{"usuario": "Integer",
									 "accion": "ItemsStock",
									 "order_detail": { "list_products":[' . $lista . ']}}';
		   
		
			$array_post=json_decode($array_json);
		   
			$json_data_post = json_encode($array_post);
			$json_data_post = base64_encode($json_data_post);
			$postArray      = array ('data' => $json_data_post);
			
			$result         = $this->requestPost($this->URL_GET_HH, $postArray);
			
			$arr_json = json_decode($result, true);
						
			$respuesta  = base64_decode($arr_json['respuesta'][0]);


			$valores_stock=json_decode($respuesta,true);
			

			$i=0;
			foreach ($array_response["response"]["list_items"] as $key => $value) { 
			  
			if($array_response["response"]["list_items"][$i]["InventoryItemId"]==$valores_stock['order_detail']["list_products"][0]["Inventory_item_id"])
			{
				$array_response["response"]["list_items"][$i]["stock"]=$valores_stock['order_detail']["list_products"][0]["stock"];
			}
			else 
			{
				$array_response["response"]["list_items"][$i]["stock"]="0";
			}

				if($array_response['response']['list_items'][$i]['utilization']>85){
				array_push($productList, $array_response["response"]["list_items"][$i]);
				}
				$i++;
			}
		}
			
		$this->sendResponse(json_encode($productList));

	}


}
