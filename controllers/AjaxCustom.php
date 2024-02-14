<?php

namespace Custom\Controllers;
require_once(get_cfg_var('doc_root').'/include/ConnectPHP/Connect_init.phph');
use RightNow\Connect\v1_2 as RNCPHP;
use RightNow\Connect\Crypto\v1_3 as Crypto;

class AjaxCustom extends \RightNow\Controllers\Base
{
    //This is the constructor for the custom controller. Do not modify anything within
    //this function.
    public $error = '';

    function __construct()
    {
        parent::__construct();


    }


    public function testAsset()
    {
      $incident = RNCPHP\Incident::fetch("171110-000001");

      try
      {
        $id_hh                                      = $incident->CustomFields->c->id_hh;
        $objDireccion                               = $incident->CustomFields->DOS->Direccion;
        $marca                                      = $incident->CustomFields->c->marca_hh;
        $modelo                                     = $incident->CustomFields->c->modelo_hh;
        $serial                                     = $incident->CustomFields->c->serie_hh;
        $inventoryItemId                            = $incident->CustomFields->c->inventory_item_id;

        if (!empty($inventoryItemId))
        {

          $product                                    = RNCPHP\OP\Product::first("InventoryItemId = {$inventoryItemId}");
          if (!$product instanceof RNCPHP\OP\Product)
          {
            //crear Equipo (producto)
            $product                                  = new RNCPHP\OP\Product();
            $product->InventoryItemId                 = $inventoryItemId;
            $product->Save(RNCPHP\RNObject::SuppressAll);
          }

          $a_suppliers = null;
          if (!empty($incident->CustomFields->c->json_hh))
          {
            $a_json = json_decode($incident->CustomFields->c->json_hh, true);

            if (is_array($a_json))
            {
              $a_suppliers = $array_hh_data['respuesta']['suppliers'];
            }
          }

          if (is_array($a_suppliers))
          {
            foreach ($a_suppliers as $key => $supplierId)
            {
              $supplierRelation = RNCPHP\OP\SuppliersRelated::first("Supplier.InventoryItemId = {$supplierId} and Product.ID = {$product->ID}");
              if ($supplierRelation instanceof RNCPHP\OP\SuppliersRelated)
                continue;
              else
              {
                $objSupplier = RNCPHP\OP\Product::first("InventoryItemId = {$supplierId}");
                if (!$objSupplier instanceof RNCPHP\OP\Product)
                {
                  //Crear Insumo
                  $objSupplier                  = new RNCPHP\OP\Product();
                  $objSupplier->InventoryItemId = $inventoryItemId;
                  $objSupplier->Save(RNCPHP\RNObject::SuppressAll);
                }

                //Relación Insumos
                $newSupplierRelation           = new RNCPHP\OP\SuppliersRelated();
                $newSupplierRelation->Product  = $product;
                $newSupplierRelation->Supplier = $objSupplier;
                $newSupplierRelation->Save(RNCPHP\RNObject::SuppressAll);
              }
            }
          }
        }

        //$asset                                      = RNCPHP\Asset::first( "SerialNumber = '".$id_hh."'");


        $res = RNCPHP\ROQL::query("select a.ID, a.SerialNumber from Asset as a where a.SerialNumber = '{$id_hh}' LIMIT 1")->next();

        $assetId = null;
        while($row = $res->next()) {
            $assetId = $row['ID'];
        }

        if (!empty($assetId))
          $asset = RNCPHP\Asset::fetch($assetId);


        if ($asset instanceof RNCPHP\Asset)
        {
          //Actualizar
          $asset->CustomFields->DOS->Direccion      = $objDireccion;
          $asset->CustomFields->DOS->SerialDimacofi = $serial;
          if (!empty($product))
           $asset->CustomFields->DOS->Product        = $product;
          $asset->save(RNCPHP\RNObject::SuppressAll);
          echo "Ya exite";
        }
        else
        {
          //Crear
          $asset                                    = new RNCPHP\Asset();
          $nameHH                                   = $id_hh."-".$marca."-".$modelo;
          $asset->Name                              = substr($nameHH, 0, 80);
          $asset->Contact                           = $incident->PrimaryContact;
          $asset->Product                           = 2;
          $asset->SerialNumber                      = $id_hh;
          $asset->CustomFields->DOS->Direccion      = $objDireccion;
          $asset->CustomFields->DOS->SerialDimacofi = $serial;
          if (!empty($product))
           $asset->CustomFields->DOS->Product        = $product;
          $asset->save(RNCPHP\RNObject::SuppressAll);

        }

        $incident->Asset = $asset;
        $incident->Save(RNCPHP\RNObject::SuppressAll);
        echo "Incidente actualizado con éxito";
      }
      catch (RNCPHP\ConnectAPIError $err){
          //echo $err->getMessage();
          echo $err->getMessage();
      }
    }


    function testdate()
    {
      $incident = RNCPHP\Incident::fetch("170515-000373");
      echo $incident->UpdatedTime;
      //echo $incident->CreatedTime;
      //$fecha = strtotime($incident->CreatedTime);
      $fecha = date("d/m/Y H:i:s", $incident->UpdatedTime );
      //$fecha2 = gmdate("d/m/Y H:i:s", $incident->CreatedTime );
      echo $fecha;
    }

    function test()
    {
        phpinfo();
    }
    /**
     * Sample function for ajaxCustom controller. This function can be called by sending
     * a request to /ci/ajaxCustom/ajaxFunctionHandler.
     */
    function ajaxFunctionHandler()
    {
        $postData = $this->input->post('post_data_name');
        //Perform logic on post data here
        echo $returnedInformation;
    }

    function testcounters()
    {
        $json_counters = '{ "Contadores": [{
                                            "ID": "29513",
                                            "Tipo": "TOTAL",
                                            "Valor": "0"
                                        },
                                        {
                                            "ID": "29517",
                                            "Tipo": "IMPRESION B/N",
                                            "Valor": "0"
                                        },
                                        {
                                            "ID": "29514",
                                            "Tipo": "COPIA B/N",
                                            "Valor": "766052"
                                        },
                                        {
                                            "ID": "29522",
                                            "Tipo": "FORMATO A3 COLOR",
                                            "Valor": "0"
                                        },
                                        {
                                            "ID": "29524",
                                            "Tipo": "FORMATO B4 COLOR",
                                            "Valor": "0"
                                        },
                                        {
                                            "ID": "29515",
                                            "Tipo": "COPIA COLOR",
                                            "Valor": "766052"
                                        },
                                        {
                                            "ID": "29520",
                                            "Tipo": "SCANER COLOR",
                                            "Valor": "0"
                                        },
                                        {
                                            "ID": "29518",
                                            "Tipo": "IMPRESION COLOR",
                                            "Valor": "0"
                                        },
                                        {
                                            "ID": "29519",
                                            "Tipo": "SCANER B/N",
                                            "Valor": "0"
                                        },
                                        {
                                            "ID": "29523",
                                            "Tipo": "FORMATO B4 B/N",
                                            "Valor": "0"
                                        },
                                        {
                                            "ID": "29516",
                                            "Tipo": "FAX",
                                            "Valor": "0"
                                        },
                                        {
                                            "ID": "29521",
                                            "Tipo": "FORMATO A3 B/ NNNNNN",
                                            "Valor": "0"
                                        }]
                            }';

        $array_counters = json_decode($json_counters, true);

         if ($this->saveCounters($array_counters['Contadores']) === false)
            echo $this->getLastError();


    }

    public function saveCounters($array_counters)
    {
        $referenceNumber = "150622-000001";

        if (is_array($array_counters))
        {
            try
            {
                RNCPHP\ConnectAPI::commit();
                foreach ($array_counters as $counter)
                {
                    //Contadores
                    $count_id    = $counter['ID'];
                    $count_tipo  = $counter['Tipo'];
                    $count_valor = $counter['Valor'];
                    $contador               = new RNCPHP\DOS\Contador();
                    $contador->ContadorID   = $count_id;
                    $contador->Valor        = $count_valor;
                    //$contador->Incident   = $this->inc_obj;
                    $contador->Incident     = RNCPHP\Incident::fetch($referenceNumber);
                    $contador->TipoContador = RNCPHP\DOS\TipoContador::fetch($counter['Tipo']);
                    $contador->save();
                    //$contador->assert(assertion)      =

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

    public function getLastError()
    {
        return $this->error;
    }

    public function updateAsset2()
    {
        try
        {
            $incident = RNCPHP\Incident::fetch("150710-000011");
            $asset = RNCPHP\Asset::first( "SerialNumber = '197675'");

            /*if (empty($asset)) {

                $asset = new RNCPHP\Asset;
                $asset->Name = $this->inc_obj->CustomFields->c->id_hh."-".$this->inc_obj->CustomFields->c->marca_hh."-".$this->inc_obj->CustomFields->c->modelo_hh;
                $asset->Contact = $this->inc_obj->PrimaryContact;
                //$asset->Organization = $this->inc_obj->Organization;
                $asset->Product = 2;
                $asset->SerialNumber = $this->inc_obj->CustomFields->c->id_hh;
                $asset->save();

            }
            */

            $asset->CustomFields->DOS->Direccion =  $incident->CustomFields->DOS->Direccion;
            $asset->save();
            //$this->inc_obj->Asset = $asset;
            //$this->inc_obj->save();

            /*
            echo "<pre>";
            print_r($asset->CustomFields->DOS);
            echo "<pre>";
            /*
            $this->inc_obj->Asset = $asset;
            $this->inc_obj->Asset->DOS->Direccion =  $this->inc_obj->CustomFields->DOS->Direccion;
            $this->inc_obj->save();
            */

        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error = "Problema al generar activo | Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }
    }

    public function testblowfish()
    {
      $KEY_BLOWFISH          = "D3t1H6q0p6V7z8";
      //$json_data  = $this->blowfish->encrypt("hola", $KEY_BLOWFISH, 10, 22, null); //desencriptar blowfish
      /*
      $this->load->library('Blowfish', null); //carga Libreria de Blowfish

      $KEY_BLOWFISH          = "D3t1H6q0p6V7z8";
      $json_data  = $this->blowfish->encrypt("hola", $KEY_BLOWFISH, 10, 22, null); //desencriptar blowfish
      echo base64_encode($json_data);
      */

      $menu  = RNCPHP\ConnectAPI::getNamedValues('RightNow\\Connect\\Crypto\\v1_3\\BlowFish','Mode');
      foreach ($menu as $key => $value)
      {
        echo "ID ".$value->ID. " name ". $value->LookupName. "<br>";
      }

      echo "Padding <br>";
      $menu2  = RNCPHP\ConnectAPI::getNamedValues('RightNow\\Connect\\Crypto\\v1_3\\BlowFish','padding');
      foreach ($menu2 as $key => $value)
      {
        echo "ID ".$value->ID. " name ". $value->LookupName. "<br>";
      }





      try
      {
      	$cipher                       = new Crypto\BlowFish();
      	//$cipher->Mode                 = $menu[2];
        $cipher->Mode->ID               = 3;
        //$cipher->padding              = $menu2[1];
        $cipher->padding->ID            = 2;
        $cipher->Key                  = $KEY_BLOWFISH;
      	$cipher->Text                 = "Texto";

        print_r($cipher);
      	$cipher->encrypt();

        $encrypted_text = $cipher->EncryptedText;
      	echo "Encrypted Text : " .base64_encode($encrypted_text)."<br>";


      	$cipher->decrypt();
      	$decrypted_text = $cipher->Text;
      	echo "Decrypted Text : " .$decrypted_text;

    	}
      catch (Exception $err ){
      	echo $err->getMessage();
      }

    }


    public function updateAsset()
    {

        if (empty($_POST))
        {
          echo "No hay valores de post";
          return false;
        }


        try
        {

           $incidentId = $_POST["incidentId"];
           $marca      = $_POST["hh_marca"];
           $modelo     = $_POST["hh_modelo"];
           $contactId  = $_POST["contactId"];


            $incident = RNCPHP\Incident::fetch($incidentId);
            $asset    = RNCPHP\Asset::first( "SerialNumber = '".$incident->CustomFields->c->id_hh."'");
            if (empty($asset))
            {
                $asset               = new RNCPHP\Asset;
                $asset->Name         = $incident->CustomFields->c->id_hh."-".$marca."-".$Modelo;
                $asset->Contact      = RNCPHP\Contact::fetch($contactId);
                $asset->Product      = 2;
                $asset->SerialNumber = $incident->CustomFields->c->id_hh;
                $asset->save(RNCPHP\RNObject::SuppressAll);
            }

            $incident->Asset                 = $asset;
            /*
            if (is_object($incident->CustomFields->DOS->Direccion))
            {
              $asset->CustomFields->DOS->Direccion = $incident->CustomFields->DOS->Direccion;
            }

            */
            $incident->save(RNCPHP\RNObject::SuppressAll);
            echo "HH actualizado y asociada";
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            echo "Problema al generar activo | Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }
    }

}
