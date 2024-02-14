<?php

namespace Custom\Controllers;

class ServiceHH extends \RightNow\Controllers\Base
{
    protected $typeFormat        = 'json';

    function __construct()
    {
        parent::__construct();
        //$this->validAccountLogged();
        //$this->load->model('custom/ws/TicketReparation'); //Modelo para acceder a tecnicos y modelo
        $this->load->model('custom/ws/HH');
    }


    public function getInfo()
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
      $array_data = json_decode($_POST['data'], true);


      $idHH = $array_data['id_hh'];

      if (empty($idHH))
      {
        $response = $this->responseError(8);
        $this->sendResponse($response);
      }

      $result = $this->HH->getInfoReparation($idHH);
      if ($result === false)
      {
        $response = $this->responseError(7, $this->HH->getLastError());
        $this->sendResponse($response);
      }
      else
      {
        $array_response['response'] = array ('status' => true, 'info_hh' => $result );
        $responseEncode = json_encode($array_response);
        $this->sendResponse($responseEncode);
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
                $response =  array('code' => 3, 'message' => 'Estructura minima requerida de JSON no encontrada');
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
                $response =  array('code' => 7, 'message' => 'Error en Modelo: '. $message);
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
      $account_values  = unserialize($_COOKIE['Account_loggedValues']);

      if (empty($account_values) and !is_array($account_values))
        \RightNow\Utils\Url::redirectToErrorPage(4);
    }



}
