<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_2 as RNCPHP;

class EbsAssets extends \RightNow\Models\Base
{
    private $error = '';

    function __construct()
    {
        parent::__construct();
        //\RightNow\Libraries\AbuseDetection::check();
    }

    
    public function modifyAsset($inventoryItemID, $NameHH, $SerialNumber, $id_ebs_direccion, $objDireccion, $producto,$SERIAL_NUMBER)
    {
      //Verifica serial
     
      if (!empty($SerialNumber) )
      {
        try
        {
          $ObjAsset =  RNCPHP\Asset::first("SerialNumber = " . $SerialNumber);
          //$this->sendResponse(json_encode($producto));
          if (empty($ObjAsset))
          {
            
            return $this->createAsset($inventoryItemID, $NameHH, $SerialNumber, $id_ebs_direccion, $objDireccion, $producto,$SERIAL_NUMBER);
          }
          else 
          {
           
            return $this->updateAsset($ObjAsset,$inventoryItemID, $NameHH, $SerialNumber, $id_ebs_direccion, $objDireccion, $producto,$SERIAL_NUMBER);
          }

        }
        catch (RNCPHP\ConnectAPIError $err)
        {
          $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
          return false;
        }

      }
      else
      {
          $this->error = "Serial vienen vacios";
          return false;
      }
    }


    private function createAsset($inventoryItemID, $NameHH, $SerialNumber, $id_ebs_direccion, $objDireccion, $producto,$SERIAL_NUMBER)
    {
      try
      {

        $asset                                    = new RNCPHP\Asset();
  /* 
        $asset->Name                              = substr($nameHH, 0, 80);
        $asset->Description  ='PRUEBA';
        $asset->Product                           = 2;
        $asset->SerialNumber                      = $SerialNumber;
        //$asset->CustomFields->DOS->Direccion      = $objDireccion;
        //$asset->CustomFields->DOS->Product        = $product;
*/
        $asset->Name                              = substr($NameHH, 0, 80);
        $asset->Description  =$NameHH;
        $asset->Product                           = 2;
        $asset->SerialNumber                      = $SerialNumber;
        $asset->CustomFields->DOS->Direccion      = $objDireccion;
        $asset->CustomFields->DOS->Product        = $producto;
        $asset->CustomFields->DOS->Serial_Number        = $SERIAL_NUMBER;
        $asset->description=$SERIAL_NUMBER;

        $asset->save(RNCPHP\RNObject::SuppressAll);
          
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
          $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
          return false;
      }
      return true;
    }



    private function updateAsset($ObjAsset,$inventoryItemID, $NameHH, $SerialNumber, $id_ebs_direccion, $objDireccion, $producto,$SERIAL_NUMBER)
    {
      try
      {
          if (!($ObjAsset instanceof RNCPHP\Asset))
          {
           
            $this->error = "No es un Objeto HH";
            return false;
          }

          $asset = $ObjAsset;
          $asset->Name                              = substr($NameHH, 0, 80);
        
        
           $asset->Product                           = 2;
            $asset->SerialNumber                      = $SerialNumber;
            $asset->CustomFields->DOS->Direccion      = $objDireccion;
            $asset->CustomFields->DOS->Product        = $producto;
            $asset->CustomFields->DOS->Serial_Number        = $SERIAL_NUMBER;
            $asset->description=$SERIAL_NUMBER;
            $asset->save(RNCPHP\RNObject::SuppressAll);
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
          $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
          return false;
      }
      return true;
    }

    public function getLastError()
    {
        return $this->error;
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
