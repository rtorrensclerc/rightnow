<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_2 as RNCPHP;

class EbsProducts extends \RightNow\Models\Base
{
    private $error = '';

    function __construct()
    {
        parent::__construct();
        //\RightNow\Libraries\AbuseDetection::check();
    }

    public function modifyProduct($inventoryItemID, $name, $codeItem, $partNumber, $unitCostPrice, $unitSellingPrice, $item_category,
                                                $description, $alternativeParentID, $unitMeasure, $disabled,
                                                $atribute1, $atribute2, $atribute3, $atribute4, $atribute5, $atribute9,$atribute25,$product)
    {
      //if (!empty($inventoryItemID) and !empty($codeItem) and !empty($partNumber))
      if (!empty($inventoryItemID) and !empty($codeItem))
      {
        try
        {
          $ObjProduct = RNCPHP\OP\Product::first("InventoryItemId = '{$inventoryItemID}'");
          if (empty($ObjProduct))
          {
            return $this->createProduct($inventoryItemID, $name, $codeItem, $partNumber, $unitCostPrice, $unitSellingPrice, $item_category,
                                                        $description, $alternativeParentID, $unitMeasure, $disabled,
                                                        $atribute1, $atribute2, $atribute3, $atribute4, $atribute5, $atribute9,$atribute25,$product);
          }
          else if (is_object($ObjProduct) and ($ObjProduct instanceof RNCPHP\OP\Product))
          {
            return $this->updateProduct($ObjProduct,$inventoryItemID, $name, $codeItem, $partNumber, $unitCostPrice, $unitSellingPrice,
                                                    $item_category, $description, $alternativeParentID, $unitMeasure, $disabled,
                                                    $atribute1, $atribute2, $atribute3, $atribute4, $atribute5, $atribute9,$atribute25,$product);
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
          $this->error = "Inventroy Item ID, Code Item o PartNumber vienen vacios";
          return false;
      }
    }


    private function createProduct($inventoryItemID, $name, $codeItem, $partNumber, $unitCostPrice, $unitSellingPrice, $item_category,
                                                $description, $alternativeParentID, $unitMeasure, $disabled,
                                                $atribute1, $atribute2, $atribute3, $atribute4, $atribute5, $atribute9,$atribute25,$product)
    {
      try
      {
          $Product = new RNCPHP\OP\Product();
          $Product->InventoryItemId    = $inventoryItemID;
          $Product->CodeItem           = $codeItem;
          if (!empty($partNumber))
            $Product->PartNumber         = $partNumber;
          $Product->UnitCostPrice      = $unitCostPrice;
          $Product->UnitSellingPrice   = $unitSellingPrice;
          switch ($item_category) {
            case 'repuesto':
              $Product->CategoryItem = RNCPHP\OP\CategoryItem::fetch(2);
              break;
            case 'accesorio':
              $Product->CategoryItem = RNCPHP\OP\CategoryItem::fetch(3);
              break;
            case 'insumo':
              $Product->CategoryItem = RNCPHP\OP\CategoryItem::fetch(1);
              break;
            case 'equipo':
              $Product->CategoryItem = RNCPHP\OP\CategoryItem::fetch(5);
              break;
            case 'material':
              $Product->CategoryItem = RNCPHP\OP\CategoryItem::fetch(4);
              break;
            case 'servicio':
              $Product->CategoryItem = RNCPHP\OP\CategoryItem::fetch(6);
              break;
            case 'computo':
              $Product->CategoryItem = RNCPHP\OP\CategoryItem::fetch(40);
              break;
            default:
              $this->error = "Item de Categoría no identificado";
              return false;
              break;
          }
          $Product->Description        = $description;
          $Product->Name               = $name;
          $Product->Enabled            = ($disabled == 1) ? false:true; //Invierte el resultado
          $Product->UnitMeasure        = $unitMeasure;
          $Product->Atribute1          = (empty($atribute1)) ? 0: $atribute1;
          $Product->Atribute2          = (empty($atribute2)) ? 0: $atribute2;
          $Product->Atribute3          = (empty($atribute3)) ? 0: $atribute3;
          $Product->Atribute4          = (empty($atribute4)) ? 0: $atribute4;
          $Product->Atribute5          = (empty($atribute5)) ? 0: $atribute5;
          $Product->Atribute25          = (empty($atribute25)) ? 0: $atribute25;
        
          if($product['TeoricYieldToner']>0)
          {
            $Product->TeoricYieldToner   = (empty($product['TeoricYieldToner'])) ? 0: $product['TeoricYieldToner'];
            $Product->TrueYieldToner   = (empty($product['TeoricYieldToner'])) ? 0: $product['TeoricYieldToner']/2.0;
          }
          if (!empty($atribute9 ))
            $Product->InputCartridgeType = RNCPHP\OP\InputCartridgeType::first("Code = '{$atribute9}'");


          if (!empty($alternativeParentID))
            $Product->AlternativeProduct = RNCPHP\OP\Product::first("InventoryItemId = $alternativeParentID");
          $Product->Save(RNCPHP\RNObject::SuppressAll);
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
          $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
          return false;
      }
      return true;
    }


    private function updateProduct($ObjProduct, $inventoryItemID, $name, $codeItem, $partNumber, $unitCostPrice, $unitSellingPrice, $item_category,
                                                $description, $alternativeParentID, $unitMeasure, $disabled,
                                                $atribute1, $atribute2, $atribute3, $atribute4, $atribute5, $atribute9,$atribute25,$product)
    {
      try
      {
          if (!($ObjProduct instanceof RNCPHP\OP\Product))
          {
            $this->error = "No es un Objeto Producto";
            return false;
          }

          $Product = $ObjProduct;
          $Product->InventoryItemId    = $inventoryItemID;
          $Product->CodeItem           = $codeItem;
          if (!empty($partNumber))
            $Product->PartNumber         = $partNumber;
          $Product->UnitCostPrice      = $unitCostPrice;
          $Product->UnitSellingPrice   = $unitSellingPrice;
          switch ($item_category) {
            case 'repuesto':
              $Product->CategoryItem = RNCPHP\OP\CategoryItem::fetch(2);
              break;
            case 'accesorio':
              $Product->CategoryItem = RNCPHP\OP\CategoryItem::fetch(3);
              break;
            case 'insumo':
              $Product->CategoryItem = RNCPHP\OP\CategoryItem::fetch(1);
              break;
            case 'equipo':
              $Product->CategoryItem = RNCPHP\OP\CategoryItem::fetch(5);
              break;
            case 'material':
              $Product->CategoryItem = RNCPHP\OP\CategoryItem::fetch(4);
              break;
            case 'servicio':
              $Product->CategoryItem = RNCPHP\OP\CategoryItem::fetch(6);
              break;
            case 'computo':
                $Product->CategoryItem = RNCPHP\OP\CategoryItem::fetch(40);
                break;
            default:
              $this->error = "Item  " . $item_category . " de Categoría no identificado";
              return false;
              break;
          }
          $Product->Description        = $description;
          $Product->Name               = $name;
          $Product->Enabled            = ($disabled == 1) ? false:true; //Invierte el resultado
          $Product->UnitMeasure        = $unitMeasure;
          $Product->Atribute1          = (empty($atribute1)) ? 0: $atribute1;
          $Product->Atribute2          = (empty($atribute2)) ? 0: $atribute2;
          $Product->Atribute3          = (empty($atribute3)) ? 0: $atribute3;
          $Product->Atribute4          = (empty($atribute4)) ? 0: $atribute4;
          $Product->Atribute5          = (empty($atribute5)) ? 0: $atribute5;
          $Product->Atribute9          = (empty($atribute9)) ? 0: $atribute9;
          $Product->Atribute25          = (empty($atribute25)) ? 0: $atribute25;
          if($product['TeoricYieldToner']>0)
          {
          $Product->TeoricYieldToner   = (empty($product['TeoricYieldToner'])) ? 0: $product['TeoricYieldToner'];
          $Product->TrueYieldToner   = (empty($product['TeoricYieldToner'])) ? 0: $product['TeoricYieldToner'];
          }
          if($product['TeoricYieldToner']>0)
          {
            $Product->TeoricYieldToner   = (empty($product['TeoricYieldToner'])) ? 0: $product['TeoricYieldToner'];
            $Product->TrueYieldToner   = (empty($product['TeoricYieldToner'])) ? 0: $product['TeoricYieldToner']/2.0;
          }
          if (!empty($atribute9))
            $Product->InputCartridgeType = RNCPHP\OP\InputCartridgeType::first("Code = '{$atribute9}' ");


          if (!empty($alternativeParentID))
          {
            $Product->AlternativeProduct = RNCPHP\OP\Product::first("InventoryItemId = $alternativeParentID");
          }
          $Product->Save(RNCPHP\RNObject::SuppressAll);
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


}
