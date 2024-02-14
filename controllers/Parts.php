<?php

namespace Custom\Controllers;

class Parts extends \RightNow\Controllers\Base
{
    CONST KEY_BLOWFISH          = "D3t1H6q0p6V7z8";
    CONST USER                  = "UserDimacofi";
    //CONST ACCION        = "getTicketsByTecnico";
    //CONST ACCION_2 = "getTicketsByTecnico";
    public   $responseEncripted = false;
    protected $typeFormat       = 'json';

    function __construct()
    {
        parent::__construct();
        $this->load->library('Blowfish', false); //carga Libreria de Blowfish
        $this->load->model('custom/ws/TicketReparation'); //Modelo para acceder a tickets de reparación
        $this->load->model('custom/ws/TicketDevolution'); //Modelo para acceder a tickets de devolución
    }

    public function setPickRelease()
    {
      $data_post  = $this->getdataPOST();
      $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
      $array_data = json_decode(utf8_encode($json_data), true);

      if (empty($_POST))
      {
        $response = $this->responseError(1);
        $this->sendResponse($response);
      }

      if (is_array($array_data) and ($array_data != false))
      {
        $indiceAccion      = 'accion';
        $indiceUsuario     = 'usuario';
        $indiceOrderDetail = "order_detail";

        //Verficación de que el array tiene las llaves minimas solicitud
        if (!array_key_exists($indiceAccion , $array_data) and
            !array_key_exists($indiceUsuario, $array_data) and
            !array_key_exists($indiceOrderDetail, $array_data))
        {
          $response = $this->responseError(3);
          $this->sendResponse($response);
        }

        //Verificación de Usuario
        if ($array_data[$indiceUsuario] != self::USER)
        {
          $response = $this->responseError(5);
          $this->sendResponse($response);
        }

        //Verificación de Método Invocado
        if ($array_data[$indiceAccion] != __FUNCTION__ )
        {
          $response = $this->responseError(6);
          $this->sendResponse($response);
        }

        if (is_array($array_data[$indiceOrderDetail]))
        {
          $a_order       = $array_data[$indiceOrderDetail];
          $refNumber     = $a_order['ref_number_order'];
          $omNumberOrder = $a_order['order_number_om'];
          $a_infoitems   = $a_order['list_products'];

          //Items no son array
          if (!is_array($a_infoitems))
          {
            $response = $this->responseError(3);
            $this->sendResponse($response);
          }

          //Sin items
          if (count($a_infoitems) <= 0)
          {
            $response = $this->responseError(7);
            $this->sendResponse($response);
          }

          //Numero de referencia no es String
          if (!is_string($refNumber))
          {
            $response = $this->responseError(10);
            $this->sendResponse($response);
          }

          $result = $this->TicketReparation->reservationItems($refNumber, $omNumberOrder,  $a_infoitems);

          //if ($result === true)
          if ($result !== false)
          {
            $a_response    = array("resultado" => true, "respuesta" => array("glosa"        => "Pick release registrado con éxito",
                                                                             "order_detail" => array("order_number_om" => $omNumberOrder,
                                                                                                     "list_new_lines"  => $result
                                                                                                    )

                                                                             ));
            $json_response = json_encode($a_response);
            $this->sendResponse($json_response);
          }
          else {
            $response = $this->responseError(4, $this->TicketReparation->getLastError());
            $this->sendResponse($response);
          }
        }
        else
        {
          $response = $this->responseError(3);
          $this->sendResponse($response);
        }
      }
      else
      {
        $response = $this->responseError(1);
        $this->sendResponse($response);
      }

    }


    public function TestsetPickRelease()
    {
     
      $json_data  = '{
        "usuario": "UserDimacofi",
        "accion": "TestsetPickRelease",
        "order_detail":
          {
            "ref_number_order": "230629-000292",
            "order_number_om": 1700615, 
            "list_products": 
            [
              {
                "Inventory_item_id": 27901,
                "line_id": "OE_ORDER_LINES_ALL20232906090641",
                "ordered_quantity": 1
              }
            ]
          }
        }';
        
      $array_data = json_decode(utf8_encode($json_data), true);

      

      if (is_array($array_data) and ($array_data != false))
      {
        $indiceAccion      = 'accion';
        $indiceUsuario     = 'usuario';
        $indiceOrderDetail = "order_detail";

        //Verficación de que el array tiene las llaves minimas solicitud
        if (!array_key_exists($indiceAccion , $array_data) and
            !array_key_exists($indiceUsuario, $array_data) and
            !array_key_exists($indiceOrderDetail, $array_data))
        {
          $response = $this->responseError(3);
          $this->sendResponse($response);
        }

        //Verificación de Usuario
        if ($array_data[$indiceUsuario] != self::USER)
        {
          $response = $this->responseError(5);
          $this->sendResponse($response);
        }

        //Verificación de Método Invocado
        if ($array_data[$indiceAccion] != __FUNCTION__ )
        {
          $response = $this->responseError(6);
          $this->sendResponse($response);
        }

        if (is_array($array_data[$indiceOrderDetail]))
        {
          $a_order       = $array_data[$indiceOrderDetail];
          $refNumber     = $a_order['ref_number_order'];
          $omNumberOrder = $a_order['order_number_om'];
          $a_infoitems   = $a_order['list_products'];

          //Items no son array
          if (!is_array($a_infoitems))
          {
            $response = $this->responseError(3);
            $this->sendResponse($response);
          }

          //Sin items
          if (count($a_infoitems) <= 0)
          {
            $response = $this->responseError(7);
            $this->sendResponse($response);
          }

          //Numero de referencia no es String
          if (!is_string($refNumber))
          {
            $response = $this->responseError(10);
            $this->sendResponse($response);
          }

          $result = $this->TicketReparation->reservationItems($refNumber, $omNumberOrder,  $a_infoitems);

          //if ($result === true)
          if ($result !== false)
          {
            $a_response    = array("resultado" => true, "respuesta" => array("glosa"        => "Pick release registrado con éxito",
                                                                             "order_detail" => array("order_number_om" => $omNumberOrder,
                                                                                                     "list_new_lines"  => $result
                                                                                                    )

                                                                             ));
            $json_response = json_encode($a_response);
            $this->sendResponse($json_response);
          }
          else {
            $response = $this->responseError(4, $this->TicketReparation->getLastError());
            $this->sendResponse($response);
          }
        }
        else
        {
          $response = $this->responseError(3);
          $this->sendResponse($response);
        }
      }
      else
      {
        $response = $this->responseError(1);
        $this->sendResponse($response);
      }

    }

    public function ConfirmShipping()
    {
      $data_post  = $this->getdataPOST();
      $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish

      $array_data = json_decode(utf8_encode($json_data), true);

      if (empty($_POST))
      {
        $response = $this->responseError(1);
        $this->sendResponse($response);
      }

      if (is_array($array_data) and ($array_data != false))
      {
        $indiceAccion      = 'accion';
        $indiceUsuario     = 'usuario';
        $indiceOrderDetail = "order_detail";

        //Verficación de que el array tiene las llaves minimas solicitud
        if (!array_key_exists($indiceAccion , $array_data) and
            !array_key_exists($indiceUsuario, $array_data) and
            !array_key_exists($indiceOrderDetail  , $array_data))
        {
          $response = $this->responseError(3);
          $this->sendResponse($response);
          //$this->sendResponse($response .  " LLaves ".  );
        }

        //Verificación de Usuario
        if ($array_data[$indiceUsuario] != self::USER)
        {
          $response = $this->responseError(5);
          $this->sendResponse($response);
        }

        //Verificación de Método Invocado
        if ($array_data[$indiceAccion] != __FUNCTION__ )
        {
          $response = $this->responseError(6);
          $this->sendResponse($response);
        }

        if (is_array($array_data[$indiceOrderDetail]))
        {
          $a_order       = $array_data[$indiceOrderDetail];
          $refNumber     = $a_order['ref_number_order'];
          $omNumberOrder = $a_order['order_number_om'];
          $guideDispatch = $a_order['guide_dispastch'];

          $a_infoitems   = $a_order['list_products'];
          $confirmed     = $a_order['confirmed'];

          //Verificación de Keys
          //if (is_array($a_infoitems)  and array_key_exists('line_id', $a_infoitems) and array_key_exists('ordered_quantity', $a_infoitems))
          if (!is_array($a_infoitems))
          {
            $response = $this->responseError(3);
            $this->sendResponse($response);
          }
          //verificación de Array
          if (count($a_infoitems) <= 0)
          {
            $response = $this->responseError(7);
            $this->sendResponse($response);
          }

          if (!is_string($refNumber) and $confirmed == true)
          {
            $response = $this->responseError(10);
            $this->sendResponse($response);
          }

          if($omNumberOrder=='1171137' )
          {
            $a_response    = array("resultado" => true, "respuesta" => array("glosa" => "Se ha confirmado la orden con exito. guia [NNN]"));
            $json_response = json_encode($a_response);
            $this->sendResponse($json_response);
          }
          if ($confirmed == false)
          {
            $result = $this->TicketReparation->cancelOrder($refNumber);
            if ($result === true)
            {
              $a_response    = array("resultado" => true, "respuesta" => array("glosa" => "La orden ha sido cancelado"));
              $json_response = json_encode($a_response);
              $this->sendResponse($json_response);
            }
            else {
              $response = $this->responseError(4, $this->TicketReparation->getLastError());
              $this->sendResponse($response);
            }
          }
          else if ($confirmed == true)
          {
            $result = $this->TicketReparation->confirmItems($refNumber, $a_infoitems, $guideDispatch);
            if ($result === true)
            {
              $a_response    = array("resultado" => true, "respuesta" => array("glosa" => "Se ha confirmado la orden con exito. guia [" . $guideDispatch . "]"));
              $json_response = json_encode($a_response);
              $this->sendResponse($json_response);
            }
            else {
              $response = $this->responseError(4, $this->TicketReparation->getLastError());
              $this->sendResponse($response);
            }
          }

        }
        else
        {
          $response = $this->responseError(1);
          $this->sendResponse($response);
        }
      }
      else
      {
        $response = $this->responseError(1);
        $this->sendResponse($response);
      }


    }


    public function TestConfirmShipping()
    {
      $json_data  = '{
        "usuario": "UserDimacofi",
        "accion": "TestConfirmShipping",
        
        "order_detail":
          {
            "ref_number_order": "230629-000292",
            "confirmed": true,
            "order_number_om": 1700615, 
            "list_products": 
            [
              {
                "Inventory_item_id": 27901,
                "line_id": "OE_ORDER_LINES_ALL20232906090641",
                "ordered_quantity": 1
              }
            ]
          }
        }';
        
      $array_data = json_decode(utf8_encode($json_data), true);

      

      if (is_array($array_data) and ($array_data != false))
      {
        $indiceAccion      = 'accion';
        $indiceUsuario     = 'usuario';
        $indiceOrderDetail = "order_detail";

        //Verficación de que el array tiene las llaves minimas solicitud
        if (!array_key_exists($indiceAccion , $array_data) and
            !array_key_exists($indiceUsuario, $array_data) and
            !array_key_exists($indiceOrderDetail  , $array_data))
        {
          $response = $this->responseError(3);
          $this->sendResponse($response);
          //$this->sendResponse($response .  " LLaves ".  );
        }

        //Verificación de Usuario
        if ($array_data[$indiceUsuario] != self::USER)
        {
          $response = $this->responseError(5);
          $this->sendResponse($response);
        }

        //Verificación de Método Invocado
        if ($array_data[$indiceAccion] != __FUNCTION__ )
        {
          $response = $this->responseError(6);
          $this->sendResponse($response);
        }

        if (is_array($array_data[$indiceOrderDetail]))
        {
          $a_order       = $array_data[$indiceOrderDetail];
          $refNumber     = $a_order['ref_number_order'];
          $omNumberOrder = $a_order['order_number_om'];
          $guideDispatch = $a_order['guide_dispastch'];

          $a_infoitems   = $a_order['list_products'];
          $confirmed     = $a_order['confirmed'];

          //Verificación de Keys
          //if (is_array($a_infoitems)  and array_key_exists('line_id', $a_infoitems) and array_key_exists('ordered_quantity', $a_infoitems))
          if (!is_array($a_infoitems))
          {
            $response = $this->responseError(3);
            $this->sendResponse($response);
          }
          //verificación de Array
          if (count($a_infoitems) <= 0)
          {
            $response = $this->responseError(7);
            $this->sendResponse($response);
          }

          if (!is_string($refNumber) and $confirmed == true)
          {
            $response = $this->responseError(10);
            $this->sendResponse($response);
          }

          if($omNumberOrder=='1171137' )
          {
            $a_response    = array("resultado" => true, "respuesta" => array("glosa" => "Se ha confirmado la orden con exito. guia [NNN]"));
            $json_response = json_encode($a_response);
            $this->sendResponse($json_response);
          }
          if ($confirmed == false)
          {
            $result = $this->TicketReparation->cancelOrder($refNumber);
            if ($result === true)
            {
              $a_response    = array("resultado" => true, "respuesta" => array("glosa" => "La orden ha sido cancelado"));
              $json_response = json_encode($a_response);
              $this->sendResponse($json_response);
            }
            else {
              $response = $this->responseError(4, $this->TicketReparation->getLastError());
              $this->sendResponse($response);
            }
          }
          else if ($confirmed == true)
          {
            $result = $this->TicketReparation->confirmItems($refNumber, $a_infoitems, $guideDispatch);
            if ($result === true)
            {
              $a_response    = array("resultado" => true, "respuesta" => array("glosa" => "Se ha confirmado la orden con exito. guia [" . $guideDispatch . "]"));
              $json_response = json_encode($a_response);
              $this->sendResponse($json_response);
            }
            else {
              $response = $this->responseError(4, $this->TicketReparation->getLastError());
              $this->sendResponse($response);
            }
          }

        }
        else
        {
          $response = $this->responseError(1);
          $this->sendResponse($response);
        }
      }
      else
      {
        $response = $this->responseError(1);
        $this->sendResponse($response);
      }


    }


    private function getdataPOST()
    {
        $data = trim($_POST['data']);
        if (!empty($data)){
            $data_decode = base64_decode($data);
            //$data_decode = utf8_encode($data_decode);
            return $data_decode;
        }
        return false;
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
                $response =  array('Error' => 7, 'Glosa' => 'Solicitud sin items');
                break;
            case 8:
                $response =  array('Error' => 8, 'Glosa' => 'ID de ticket desconocido o no presente en Oracle RightNow');
                break;
            case 9:
                $response =  array('Error' => 9, 'Glosa' => 'ID de ticket no valido, no se encuentra en estado previo requerido');
                break;
            case 10:
                $response =  array('Error' => 10, 'Glosa' => 'Numero de referencia debe ser de tipo String');
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

    public function simulateOMresponse($encrypt = true)
    {


      $data_post  = $this->getdataPOST();
      $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
      $array_data = json_decode(utf8_encode($json_data), true);

      if (empty($_POST))
      {
        $response = $this->responseError(1);
        $this->sendResponse($response);
      }

      if (is_array($array_data) and ($array_data != false))
      {
        $indiceAccion      = 'accion';
        $indiceUsuario     = 'usuario';
        $indiceOrderDetail = "order_detail";
        //Verificación de Usuario
        if ($array_data[$indiceUsuario] != "Integer")
        {
          $response = $this->responseError(5);
          $this->sendResponse($response);
        }

        //Verificación de Método Invocado
        if ($array_data[$indiceAccion] != "setOrderOM" )
        {
          $response = $this->responseError(6);
          $this->sendResponse($response);
        }

        //Verficación de que el array tiene las llaves minimas solicitud
        if (!array_key_exists($indiceAccion , $array_data) and
            !array_key_exists($indiceUsuario, $array_data) and
            !array_key_exists($indiceOrderDetail  , $array_data))
        {
          $response = $this->responseError(3);
          $this->sendResponse($response);
        }

        if (is_array($array_data[$indiceOrderDetail]))
        {
          $a_order                = $array_data[$indiceOrderDetail];
          $refNumber              = $a_order['ref_number_order'];
          $fatherTicket           = $a_order['ref_number_ticket'];
          $clientRut              = $a_order['client_rut'];
          $typeOrder              = $a_order['type_order'];
          $hh                     = $a_order['hh'];
          $shippingInstructions   = $a_order['shipping_instructions'];
          $a_infoitems            = $a_order['list_products'];

          //Verificación de Keys
          if (is_array($a_infoitems)  and array_key_exists('line_id', $a_infoitems) and array_key_exists('ordered_quantity', $a_infoitems)
          and array_key_exists('Inventory_item_id', $a_infoitems) and array_key_exists('line_type_id', $a_infoitems) )
          {
            $response = $this->responseError(3);
            $this->sendResponse($response);
          }
          //verificación de Array
          if (count($a_infoitems) <= 0)
          {
            $response = $this->responseError(7);
            $this->sendResponse($response);
          }

          if (!is_string($refNumber) or !is_string($fatherTicket) or !is_numeric($hh) or !is_string($clientRut))
          {
            $response = $this->responseError(10);
            $this->sendResponse($response);
          }

          $a_response['resultado']       = true;
          $response["order_number_OM"]   = time();


          if ($encrypt == true)
          {
            $response                = $this->blowfish->encrypt(json_encode($response), self::KEY_BLOWFISH, 10, 22, NULL); //encriptar blowfish
            $a_response['respuesta'] = base64_encode($response);
          }
          else
            $a_response['respuesta'] = $response;

          $responseEncode  = json_encode($a_response);
          $this->sendResponse($responseEncode);

        }
        else
        {
          $response = $this->responseError(3);
          $this->sendResponse($response);
        }

      }
      else {
        $response = $this->responseError(3); //Error de estructura
        $this->sendResponse($response);
      }

    }

    public function refundShipping()
    {
      $data_post  = $this->getdataPOST();
      $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
      $array_data = json_decode(utf8_encode($json_data), true);

      if (empty($_POST))
      {
        $response = $this->responseError(1);
        $this->sendResponse($response);
      }

      if (is_array($array_data) and ($array_data != false))
      {
        $indiceAccion      = 'accion';
        $indiceUsuario     = 'usuario';
        $indiceOrderDetail = "order_detail";

        //Verficación de que el array tiene las llaves minimas solicitud
        if (!array_key_exists($indiceAccion , $array_data) and
            !array_key_exists($indiceUsuario, $array_data) and
            !array_key_exists($indiceOrderDetail  , $array_data))
        {
          $response = $this->responseError(3);
          $this->sendResponse($response);
        }

        //Verificación de Usuario
        if ($array_data[$indiceUsuario] != self::USER)
        {
          $response = $this->responseError(5);
          $this->sendResponse($response);
        }

        //Verificación de Método Invocado
        if ($array_data[$indiceAccion] != __FUNCTION__ )
        {
          $response = $this->responseError(6);
          $this->sendResponse($response);
        }

        if (is_array($array_data[$indiceOrderDetail]))
        {
          $a_order          = $array_data[$indiceOrderDetail];
          $omNumberOrder    = $a_order['order_number_om'];
          $omNumberOrderDev = $a_order['order_number_om_dev'];
          $a_infoitems      = $a_order['list_products'];

          if (!is_array($a_infoitems))
          {
            $response = $this->responseError(3);
            $this->sendResponse($response);
          }
          //verificación de Array
          if (count($a_infoitems) <= 0)
          {
            $response = $this->responseError(7);
            $this->sendResponse($response);
          }

          if (!is_string($refNumber) and $confirmed == true)
          {
            $response = $this->responseError(10);
            $this->sendResponse($response);
          }

          $result = $this->TicketDevolution->create($omNumberOrder, $a_infoitems, $omNumberOrderDev);
          if ($result != false)
          {
            $a_response    = array("resultado" => true, "respuesta" => array("glosa" => "Se ha registrado la orden de devolución con exito"));
            $json_response = json_encode($a_response);
            $this->sendResponse($json_response);
          }
          else {
            $response = $this->responseError(4, $this->TicketDevolution->getLastError());
            $this->sendResponse($response);
          }

        }
        else
        {
          $response = $this->responseError(1);
          $this->sendResponse($response);
        }
      }
      else
      {
        $response = $this->responseError(1);
        $this->sendResponse($response);
      }
    }

}
