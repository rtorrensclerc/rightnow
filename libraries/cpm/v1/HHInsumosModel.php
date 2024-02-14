<?php
/*
 * Given a CPHP object, appends a string to the specified field and saves the
 * object.
 */
namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;

class HHInsumosModel
{
    private $inc_obj;
    public $error = '';

    function __construct($object)
    {
        $this->inc_obj = $object;
    }

    public function saveInfoHHinsumos($marca, $modelo, $sla, $sla_rsn, $bool_convenio, $hh_tipo_contrato,
                                      $array_contadores, $array_direcciones, $serie_hh, $numero_delfos,
                                      $bool_convenio_insumos, $bool_convenio_corchetes, $inventoryItemId, $codeItem,
                                      $a_suppliers,$a_suppliers_full,$Rut)
    {
      try
      {
        RNCPHP\ConnectAPI::commit();
        $incident                                      = RNCPHP\incident::fetch($this->inc_obj->ReferenceNumber);
        $incident->CustomFields->c->marca_hh           = $marca;
        $incident->CustomFields->c->modelo_hh          = $modelo;
        $incident->CustomFields->c->convenio           = (int) $bool_convenio;
        $incident->CustomFields->c->tipo_contrato      = $hh_tipo_contrato;
        $incident->CustomFields->c->sla_hh             = $sla;
        $incident->CustomFields->c->sla_hh_rsn         = $sla_rsn;
        $incident->CustomFields->c->cliente_bloqueado  = (int) $array_direcciones['Bloqueado'];
        $incident->CustomFields->c->serie_maq          = $serie_hh;
        $incident->CustomFields->c->numero_delfos      = $numero_delfos;
        $id_ebs_direccion                              = (int) $array_direcciones['ID_direccion'];
        //Campos nuevos
        $incident->CustomFields->c->convenio_corchetes = (int) $bool_convenio_corchetes;
        $incident->CustomFields->c->convenio_insumos   = (int) $bool_convenio_insumos;

        $incident->CustomFields->c->order_number_om_ref= $Rut;


        if (!is_array($array_direcciones))
        {
          $string      = print_r($array_direcciones, true);
          $this->error = "Objeto direcciones viene vacio ".$string;
          RNCPHP\ConnectAPI::rollback();
          return false;
        }


        $objDir = RNCPHP\DOS\Direccion::first('d_id = '. $id_ebs_direccion);
        if ($objDir instanceof RNCPHP\DOS\Direccion)
        {
            $incident->CustomFields->DOS->Direccion =  $objDir;
        }
        else
        {

            $this->error = "Dirección ID {$string} enviada por ws no se encuentra en Oracle RightNow";
            RNCPHP\ConnectAPI::rollback();
            return false;
        }

        if ($bool_convenio_insumos === false)
        {
          $incident->StatusWithType->Status->ID = 185; //Evaluación convenio
        }
        else
        {
          $incident->StatusWithType->Status->ID = 129; //Información validada
        }

        $incident->Save(RNCPHP\RNObject::SuppressAll);

        $asset = $this->updateAsset($marca, $modelo, $serie_hh, $objDir, $inventoryItemId, $a_suppliers,$a_suppliers_full);
        if ( $asset === false) //Creación del activo - HH
        {
            RNCPHP\ConnectAPI::rollback();
            return false;
        }
        else
        {
          $incident->Asset = $asset;
          $incident->Save(RNCPHP\RNObject::SuppressAll);
        }

        $counter_result = $this->saveCounters($array_contadores); //Creación de contadores
        if ($counter_result === false)
        {
            RNCPHP\ConnectAPI::rollback();
            return false;
        }

        return true;

      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
        RNCPHP\ConnectAPI::rollback();
        return false;
      }
    }

    private function saveCounters($array_counters)
    {
        if (is_array($array_counters))
        {
            try
            {
                RNCPHP\ConnectAPI::commit();
                foreach ($array_counters as $counter)
                {
                    //Contadores
                    $count_id               = $counter['ID'];
                    $count_tipo             = $counter['Tipo'];
                    $count_valor            = $counter['Valor'];
                    $contador               = new RNCPHP\DOS\Contador();
                    $contador->ContadorID   = $count_id;
                    $contador->Valor        = $count_valor;
                    $contador->Incident     = $this->inc_obj;
                    $contador->TipoContador = RNCPHP\DOS\TipoContador::fetch($count_tipo);
                    $contador->Asset        = $this->inc_obj->Asset;
                    $contador->save(RNCPHP\RNObject::SuppressAll);
                }
                return true;
            }
            catch ( RNCPHP\ConnectAPIError $err )
            {
                $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
                RNCPHP\ConnectAPI::rollback();
                return false;
            }
        }
        else
        {
            $this->error = "Estructura no Valida en los contadores";
            return false;
        }
    }


    private function updateAsset($marca, $modelo, $serial, $objDireccion, $inventoryItemId, $a_suppliers,$a_suppliers_full)
    {
      try
      {
        $id_hh                                      = $this->inc_obj->CustomFields->c->id_hh;

        if (empty($inventoryItemId))
        {
          $this->error = "El valor de Item_inventory_id no puede ser nulo";
          return false;
        }

        $product                                    = RNCPHP\OP\Product::first("InventoryItemId = {$inventoryItemId}");
        if (!$product instanceof RNCPHP\OP\Product)
        {
          //crear Equipo (producto)
          $product                                  = new RNCPHP\OP\Product();
          $product->InventoryItemId                 = $inventoryItemId;
          $product->Save(RNCPHP\RNObject::SuppressAll);
        }


        if (is_array($a_suppliers_full))
        {
          foreach ($a_suppliers_full as $key => $supplierId)
          {

              $objSupplier = RNCPHP\OP\Product::first("InventoryItemId = {$supplierId[1]}");
              if (!$objSupplier instanceof RNCPHP\OP\Product)
              {
                //Crear Insumo
                $objSupplier                  = new RNCPHP\OP\Product();
                $objSupplier->InventoryItemId = $inventoryItemId;
                $objSupplier->last_stock=$supplierId[8];
                $objSupplier->Save(RNCPHP\RNObject::SuppressAll);
                //Relación Insumos

              }
              else {

                $objSupplier->last_stock=$supplierId[8];
                $objSupplier->Save(RNCPHP\RNObject::SuppressAll);

              }


              $supplierRelation = RNCPHP\OP\SuppliersRelated::first("Supplier.InventoryItemId = {$supplierId[1]} and Product.ID = {$product->ID}");
              if (!$supplierRelation instanceof RNCPHP\OP\SuppliersRelated)
              {
                $newSupplierRelation           = new RNCPHP\OP\SuppliersRelated();
                $newSupplierRelation->Product  = $product;
                $newSupplierRelation->Supplier = $objSupplier;
                $newSupplierRelation->Save(RNCPHP\RNObject::SuppressAll);

              }


          }
        }


        $asset                                      = RNCPHP\Asset::first( "SerialNumber = '".$id_hh."'");
        if ($asset instanceof RNCPHP\Asset)
        {
          $asset->CustomFields->DOS->Direccion      = $objDireccion;
          $asset->CustomFields->DOS->SerialDimacofi = $serial;
          $asset->CustomFields->DOS->Product        = $product;
          $asset->save(RNCPHP\RNObject::SuppressAll);
          return $asset;
        }
        else
        {
          $asset                                    = new RNCPHP\Asset();
          $nameHH                                   = $id_hh."-".$marca."-".$modelo;
          $asset->Name                              = substr($nameHH, 0, 80);
          $asset->Contact                           = $this->inc_obj->PrimaryContact;
          $asset->Product                           = 2;
          $asset->SerialNumber                      = $id_hh;
          $asset->CustomFields->DOS->Direccion      = $objDireccion;
          $asset->CustomFields->DOS->SerialDimacofi = $serial;
          $asset->CustomFields->DOS->Product        = $product;
          $asset->save(RNCPHP\RNObject::SuppressAll);
          return $asset;
        }
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        $this->error = "Problema al generar activo | Codigo : ".$err->getCode()." ".$err->getMessage();
        return false;
      }
    }

    public function getLastError()
    {
        return $this->error;
    }


}
