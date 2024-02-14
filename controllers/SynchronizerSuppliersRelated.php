<?php

namespace Custom\Controllers;

use RightNow\Connect\v1_2 as RNCPHP;

class SynchronizerSuppliersRelated extends \RightNow\Controllers\Base
{
    const KEY_BLOWFISH          = "D3t1H6q0p6V7z8";
    protected $typeFormat = 'json';


    function __construct()
    {
        parent::__construct();
        $this->load->model('custom/ws/EbsAssets');
        $this->load->model('custom/ws/EbsOrganization');
        $this->load->model('custom/ws/EbsProducts');
        $this->load->library('Blowfish', false); //carga Libreria de Blowfish
    }

    public function execute()
    {
        
        if (!empty($_POST)) {
            $data_post  = $this->getdataPOST();
            $array_data = json_decode(utf8_encode($data_post), true);
        } else {
            $data = "";
            /*
            $data = '
            {"related": 
                [
                    {
                        "ITEM_CODIGO_HH": "1677581",
                        "SUPPLIER_NAME": "NOMBRE DE ITEM1",
                        "Inventory_Item_Id":"123456",
                        "ITEM_CODIGO":"23456",  
                        "PART_NUMBER":"RTUV124",
                        "Item_Type":"111",
                        "Quantity":"1",
                        "ATTRIBUTE6":"12000",
                        "ATTRIBUTE9":"K"
                    },
                    {
                        "ITEM_CODIGO_HH": "1677581",
                        "SUPPLIER_NAME": "NOMBRE DE ITEM2",
                        "Inventory_Item_Id":"1234586",
                        "ITEM_CODIGO":"23456",  
                        "PART_NUMBER":"RTUV124",
                        "Item_Type":"111",
                        "Quantity":"1",
                        "ATTRIBUTE6":"12000",
                        "ATTRIBUTE9":"K"
                    } 
                ]
            }';
            */
            $array_data = json_decode(utf8_encode($data), true);
        }

        //$this->sendResponse($data);
        //echo $data;
        //exit;

        //$this->sendResponse(json_encode($array_data[$indiceAssets]));
        if (is_array($array_data) and ($array_data != false)) {
            $indiceAssets = "related";
            if (array_key_exists($indiceAssets, $array_data)) {
                $array_result                               = array('Resultado' => true, 'Respuesta' => array('related' => ''));
                $array_result['Respuesta']['related']     = $this->updateSuppliersRelated($array_data['related']);


                $response1                                  = $array_result['Respuesta']; //desencriptar blowfish
                $array_result['Respuesta']                  = $response1;

                $response = $this->formatEncode($array_result);
                $this->sendResponse($response);
            } else {
                $response = $this->responseError(3);
                $this->sendResponse($response);
            }
        } else {
            $response = $this->responseError(2);
            $this->sendResponse($response);
        }
    }
    //Actualiza HH o lo crea

    private function updateSuppliersRelated($array_related)
    {
        /*
        echo json_encode($array_related);
        exit;
        */
        /*
        echo $array_related[0]['ITEM_CODIGO_HH'];
        $ObjAsset =  RNCPHP\Asset::first("SerialNumber = '" . $array_related[0]['ITEM_CODIGO_HH'] . "'");
        $product=$ObjAsset->CustomFields->DOS->Product;
        echo json_encode($product);
        exit;
        */

        if (is_array($array_related)) {
            foreach ($array_related as $key => $supplierId) {
                //$this->sendResponse(json_encode($supplierId['ATTRIBUTE9']));

                //{"Inventory_Item_Id":"8426","Inventory_Item_Id_Hh":"27306","Attribute1":"10000","SUPPLIER_NAME":"INK BLACK 1000CC RZ-230 RISO","Component_Sequence_Id":"1630","Item_Type":"Normal","Quantity":"4649"}
                $objSupplier = RNCPHP\OP\Product::first("InventoryItemId = {$supplierId['Inventory_Item_Id']}");
                //$this->sendResponse($supplierId['Inventory_Item_Id']);
                if (!$objSupplier instanceof RNCPHP\OP\Product) {
                    //Crear Insumo
                    $objSupplier                  = new RNCPHP\OP\Product();
                    $objSupplier->Name = $supplierId['SUPPLIER_NAME'];


                    $objSupplier->InventoryItemId =  $supplierId['Inventory_Item_Id']; // Id de Oracle Finacial
                    $objSupplier->codeItem = $supplierId['ITEM_CODIGO'];  // Numero DELFOS 
                    $objSupplier->partNumber = $supplierId['PART_NUMBER'];  // Numero de parte
                    $objSupplier->item_category = $supplierId['Item_Type'];  //  tipo de item  ( INSUMO, EQUIPO, REPUESTO,stc )



                    $objSupplier->last_stock = $supplierId['Quantity'];  // STOCK en FINACIAL

                    // RENDIMENTO DE FABRIVANTE TEORICO
                    $objSupplier->TeoricYieldToner   = (empty($supplierId['ATTRIBUTE6'])) ? 0 : $supplierId['ATTRIBUTE6'];

                    $objSupplier->TrueYieldToner   = (empty($supplierId['ATTRIBUTE6'])) ? 0 : $supplierId['ATTRIBUTE6'] / 2.0;


                    // tecnoligia o color del Tonner
                    if (!empty($supplierId['ATTRIBUTE9'])) {
                        $objSupplier->InputCartridgeType = RNCPHP\OP\InputCartridgeType::first("Code = '" . $supplierId['ATTRIBUTE9'] . "'");
                    }

                    $objSupplier->Save(RNCPHP\RNObject::SuppressAll);
                    //Relación Insumos

                } else {

                    //$this->sendResponse(json_encode('aca2.0--> ' .$supplierId['PART_NUMBER']));
                    $objSupplier->last_stock = $supplierId['Quantity'];
                    $objSupplier->codeItem = $supplierId['ITEM_CODIGO'];
                    $objSupplier->partNumber = $supplierId['PART_NUMBER'];
                    $objSupplier->item_category = $supplierId['Item_Type'];
                    $objSupplier->Name = $supplierId['SUPPLIER_NAME'];

                    $objSupplier->TeoricYieldToner   = (empty($supplierId['ATTRIBUTE6'])) ? 0 : $supplierId['ATTRIBUTE6'];
                    $objSupplier->TrueYieldToner   = (empty($supplierId['ATTRIBUTE6'])) ? 0 : $supplierId['ATTRIBUTE6'] / 2.0;

                    if (!empty($supplierId['ATTRIBUTE9'])) {
                        $objSupplier->InputCartridgeType = RNCPHP\OP\InputCartridgeType::first("Code = '" . $supplierId['ATTRIBUTE9'] . "'");
                    }

                    $objSupplier->Save(RNCPHP\RNObject::SuppressAll);
                }
                
                // Obtiene HH
                $ObjAsset =  RNCPHP\Asset::first("SerialNumber = " . $supplierId['ITEM_CODIGO_HH']);
                $product=$ObjAsset->CustomFields->DOS->Product;

                $supplierRelation = RNCPHP\OP\SuppliersRelated::first("Supplier.InventoryItemId = {$supplierId['Inventory_Item_Id']} and Product.ID = {$product->ID}");
                if (!$supplierRelation instanceof RNCPHP\OP\SuppliersRelated) {

                    $newSupplierRelation           = new RNCPHP\OP\SuppliersRelated();
                    $newSupplierRelation->Product  = $product;
                    $newSupplierRelation->Supplier = $objSupplier;
                    $newSupplierRelation->Save(RNCPHP\RNObject::SuppressAll);
                }
            }
        }
    }


    //Excepciones
    private function responseError($type, $message = false)
    {

        $array_error = array('Resultado' => false, 'Respuesta' => array(), 'JSON ERROR' => json_last_error(), 'POST' => $_POST['data']);
        //$array_error = array ('Resultado' => false, 'Respuesta' => array());
        switch ($type) {
            case 1:
                $array_error['Respuesta'] =  array('Error' => 1, 'Glosa' => 'No tiene los permisos para acceder a esta pagina');
                break;
            case 2:
                $data_post  = $this->getdataPOST();
                $array_error['Respuesta'] =  array('Error' => 2, 'Glosa' => 'Cadena inesperada, problemas al decodificar ->' . json_encode($data_post));
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
        if (!empty($data)) {
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
        switch ($this->typeFormat) {
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
