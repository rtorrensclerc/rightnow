<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;

class SaveHHAsync
{
    static function HandleIncident($runMode, $action, $incident, $cycle)
    {

        if ($cycle !== 0) return;
        $bannerNumber = 0;

        try 
        {
            // Se va a separar funcioanlidad de HH normal ó HH Insumos
            if ($incident->CustomFields->c->json_hh != NULL) 
            {
                $a_json_hh = json_decode($incident->CustomFields->c->json_hh, TRUE);
                if (array_key_exists("suppliers", $a_json_hh)) 
                {
                    self::updateAssetInsumos($incident, $a_json_hh["suppliers"]);
                } 
                else 
                {
                    self::updateAsset($incident);
                }
            } 
            else 
            {
                self::updateAsset($incident);
            }
        } 
        catch (Exception $e) 
        {
            self::insertPrivateNote($incident, "Error " . $e->getMessage());
            return FALSE;
        }
    }

    static function insertPrivateNote($incident, $textoNP)
    {
        try 
        {
            $incident->Threads                   = new RNCPHP\ThreadArray();
            $incident->Threads[0]                = new RNCPHP\Thread();
            $incident->Threads[0]->EntryType     = new RNCPHP\NamedIDOptList();
            $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
            $incident->Threads[0]->Text          = $textoNP;
            $incident->Save(RNCPHP\RNObject::SuppressAll);
        } 
        catch (RNCPHP\ConnectAPIError $err) 
        {
            $incident->Subject = "Error " . $err->getMessage();
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            return FALSE;
        }
    }

    static function insertBanner($incident, $typeBanner, $texto = '')
    {
        if (!is_numeric($typeBanner) and $typeBanner > 3 and $typeBanner < 0)
            $typeBanner = 1;

        $texto = '';
        if ($typeBanner == 3)
            $texto = "HH no pudo ser asignada";

        $incident->Banner->Text = $texto;
        $incident->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
        $incident->Save(RNCPHP\RNObject::SuppressAll);
    }

    static function updateAsset($incident)
    {
        try 
        {
            self::insertPrivateNote($incident, "updateAsset");
            $asset = NULL;
            $a_asset = RNCPHP\Asset::find("SerialNumber = '" . $incident->CustomFields->c->id_hh . "' LIMIT 1");

            if (count($a_asset) < 1) 
            {
                $asset               = new RNCPHP\Asset;
                $nameHH              = $incident->CustomFields->c->id_hh . "-" . $incident->CustomFields->c->marca_hh . "-" . $incident->CustomFields->c->modelo_hh;
                $asset->Name         = substr($nameHH, 0, 80);
                $asset->Contact      = $incident->PrimaryContact;
                $asset->Product      = 2;
                $asset->SerialNumber = $incident->CustomFields->c->id_hh;
                $asset->save(RNCPHP\RNObject::SuppressAll);
            } 
            else 
            {
                $asset = $a_asset[0];
            }

            if ($incident->CustomFields->c->id_hh >= 200) 
            {
                $asset->CustomFields->DOS->Direccion = $incident->CustomFields->DOS->Direccion;
                $asset->save(RNCPHP\RNObject::SuppressAll);
            } 
            else
                $incident->CustomFields->DOS->Direccion = $asset->CustomFields->DOS->Direccion;

            $incident->Asset                           = RNCPHP\Asset::fetch($asset->ID);
            $incident->CustomFields->c->hh_rel_created = TRUE;
            $incident->save();
            return TRUE;
        } 
        catch (RNCPHP\ConnectAPIError $err) 
        {
            self::insertPrivateNote($incident, "Problema al generar activo | Código : " . $err->getCode() . " " . $err->getMessage());
            return FALSE;
        }
    }

    static function updateAssetInsumos($incident, $a_suppliers_full)
    {
        try 
        {
            if ($incident->CustomFields->c->inventory_item_id == NULL)
            {
                self::insertPrivateNote($incident, "El valor de Item_inventory_id no puede ser nulo");
                return FALSE;
            }
            
            $product   = NULL;
            $a_product = RNCPHP\OP\Product::find("InventoryItemId = {$incident->CustomFields->c->inventory_item_id} LIMIT 1");
            if (count($a_product) < 1) 
            {
                // Crear Equipo (producto)
                $product                  = new RNCPHP\OP\Product();
                $product->InventoryItemId = $incident->CustomFields->c->inventory_item_id;
                $product->Save(RNCPHP\RNObject::SuppressAll);
            }
            else
                $product = $a_product[0];



            if (is_array($a_suppliers_full)) 
            {
                foreach ($a_suppliers_full as $key => $supplierId) 
                {
                    $objSupplier   = NULL;
                    $supplierId_id = $supplierId[1];
                    $a_objSupplier = RNCPHP\OP\Product::find("InventoryItemId = {$supplierId_id} LIMIT 1");
                    if (count($a_objSupplier) < 1) 
                    {
                        //Crear Insumo
                        $objSupplier                  = new RNCPHP\OP\Product();
                        $objSupplier->InventoryItemId = $incident->CustomFields->c->inventory_item_id;
                        $objSupplier->last_stock      = (int) $supplierId[8];
                        $objSupplier->Save(RNCPHP\RNObject::SuppressAll);
                    } 
                    else 
                    {
                        $objSupplier               = $a_objSupplier[0];
                        $objSupplier->last_stock   = (int) $supplierId[8];
                        $objSupplier->Save(RNCPHP\RNObject::SuppressAll);
                    }


                    $supplierRelation = RNCPHP\OP\SuppliersRelated::first("Supplier.InventoryItemId = {$supplierId_id} and Product.ID = {$product->ID}");
                    if (!$supplierRelation instanceof RNCPHP\OP\SuppliersRelated) 
                    {
                        $newSupplierRelation           = new RNCPHP\OP\SuppliersRelated();
                        $newSupplierRelation->Product  = $product;
                        $newSupplierRelation->Supplier = $objSupplier;
                        $newSupplierRelation->Save(RNCPHP\RNObject::SuppressAll);
                    }
                }
            }
            
            $asset = NULL;
            $a_asset = RNCPHP\Asset::find("SerialNumber = '" . $incident->CustomFields->c->id_hh . "' LIMIT 1");

            if (count($a_asset) < 1) 
            {
                $asset                                    = new RNCPHP\Asset();
                $nameHH                                   = $incident->CustomFields->c->id_hh . "-" . $incident->CustomFields->c->marca_hh . "-" . $incident->CustomFields->c->modelo_hh;
                $asset->Name                              = substr($nameHH, 0, 80);
                $asset->Contact                           = $incident->PrimaryContact;
                $asset->Product                           = 2;
                $asset->SerialNumber                      = $incident->CustomFields->c->id_hh;
                $asset->CustomFields->DOS->Direccion      = $incident->CustomFields->DOS->Direccion;
                $asset->CustomFields->DOS->SerialDimacofi = $incident->CustomFields->c->serie_maq;
                $asset->CustomFields->DOS->Product        = $product;
                $asset->save(RNCPHP\RNObject::SuppressAll);
            }
            else 
            {
                $asset                                    = $a_asset[0];
                $asset->CustomFields->DOS->Direccion      = $incident->CustomFields->DOS->Direccion;
                $asset->CustomFields->DOS->SerialDimacofi = $incident->CustomFields->c->serie_maq;
                $asset->CustomFields->DOS->Product        = $product;
                $asset->save(RNCPHP\RNObject::SuppressAll);
            }
            $incident->Asset                           = RNCPHP\Asset::fetch($asset->ID);
            $incident->CustomFields->c->hh_rel_created = TRUE;
            $incident->save(); // Este save disparará la regla que guardará los contadores
            return TRUE;
        }
        catch (RNCPHP\ConnectAPIError $err) 
        {
            self::insertPrivateNote($incident, "Problema al generar activo | Código : " . $err->getCode() . " " . $err->getMessage());
            return FALSE;
        }
        catch (\Exception $err) 
        {
            self::insertPrivateNote($incident, "Problema al generar activo | Código : " . $err->getCode() . " " . $err->getMessage());
            return FALSE;
        }
    }
}
