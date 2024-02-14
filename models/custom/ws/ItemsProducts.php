<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_2 as RNCPHP;

class ItemsProducts extends \RightNow\Models\Base
{
    //public $error = '';
    public  $error          = array ('numberID' => null , 'message' => null);
    private $nro_referencia = '';

    function __construct()
    {
        parent::__construct();
        //\RightNow\Libraries\AbuseDetection::check();
    }

    public function search($q_delfos, $q_partCode, $q_type,$ref_no)
    {
      try
      {
        if (empty($q_partCode))
          $q_partCode = '';
        if (empty($q_type))
          $q_type = '';
        if (empty($q_delfos))
          $q_delfos = '';


        RNCPHP\ConnectAPI::commit();

        $obj_incident = RNCPHP\Incident::fetch( $ref_no);
          
        if($obj_incident->CustomFields->c->tipo_contrato=='Arriendo' or  $obj_incident->Disposition->ID==27  or $obj_incident->CustomFields->c->tipo_contrato=='Convenio')
        {
          $a_products = RNCPHP\OP\Product::find("PartNumber like '%{$q_partCode}%' and CodeItem like '%{$q_delfos}%' and CategoryItem like '%{$q_type}%' limit 30");
          
        }
        else
        {
          $a_products = RNCPHP\OP\Product::find("PartNumber like '%{$q_partCode}%' and CodeItem like '%{$q_delfos}%' and CategoryItem like '%{$q_type}%' and (Atribute25 is null or Atribute25 =2 or Atribute25 =0) limit 30");
        }
        if (count($a_products) > 0 )
        {
          $a_result = array();

          foreach ($a_products as $product)
          {
            $a_tmp_result['id']          =  $product->ID;
            $a_tmp_result['code_delfos'] =  $product->CodeItem;
            $a_tmp_result['partNumber']  =  $product->PartNumber;
            $a_tmp_result['name']        =  $product->Name;
            $a_tmp_result['InventoryItemId'] =  $product->InventoryItemId;
            $a_tmp_result['UnitCostPrice'] =  $product->UnitCostPrice; //agregado por NV para DDDM
            $a_tmp_result['UnitSellingPrice'] =  $product->UnitSellingPrice; //agregado por NV para DDDM
            $a_result[] = $a_tmp_result;
          }
          return $a_result;
        }
        else
        {
          $this->error['message']  = "No se encontraron resultados";
          $this->error['numberID'] = 1;
          return false;
        }
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        RNCPHP\ConnectAPI::rollback();
        return false;
      }
    }

    public function getTypeProducts()
    {
      try
      {
        //$a_typeProducts = RNCPHP\OP\CategoryItem::find("ID is not NULL");
        $a_typeProducts = RNCPHP\OP\CategoryItem::find("ID in (1,2,6) order by DisplayOrder");
        return $a_typeProducts;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }
    }

    public function getLastError()
    {
      return $this->error['message'];
    }

    public function getNumberError()
    {
      return $this->error['numberID'];
    }

}
