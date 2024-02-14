<?php
namespace Custom\Controllers;


class SynchronizerSalesReps extends \RightNow\Controllers\Base
{
    CONST KEY_BLOWFISH          = "D3t1H6q0p6V7z8";
    protected $typeFormat = 'json';


    function __construct()
    {
        parent::__construct();
        //$this->load->library('custom/Simplexml');
        $this->load->model('custom/ws/EbsSalesReps');
        $this->load->library('Blowfish', false); //carga Libreria de Blowfish
    }
    private function getdataPOST()
    {


        $data = trim($_POST['data']);
        if (!empty($data)){
            $data_decode = base64_decode($data);
            //$data = utf8_encode($data);
            return $data_decode;
        }
        return false;
    }
    private function formatEncode($cadena)
    {
        $CI = &get_instance();
        switch ($this->typeFormat) {
            case 'json':
                return json_encode($cadena);
                break;
            case 'xml':
                return json_encode($cadena);
                break;
            default:
                return json_encode($cadena);
                break;
        }
    }
    private function formatDecode($cadena)
    {
        switch ($this->typeFormat)
        {
            case 'json':
                return json_decode($cadena, true);
                break;
            case 'xml':
                return json_encode($cadena, true);
                break;
            default:
                return json_decode($cadena, true);
                break;
        }
    }

    public function execute()
    {

        if (!empty($_POST))
        {
          //$data='{"Vendedores": [{"salesrep_id": "100001047","name": "CANALES"},{"salesrep_id": "100001040","name": "COLOR"},{"salesrep_id": "100001043","name": "ENRIQUE ALFARO RIVERA"},{"salesrep_id": "100001048","name": "ENRIQUE CARLINI"},{"salesrep_id": "100001049","name": "Loreto Sampaio"},{"salesrep_id": "100001044","name": "OSVALDO ARANCIBIA"},{"salesrep_id": "100001046","name": "PAOLA CASTRO"},{"salesrep_id": "100001045","name": "RHODY SANTIBANEZ"},{"salesrep_id": "100001042","name": "SERVICIO TECNICO"},{"salesrep_id": "100001041","name": "WILFREDO MUNOZ"}]}';
          //$array_data = json_decode(utf8_encode($data), true);

          $data_post  = $this->getdataPOST();
          //$data_post = 'eyJ1c3VhcmlvIjoiVXNlckRpbWFjb2ZpIiwiYWNjaW9uIjoiU3luY2hyb25pemVyU2FsZXNSZXBzIiwiZGF0b3MiOiJ7XCJWZW5kZWRvcmVzXCI6IFt7XCJzYWxlc3JlcF9pZFwiOiBcIjEwMDAwMzA0MFwiLFwibmFtZVwiOiBcIi5cIn0se1wic2FsZXNyZXBfaWRcIjogXCIxMDAwMDMwNDRcIixcIm5hbWVcIjogXCJDTEFVRElBIENBQkVaQVNcIn0se1wic2FsZXNyZXBfaWRcIjogXCIxMDAwMDMwNDNcIixcIm5hbWVcIjogXCJDTEFVRElBIE1BUkNIQU5UIFRPUlJFU1wifSx7XCJzYWxlc3JlcF9pZFwiOiBcIjEwMDAwMTA4NFwiLFwibmFtZVwiOiBcIkpFQU5ORVRURSBQQVJBR1VFWiBESUFaXCJ9LHtcInNhbGVzcmVwX2lkXCI6IFwiMTAwMDAxMDkwXCIsXCJuYW1lXCI6IFwiSklNRU5BIEFSUklBWkFcIn0se1wic2FsZXNyZXBfaWRcIjogXCIxMDAwMDMwNDVcIixcIm5hbWVcIjogXCJKT0hBTk5BIEdBVElDQVwifSx7XCJzYWxlc3JlcF9pZFwiOiBcIi0zXCIsXCJuYW1lXCI6IFwiTm8gU2FsZXMgQ3JlZGl0XCJ9LHtcInNhbGVzcmVwX2lkXCI6IFwiMTAwMDAzMDQyXCIsXCJuYW1lXCI6IFwiUEFNRUxBIFZBTERFUyBNXCJ9LHtcInNhbGVzcmVwX2lkXCI6IFwiMTAwMDAxMDk3XCIsXCJuYW1lXCI6IFwiVEFUSUFOQSBNQVJJQU5FTEEgQkVDRVJSQSBNQVRVUkFOQVwifSx7XCJzYWxlc3JlcF9pZFwiOiBcIjEwMDAwMzA0MVwiLFwibmFtZVwiOiBcIlwifV19In0=';
          //$data_post = '{"Vendedores": [{"salesrep_id": "100003040","name": "."},{"salesrep_id": "100003044","name": "CLAUDIA CABEZAS"},{"salesrep_id": "100003043","name": "CLAUDIA MARCHANT TORRES"},{"salesrep_id": "100001084","name": "JEANNETTE PARAGUEZ DIAZ"},{"salesrep_id": "100001090","name": "JIMENA ARRIAZA"},{"salesrep_id": "100003045","name": "JOHANNA GATICA"},{"salesrep_id": "-3","name": "No Sales Credit"},{"salesrep_id": "100003042","name": "PAMELA VALDES M"},{"salesrep_id": "100001097","name": "TATIANA MARIANELA BECERRA MATURANA"},{"salesrep_id": "100003041","name": ""}]}';
          $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish


          $array_data = json_decode(utf8_encode($json_data), true);
            if (is_array($array_data) and ($array_data!=false))
            {
                $IndiceVendedores = 'Vendedores';


                if (array_key_exists($IndiceVendedores, $array_data) )
                {

                    $array_result                               = array ('Resultado' => true, 'Respuesta' => array('Vendedores' => '')) ;
                      $response = $this->formatEncode($array_result);

                    $array_result['Respuesta']['Vendedores']     = $this->updateSalesReps($array_data[$IndiceVendedores]);

                    $response1                                  = $this->blowfish->encrypt(json_encode($array_result['Respuesta']), self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
                    //$array_result['Respuesta']                  = base64_encode($response1);
                    $array_result['Respuesta']                  = $response1;

                    $response = $this->formatEncode($array_result);
                    $this->sendResponse($response);
                }
                else
                {
                    $response = $this->responseError(3);
                    $this->sendResponse($response);
                }
            }
            else{
                $response = $this->responseError(2);
                $this->sendResponse($json_data);
            }
        }
        else {
            $response = $this->responseError(1);
            $this->sendResponse($response);
        }
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

    private function responseError($type, $message = false)
    {

        $array_error = array ('resultado' => false, 'respuesta' => array(), 'POST' => $_POST['data']);
        $response = '';

        switch ($type) {
            case 1:
                $response =  array('Error' => 1, 'Glosa' => 'Solicitud Inesperada TEST 20');
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
                $response =  array('Error' => 1, 'Glosa' => 'Solicitud Inesperada  TEST');
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
    private function updateSalesReps($array_products)
    {
      foreach ($array_products as $Sales_rep)
      {

        $sales_rep_id       = $Sales_rep['salesrep_id'];
        $name               = $Sales_rep['name'];

        $result = $this->EbsSalesReps->modifySalesReps($sales_rep_id, $name);
        if ($result == false)
        {
          $array_result[] = array('salesrep_id' => $Sales_rep['salesrep_id'],  'Estado' => false, 'Glosa' => $this->EbsSalesReps->getLastError());
        }
        else
        {
          $array_result[] = array('salesrep_id' => $Sales_rep['salesrep_id'],  'Estado' => true, 'Glosa' => 'Ingresado correctamente');
        }

      }
      return $array_result;
    }
}
