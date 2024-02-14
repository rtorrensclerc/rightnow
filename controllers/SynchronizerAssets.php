<?php
namespace Custom\Controllers;
use RightNow\Connect\v1_2 as RNCPHP;

class SynchronizerAssets extends \RightNow\Controllers\Base
{
    CONST KEY_BLOWFISH          = "D3t1H6q0p6V7z8";
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
        $indiceAssets = 'Equipos';
        if (!empty($_POST))
        {
            $data_post  = $this->getdataPOST();


            //$json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish

            
            $array_data = json_decode(utf8_encode($data_post), true);
            
        }
        else
        {
            /*
            "Equipos": [
                {
                  "HH": "3500000",
                  "MarcaHH": "RICHO",
                  "ModeloHH": "NUEVO MODELO",
            
                  "inventoryItemId": "5083",
                  
                  "id_ebs_direccion": "4662027"
                }   

            */
                
                /* $data='eyJFcXVpcG9zIjogW3siTk9NQlJFX0NMSUVOVEUiOiAiU0VSVklDSU9TIEFDVUlDT0xBUyBGUkFOQ0lTQ08gSk9TRSBCQVJSSUVOVE9TIFBVR0EgRS5JLlIuTC4iLCJISCI6ICIxOTk3MDciLCJSVVRfQ0xJRU5URSI6ICI3NjgxNzE5MC00IiwiSURfQ0xJRU5URSI6ICIzOTYxMSIsIk5SX1JFRkVSRU5DSUFfQ0xJIjogIjIzNTE4IiwiSURfRElSRUNDSU9OIjogIjIwOTk4IiwiTlJfUkVGRVJFTkNJQV9ESVIiOiAiMTIxMzYiLCJESVJFQ0NJT04xIjogIkJFUk5BUkRPIE+0SElHR0lOUyA2MDUgT0YuMzA2IiwiRElSRUNDSU9OMiI6ICJBUVVBWVNFTiBFLkkuUi5MLiIsIkRJUkVDQ0lPTjMiOiAiQkVSTkFSRE8gT7RISUdHSU5TIDYwNSBPRi4zMDYgUFVFUlRPIEFZU0VOIiwiQ09NVU5BIjogIlBVRVJUTyBBWVNFTiIsIlJFR0lPTiI6ICJVTkRFQ0lNQSBSRUdJT04iLCJSRVNPVVJDRV9JRCI6ICIxMDAwMDMxMDQiLCJOT01CUkVfVEVDTklDTyI6ICJBU0VOSk8gIFZBTEVOQ0lBLCBDQVJMT1MgSEVSTkFOIiwiQkxPUVVFTyI6ICJGQUxTRSIsIlNFUklBTF9OVU1CRVIiOiAiVzMwMzg5MDU0MTAiLCJNQVJDQSI6ICJSSUNPSCIsIk1PREVMTyI6ICJBRklDSU8gTVAtMjAxIDIyMFYgUklDT0giLCJJVEVNX0NPRElHTyI6ICI0NTQ3OCIsIlBBUlRfTlVNQkVSIjogIjQxNTY1MyIsIklOVkVOVE9SWV9JVEVNX0lEIjogIjI4MzU5IiwiSVRFTV9UWVBFIjogIkVRVUlQTyIsIkVTX0JJTExUTyI6ICJ0cnVlIiwiRU5BQkxFRCI6ICJmYWxzZSIsIklURU1fQ09ESUdPIjogIjQ1NDc4IiwiSVRFTV9QQVJUX05VTUJFUiI6ICI0MTU2NTMiLCJJVEVNX1VOSVRfU0VMTElOR19QUklDRSI6ICI1NDUwMDAiLCJJVEVNX1VOSVRfQ09TVF9QUklDRSI6ICIyODAxMDAiLCJJVEVNX1RZUEUiOiAiRVFVSVBPIiwiSVRFTV9ESVNBQkxFRCI6ICJmYWxzZSIsIklURU1fQUxURVJOQVRJVkVfUEFSRU5UX0lEIjogIjAiLCJQUklNQVJZX1VPTV9DT0RFIjogIlVORCIsIkFUUklCVVRFMSI6ICIxMDY3MyIsIkFUUklCVVRFMiI6ICJJTkRJVklEIiwiQVRSSUJVVEUzIjogIjkwMDkuOTAwMCIsIkFUUklCVVRFNCI6ICIyIiwiQVRSSUJVVEU1IjogIjMiLCJBVFRSSUJVVEU5IjogIiIsInN1cHBsaWVyc19mdWxsIjogW3siSW52ZW50b3J5X0l0ZW1fSWQiOiAiMjUzMjIiLCJJbnZlbnRvcnlfSXRlbV9JZF9IaCI6ICIyODM1OSIsIklURU1fQ09ESUdPIjogIjM2NzAyIiwiUEFSVF9OVU1CRVIiOiAiODQxNzE4IiwiQXR0cmlidXRlMSI6ICI3MDAwIiwiU1VQUExJRVJfTkFNRSI6ICJUT05FUiBORUdSTyBUSVBPIDExNzBEIEFGLTE1MTUgMTE1LzIyMFYgUklDT0giLCJDb21wb25lbnRfU2VxdWVuY2VfSWQiOiAiMTk3NSIsIkl0ZW1fVHlwZSI6ICJOb3JtYWwiLCJRdWFudGl0eSI6ICI1NTM1In1dLCJjb250YWRvcmVzIjogW3siQ09VTlRFUl9UWVBFIjogIkNPUElBIEIvTiIsIkNPVU5URVJfUkVBRElORyI6ICIwIiwiSU5TVEFOQ0VfTlVNQkVSIjogIjE5OTcwNzEifV19XX0=';
                $data_post = base64_decode($data);
                $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
                */
            
                
                $data='
                {"Equipos": [{"NOMBRE_CLIENTE": "MUTUAL DE SEGURIDAD DE LA C.CH.C","HH": "2014697","RUT_CLIENTE": "1-8","ID_CLIENTE": "98164","NR_REFERENCIA_CLI": "82067","ID_DIRECCION": "357560","NR_REFERENCIA_DIR": "27052","DIRECCION1": "ALAMEDA 4848, P.1 RECEPCION MOVILIZACION","DIRECCION2": "RECEPCION MOVILIZACION","DIRECCION3": "","COMUNA": "ESTACIÓN CENTRAL","REGION": "REGION METROPOLITANA","RESOURCE_ID": "-1","NOMBRE_TECNICO": "SIN TECNICO","BLOQUEO": "FALSE","SERIAL_NUMBER": "CN6BIB603X","MARCA": "HEWLETT PACKARD","MODELO": "Escáner HP ScanJet Enterprise Flow 5000s4  HEWLETT PACKARD","ITEM_CODIGO": "53910","PART_NUMBER": "L2755A","INVENTORY_ITEM_ID": "857487","ITEM_TYPE": "EQUIPO","ES_BILLTO": "false","ENABLED": "false","ITEM_CODIGO": "53910","ITEM_PART_NUMBER": "L2755A","ITEM_UNIT_SELLING_PRICE": "466900","ITEM_UNIT_COST_PRICE": "293000","ITEM_TYPE": "EQUIPO","ITEM_DISABLED": "false","ITEM_ALTERNATIVE_PARENT_ID": "0","PRIMARY_UOM_CODE": "UND","ATRIBUTE1": "99999999","ATRIBUTE2": "INDIVID","ATRIBUTE3": "9009.9000","ATRIBUTE4": "1","ATRIBUTE5": "202","ATTRIBUTE9": "","suppliers_full": [],"contadores": [{"COUNTER_TYPE": "COPIA B/N","COUNTER_READING": "0","INSTANCE_NUMBER": "20146971"}]}]}';
                
               // $this->sendResponse('jspon ->  ' ||  $json_data );
             $array_data = json_decode(utf8_encode($data), true);
            //$this->sendResponse($data);
        }
       //$this->sendResponse($data_post);
        
        if(1)
        {
     
            //$this->sendResponse(json_encode($array_data[$indiceAssets]));
            if (is_array($array_data) and ($array_data!=false))
            {
              if (array_key_exists($indiceAssets, $array_data))
              {
                $array_result                               = array ('Resultado' => true, 'Respuesta' => array('Equipos' => '')) ;
                $array_result['Respuesta']['Equipos']     = $this->updateAssets($array_data[$indiceAssets]);
              

                $response1                                  = $array_result['Respuesta']; //desencriptar blowfish
                $array_result['Respuesta']                  = $response1;

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
	//Actualiza HH o lo crea
    private function updateAssets($array_Assets)
    {

      
      foreach ($array_Assets as $asset)

      {
        
        $nameHH_tmp                               = $asset['HH'] ."-". $asset['MARCA'] . "-" . $asset['MODELO'] ;
        $NameHH                                   = substr($nameHH_tmp, 0, 80);
        
        $SerialNumber          = $asset['HH'];
       
        $inventoryItemId       = $asset['INVENTORY_ITEM_ID'];
        $idcliente             = $asset['ID_CLIENTE'];
        $partyNumber           = $asset['NR_REFERENCIA_CLI'];
        $rut                   = $asset['RUT_CLIENTE'];
        $razonSocial           = $asset['NOMBRE_CLIENTE'];

        $id_ebs_direccion      = $asset['ID_DIRECCION'];
        $party_site_number     = $asset['NR_REFERENCIA_DIR'];
        $dir_envio             = $asset['DIRECCION1'] . ',' .$asset['DIRECCION2'] . ',' .$asset['DIRECCION3']  ;
        $region                = $asset['REGION'];
        $comuna                = $asset['COMUNA'];
        
        $is_facturacion        = $asset['ES_BILLTO'];
        $activate              = $asset['ENABLED'];
        
        $item_name             = $asset['MODELO'];
        $codeItem              = $asset['ITEM_CODIGO'];
        $partNumber            = $asset['PART_NUMBER'];
        $unitCostPrice         = $asset['ITEM_UNIT_COST_PRICE'];
        $unitSellingPrice      = $asset['ITEM_UNIT_SELLING_PRICE'];
        $item_category         = strtolower($asset['ITEM_TYPE']);
        $description           = $asset['MODELO'];
        $alternativeParentID   = $asset['ITEM_ALTERNATIVE_PARENT_ID'];
        $unitMeasure           = $asset['PRIMARY_UOM_CODE'];
        $disabled              = $asset['ITEM_DISABLED'];
        $atribute1             = $asset['ATRIBUTE1'];
        $atribute2             = $asset['ATRIBUTE2'];
        $atribute3             = $asset['ATRIBUTE3'];
        $atribute4             = $asset['ATRIBUTE4'];
        $atribute5             = $asset['ATRIBUTE5'];
        $atribute9             = $asset['ATRIBUTE9'];
        $SERIAL_NUMBER         = $asset['SERIAL_NUMBER'];
        $a_suppliers_full      = $asset['suppliers_full'];
        $array_counters        = $asset['contadores'];
       /* BUSCA CLIENTE*/ 
       
       $ObjOrg = RNCPHP\Organization::find("CustomFields.c.id_cliente = {$idcliente}");
       
	   //valida si la organizacion existe
        if ($ObjOrg == false)
        {
			//Crea o modifica el cliente
            $this->EbsOrganization->modifyClient($idcliente, $partyNumber, $rut, $razonSocial);
        }
       
       // $this->sendResponse(json_encode($id_ebs_direccion));
        /*  BUSCA DIRECCION */
        
        $objDir = RNCPHP\DOS\Direccion::first('d_id = '. $id_ebs_direccion);
        
		//Busca la direcciones o las crea 
        if ($objDir == false)
        {
            //$this->sendResponse(json_encode($id_ebs_direccion) . 'CREATE');
            //$this->sendResponse($id_ebs_direccion . '-' . $idCliente . '-' .  $party_site_number. '-' .  $dir_envio. '-' . $region. '-' .  $comuna. '-' .  $is_facturacion. '-' .  $activate);
            $this->EbsOrganization->modifyDirection($id_ebs_direccion, $idcliente, $party_site_number, $dir_envio, $region, $comuna, $is_facturacion, $activate);
            $objDir = RNCPHP\DOS\Direccion::first('d_id = '. $id_ebs_direccion);
            $objDireccion=$objDir;
        }
     
        if ($objDir instanceof RNCPHP\DOS\Direccion and $id_ebs_direccion>1)
        {
            //$this->sendResponse(json_encode($id_ebs_direccion) . 'UPDATE');
            $this->EbsOrganization->modifyDirection($id_ebs_direccion, $idcliente, $party_site_number, $dir_envio, $region, $comuna, $is_facturacion, $activate);
            $objDireccion=$objDir;
        }
        $objDireccion=$objDir;

       
        //$this->sendResponse(json_encode($objDir));

        /* BUSCA PRODUCTO */
        $objProd                                    = RNCPHP\OP\Product::first("InventoryItemId = {$inventoryItemId}");
        if ($objProd == false)
        {
            //$this->sendResponse($id_ebs_direccion . '-' . $idCliente . '-' .  $party_site_number. '-' .  $dir_envio. '-' . $region. '-' .  $comuna. '-' .  $is_facturacion. '-' .  $activate);

            //item_name es el modelo
            
            $this->EbsProducts->modifyProduct($inventoryItemId, $item_name, $codeItem, $partNumber, $unitCostPrice, $unitSellingPrice, $item_category,
            $description, $alternativeParentID, $unitMeasure, $disabled,
            $atribute1, $atribute2, $atribute3, $atribute4, $atribute5, $atribute9,$atribute10);
            $objProd                                    = RNCPHP\OP\Product::first("InventoryItemId = {$inventoryItemId}");
            $producto=$objProd;
        }
        
        if ($objProd instanceof RNCPHP\OP\Product)
        {
            
            $this->EbsProducts->modifyProduct($inventoryItemId, $item_name, $codeItem, $partNumber, $unitCostPrice, $unitSellingPrice, $item_category,
            $description, $alternativeParentID, $unitMeasure, $disabled,
            $atribute1, $atribute2, $atribute3, $atribute4, $atribute5, $atribute9,$atribute10);
            $objProd                                    = RNCPHP\OP\Product::first("InventoryItemId = {$inventoryItemId}");
            $producto=$objProd;
        }
        

        /* BUSCA LOS INSUMOS RELACIONADOS */

        $product                                    = RNCPHP\OP\Product::first("InventoryItemId = {$inventoryItemId}");
        //$this->sendResponse(json_encode($item_category));
        if (!$product instanceof RNCPHP\OP\Product)
        {
          //crear Equipo (producto)
          $product                                  = new RNCPHP\OP\Product();
          $product->InventoryItemId                 = $inventoryItemId;
          $product->partNumber                 = $partNumber;
          $product->codeItem = $codeItem;
          $product->Name = $item_name;
          $product->item_category = $item_category;
          
                
          $product->Save(RNCPHP\RNObject::SuppressAll);
        }

        if (is_array($a_suppliers_full))
        {
          foreach ($a_suppliers_full as $key => $supplierId)
          {
              //$this->sendResponse(json_encode($supplierId['ATTRIBUTE9']));
              
              //{"Inventory_Item_Id":"8426","Inventory_Item_Id_Hh":"27306","Attribute1":"10000","SUPPLIER_NAME":"INK BLACK 1000CC RZ-230 RISO","Component_Sequence_Id":"1630","Item_Type":"Normal","Quantity":"4649"}
              $objSupplier = RNCPHP\OP\Product::first("InventoryItemId = {$supplierId['Inventory_Item_Id']}");
              //$this->sendResponse($supplierId['Inventory_Item_Id']);
              if (!$objSupplier instanceof RNCPHP\OP\Product)
              {
                //Crear Insumo
                $objSupplier                  = new RNCPHP\OP\Product();
                $objSupplier->Name = $supplierId['SUPPLIER_NAME'];

                
                $objSupplier->InventoryItemId =  $supplierId['Inventory_Item_Id'];
                $objSupplier->codeItem = $supplierId['ITEM_CODIGO'];
                $objSupplier->partNumber = $supplierId['PART_NUMBER'];
                $objSupplier->item_category = $supplierId['Item_Type'];
                $objSupplier->item_category = $supplierId['Item_Type'];
                
                $objSupplier->last_stock=$supplierId['Quantity'];

                $objSupplier->TeoricYieldToner   = (empty($supplierId['ATTRIBUTE6'])) ? 0: $supplierId['ATTRIBUTE6'];
                $objSupplier->TrueYieldToner   = (empty($supplierId['ATTRIBUTE6'])) ? 0: $supplierId['ATTRIBUTE6']/2.0;
          
                if (!empty($supplierId['ATTRIBUTE9']))
                {
                  $objSupplier->InputCartridgeType = RNCPHP\OP\InputCartridgeType::first("Code = '" . $supplierId['ATTRIBUTE9'] . "'");
                }

                $objSupplier->Save(RNCPHP\RNObject::SuppressAll);
                //Relación Insumos

              }
              else {

                //$this->sendResponse(json_encode('aca2.0--> ' .$supplierId['PART_NUMBER']));
                $objSupplier->last_stock=$supplierId['Quantity'];
                $objSupplier->codeItem = $supplierId['ITEM_CODIGO'];
                $objSupplier->partNumber = $supplierId['PART_NUMBER'];
                $objSupplier->item_category = $supplierId['Item_Type'];

                $objSupplier->TeoricYieldToner   = (empty($supplierId['ATTRIBUTE6'])) ? 0: $supplierId['ATTRIBUTE6'];
                $objSupplier->TrueYieldToner   = (empty($supplierId['ATTRIBUTE6'])) ? 0: $supplierId['ATTRIBUTE6']/2.0;
                
                if (!empty($supplierId['ATTRIBUTE9']))
                {
                  $objSupplier->InputCartridgeType = RNCPHP\OP\InputCartridgeType::first("Code = '" . $supplierId['ATTRIBUTE9'] . "'");
                }
               
                $objSupplier->Save(RNCPHP\RNObject::SuppressAll);

              }


              $supplierRelation = RNCPHP\OP\SuppliersRelated::first("Supplier.InventoryItemId = {$supplierId['Inventory_Item_Id']} and Product.ID = {$product->ID}");
              if (!$supplierRelation instanceof RNCPHP\OP\SuppliersRelated)
              {
                $newSupplierRelation           = new RNCPHP\OP\SuppliersRelated();
                $newSupplierRelation->Product  = $product;
                $newSupplierRelation->Supplier = $objSupplier;
                $newSupplierRelation->Save(RNCPHP\RNObject::SuppressAll);

              }


          }
        }
        /* BUSCA LOS Contadores RELACIONADOS */

        
      


        /* */

		//Busca el HH
        $ObjAsset =  RNCPHP\Asset::first("SerialNumber = " . $SerialNumber);
       
        /*
        if (empty($ObjAsset))
        {

           
            $existeHH=false;
        }
        else
        {

            $existeHH=true;
        }
*/

        $result = $this->EbsAssets->modifyAsset($inventoryItemId, $NameHH, $SerialNumber, $id_ebs_direccion, $objDireccion, $product,$SERIAL_NUMBER);
       


        if (is_array($array_counters) )
        {
            try
            {
                
                RNCPHP\ConnectAPI::commit();
                foreach ($array_counters as $counter)
                {
                    //Contadores
                    //$this->sendResponse($counter['INSTANCE_NUMBER']);
                    $count_id               = $counter['INSTANCE_NUMBER'];
                    $count_tipo             = $counter['COUNTER_TYPE'];
                    $count_valor            = $counter['COUNTER_READING'];
                    $contador               = new RNCPHP\DOS\Contador();
                    $contador->ContadorID   = $count_id;
                    $contador->Valor        = $count_valor;
                    //$contador->Incident     = $this->inc_obj;
                    $contador->TipoContador = RNCPHP\DOS\TipoContador::fetch($count_tipo);
                    $contador->Asset        =   $ObjAsset;
                    $contador->save(RNCPHP\RNObject::SuppressAll);
                }
                
            }
            catch ( RNCPHP\ConnectAPIError $err )
            {
                $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
                $this->sendResponse($this->error );
                RNCPHP\ConnectAPI::rollback();
               
            }



        }            



        if ($result == false)
        {
            
          $array_result[] = array('HH' => $asset['HH'],  'Estado' => false, 'Glosa' => $this->EbsAssets->getLastError());
        }
        else
        {
            
          $array_result[] = array('HH' => $asset['HH'],  'Estado' => true, 'Glosa' => 'Ingresado correctamente');
        }

      }
      return $array_result;
    }
	
	//Excepciones
    private function responseError($type, $message = false)
    {

        $array_error = array ('Resultado' => false, 'Respuesta' => array(), 'JSON ERROR'=> json_last_error(), 'POST' => $_POST['data']);
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
