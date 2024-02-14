<?php
namespace Custom\Controllers;


class SynchronizerProducts extends \RightNow\Controllers\Base
{
    CONST KEY_BLOWFISH          = "D3t1H6q0p6V7z8";
    protected $typeFormat = 'json';


    function __construct()
    {
        parent::__construct();
        $this->load->model('custom/ws/EbsProducts');
        $this->load->library('Blowfish', false); //carga Libreria de Blowfish
    }

    public function execute()
    {
        if (!empty($_POST))
        {
            //$array_data = $this->getdataPOST();

            $data_post  = $this->getdataPOST();


            $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish


            $array_data = json_decode(utf8_encode($json_data), true);


            if (is_array($array_data) and ($array_data!=false))
            {
              $indiceProducts = 'Productos';

              if (array_key_exists($indiceProducts, $array_data))
              {
                $array_result                               = array ('Resultado' => true, 'Respuesta' => array('Productos' => '')) ;
                $array_result['Respuesta']['Productos']     = $this->updateProducts($array_data[$indiceProducts]);

                $response1                                  = $this->blowfish->encrypt(json_encode($array_result['Respuesta']), self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
                $array_result['Respuesta']                  = base64_encode($response1);

                $response = $this->formatEncode($array_result);
                $this->sendResponse($response);
              }
              else {
                $response = $this->responseError(3);
                $this->sendResponse($response);
              }
            }
            else{
              $response = $this->responseError(2);
              $this->sendResponse($response);
            }
        }
        else {
            $response = $this->responseError(1);
            $this->sendResponse($response);
        }
    }
    private function updateProducts($array_products)
    {
      foreach ($array_products as $product)
      {

        $inventoryItemID       = $product['Inventory_item_id'];
        //$name                  = $product['name'];
        $codeItem              = $product['code_item'];
        $partNumber            = $product['part_number'];
        $unitCostPrice         = $product['unit_cost_price'];
        $unitSellingPrice      = $product['unit_selling_price'];
        $item_category         = strtolower($product['item_category']);
        $name                  = $product['description'];
        $disabled              = $product['disabled'];
        $alternativeParentID   = $product['alternative_parent_id'];
        $unitMeasure           = $product['unit_measure'];
        $atribute1             = $product['Atributo_1'];
        $atribute2             = $product['Atributo_2'];
        $atribute3             = $product['Atributo_3'];
        $atribute4             = $product['Atributo_4'];
        $atribute5             = $product['Atributo_5'];
        $atribute9             = $product['Atributo_9'];
        $atribute25            = $product['Atributo_25'];
        
        
        $result = $this->EbsProducts->modifyProduct($inventoryItemID, $name, $codeItem, $partNumber, $unitCostPrice, $unitSellingPrice, $item_category,
                                                    $description, $alternativeParentID, $unitMeasure, $disabled,
                                                    $atribute1, $atribute2, $atribute3, $atribute4, $atribute5, $atribute9,$atribute25,$product);
        if ($result == false)
        {
          $array_result[] = array('Inventory_item_id' => $product['Inventory_item_id'],  'Estado' => false, 'Glosa' => $this->EbsProducts->getLastError());
        }
        else
        {
          $array_result[] = array('Inventory_item_id' => $product['Inventory_item_id'],  'Estado' => true, 'Glosa' => 'Ingresado correctamente');
        }

      }
      return $array_result;
    }
    private function responseError($type, $message = false)
    {

        $array_error = array ('Resultado' => false, 'Respuesta' => array(), 'JSON ERROR'=> json_last_error(), 'POST' => $_POST['data']);
        //$array_error = array ('Resultado' => false, 'Respuesta' => array());
        switch ($type) {
            case 1:
                $array_error['Respuesta'] =  array('Error' => 1, 'Glosa' => 'No tiene los permisos para acceder a esta pagina');
                break;
            case 2:
                $array_error['Respuesta'] =  array('Error' => 2, 'Glosa' => 'Cadena inesperada, problemas al decodificar');
                break;
            case 3:
                $array_error['Respuesta'] =  array('Error' => 3, 'Glosa' => 'Estructura no válida en la variable enviada');
                break;
            default:
                $array_error['Respuesta'] =  array('Error' => 1, 'Glosa' => 'No tiene los permisos para acceder a esta pagina');
                break;
        }

        $responseEncode = $this->formatEncode($array_error);
        return $responseEncode;
    }
    private function getdataPOST()
    {
        /*
        $data = $_POST['data'];
        if (!empty($data)){
            $data = base64_decode($data);
            $data = utf8_encode($data);
            $array_data = $this->formatDecode($data);
            return $array_data;
        }
        return false;
        */

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
    private function sendResponse($response)
    {
        switch ($this->typeFormat) {
            case 'json':
                header('Content-Type: application/json');
                echo $response;
                break;
            case 'xml':
                header('Content-Type: application/xml');
                echo $response;
                break;
            default:
                header('Content-Type: application/json');
                echo $response;
                break;
        }
        die();
    }
}
