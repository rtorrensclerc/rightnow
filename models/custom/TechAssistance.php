<?php
namespace Custom\Models;
use RightNow\Connect\v1_3 as RNCPHP;

class TechAssistance extends \RightNow\Models\Base
{
    //public $error = '';
    public  $error          = array ('numberID' => null , 'message' => " ");
    private $nro_referencia = '';

    function __construct()
    {
        parent::__construct();
       
        $this->CI->load->model('custom/IncidentGeneral'); // Modelo
        //\RightNow\Libraries\AbuseDetection::check();
    }

    /**
     * Obtiene las del cliente mediante integración
     *
     * @param $contactId {Integer} ID del contacto con sesión activa
    */
    public function getHHsByOrganizationService($contactId)
    {
      $this->CI->load->model('custom/ws/DatosHH'); // Modelo
      try {
        $contact      = RNCPHP\Contact::fetch($contactId);
        $rut          = $contact->Organization->CustomFields->c->rut;
        $data         = json_decode($this->CI->DatosHH->getHHsByOrganizationService($rut));

        //Hubo una falla en la integración
        if ($data === false)
        {
          $this->error['message']  = "Error obteniendo las HH ".$this->CI->DatosHH->getLastError();
          $this->error['numberID'] = 1;
          return false;
        }

        $a_hhResponse = array();

        // Recorre y valida las HH del resultado
        foreach ($data->HHS as $key => $hh) {
          // Elimina la claves de texto que retorna el servicio como 'status' o
          // 'resultado' que se encuentran al mismo nivel de las HH
          if(!is_numeric($key)) continue;

          $a_hhResponse[] = $hh->NRO_HH;
        }

        // Ordena de forma incremental
        sort($a_hhResponse);

        return $a_hhResponse;
      }
      catch (RNCPHP\ConnectAPIError $err)
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }

    }

    public function getHHsByOrganization($contactId)
    {
      $this->CI->load->model('custom/ws/DatosHH'); // Modelo
      try
      {
        $contact = RNCPHP\Contact::fetch($contactId);
        $orgId   = $contact->Organization->ID;

        if (empty($orgId))
        {
          $this->error['message']  = "Contacto no tiene organización";
          return false;
        }

        $a_dir        = RNCPHP\DOS\Direccion::find("Organization.ID = {$orgId}");
        $a_hhResponse = array();

        foreach ($a_dir as $dir)
        {
          $idDir = $dir->ID;
          $a_hh  = RNCPHP\Asset::find("CustomFields.DOS.Direccion.ID = {$idDir}");

          foreach ($a_hh as $hh)
          {
            if (empty($hh->SerialNumber))
              continue;
            $a_hhResponse[] = $hh->SerialNumber;
          }

        }
        sort($a_hhResponse);

        return $a_hhResponse;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }

    }

    public function getListDir($contactId)
    {
      $this->CI->load->model('custom/ws/DatosHH'); // Modelo
      $CI = get_instance();
      try
      {
        $contact  = RNCPHP\Contact::fetch($contactId);
        $orgId    = $contact->Organization->ID;
        if (empty($orgId))
        {
          $this->error['message']  = "Contacto no tiene organización";
          return false;
        }
        $obj_info_contact= $CI->session->getSessionData('info_contact');
        $a_dir    = RNCPHP\DOS\Direccion::find("Organization.ID = {$obj_info_contact['Org_id']}");
        // echo json_encode($a_dir->ID);
        return $a_dir;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }
    }

    public function createTechAssistanceTicket($contactId, $a_Infohh, $a_contactInfo, $dirId)
    {

      $this->CI->load->model('custom/ws/DatosHH'); // Modelo
      try
      {

        RNCPHP\ConnectAPI::commit();

        $obj_contact                                   = RNCPHP\Contact::fetch($contactId);
        
        $incident                                      = new RNCPHP\Incident();
        $incident->PrimaryContact                      = $obj_contact;
        
        $incident->CustomFields->c->tipificacion_sugerida->ID  = $a_Infohh["suggested_type"];
        $incident->Subject                                     = "SOLICITUD ASISTENCIA TÉCNICA - WEB - ". strtoupper($incident->CustomFields->c->tipificacion_sugerida->LookupName);
        
        $incident->Product                                     = RNCPHP\ServiceProduct::fetch(68); // Solicitud
        $incident->Category                                    = RNCPHP\ServiceCategory::fetch(34); // Asistencia Técnica
        $incident->Disposition                                 = RNCPHP\ServiceDisposition::fetch(25); // Soporte Técnico
        $incident->StatusWithType                              = new RNCPHP\StatusWithType() ;
        $incident->StatusWithType->Status                      = new RNCPHP\NamedIDOptList() ;
        $incident->StatusWithType->Status->ID                  = 129 ;
        
        //Datos de SOLICITUD
        $incident->CustomFields->c->cont1_hh                   = (int) $a_Infohh['contador_bn'];
        $incident->CustomFields->c->cont2_hh                   = (int) $a_Infohh['contador_color'];
        
        //Datos de HH
        $incident->CustomFields->c->id_hh                   = $a_Infohh['hh'];
        $incident->CustomFields->c->marca_hh                = $a_Infohh['marca'];
        $incident->CustomFields->c->modelo_hh               = $a_Infohh['modelo'];
        $incident->CustomFields->c->convenio                = $a_Infohh['convenio'];
        if($a_Infohh['trx_id_erp']==1662)
        {
          $incident->CustomFields->c->tipo_contrato           = 'Cargo';
          //echo "CARGO trx_id_erp[" . $a_Infohh['trx_id_erp'] . "]";
          $incident->CustomFields->c->convenio=0;
        }
        else
        {
          $incident->CustomFields->c->tipo_contrato           = $a_Infohh['hh_tipo_contrato'];
          //echo "CONTRATO trx_id_erp[" . $a_Infohh['trx_id_erp'] . "]";
        }
      
        $incident->CustomFields->c->sla_hh                  = $a_Infohh['hh_sla'];
        $incident->CustomFields->c->sla_hh_rsn              = $a_Infohh['hh_rsn'];
        $incident->CustomFields->c->cliente_bloqueado       = $a_Infohh['a_hh_direccion_id']["Bloqueado"];
        $incident->CustomFields->c->serie_maq               = $a_Infohh['serie'];
        $incident->CustomFields->c->numero_delfos           = $a_Infohh['numero_delfos'];
        $incident->CustomFields->c->order_number_om_ref     = $a_Infohh['Rut'];
        $incident->CustomFields->c->shipping_instructions   = substr($a_contactInfo['name'] . " " . $a_contactInfo['phone'] . " " . $a_contactInfo['email'], 0, 254);
        
        $incident->CustomFields->c->direccion_incorrecta    = $a_Infohh["is_wrong_address"];
        $incident->CustomFields->c->direccion_correcta      = $a_Infohh["correct_address"];
        
        $incident->CustomFields->c->solution_type = $a_Infohh['trx_id_erp'];
        
        $incident->CustomFields->c->codigo_error            = $a_contactInfo['codigo_error'];
        $incident->CustomFields->c->equipo_detenido_cliente = (bool)$a_contactInfo['equipo_detenido_cliente'];

        $array_Direccion_obj                           = RNCPHP\DOS\Direccion::find('d_id = '. $dirId);
        if (is_array($array_Direccion_obj) and is_object($array_Direccion_obj[0]))
            $incident->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
        $incident->save(RNCPHP\RNObject::SuppressAll);
        
        if($a_contactInfo['detail'])
            $this->CI->IncidentGeneral->insertPrivateNote($incident->ID, $a_contactInfo['detail'], 3);
    
        // Crear Asociar Asset.
        if ($this->updateAssistanceAsset($incident) === FALSE) // Creación del activo
        {
            RNCPHP\ConnectAPI::rollback();
            return FALSE;
        }

        $counter_result = $this->saveCounters($incident, $a_Infohh["a_hh_contadores"]); // Creación de contadores
        if($counter_result === FALSE)
        {
            RNCPHP\ConnectAPI::rollback();
            return FALSE;
        }

        $incident->Save();

        return $incident;

      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        RNCPHP\ConnectAPI::rollback();
        $this->error['message']  = "'createTechAssistanceTicket'. Código : " . $err->getCode() . " " . $err->getMessage() . " Línea: " . $err->getLine();
        $this->error['numberID'] = 1;
        return FALSE;
      }
    }

    public function createSpecialTicket($a_hh,$id_tecnico)
    {
      $this->CI->load->model('custom/ws/DatosHH'); // Modelo
      /* CON HH BUSCAMOS TODOS LOS DATOS NECESARIOS */
      $info_HH                          = RNCPHP\Asset::first("SerialNumber ='" . $a_hh  ."'" );
     
      //return $info_HH->CustomFields->DOS->Product;
      //return $info_HH->CustomFields->DOS->Direccion->d_id;


      try
      {
        RNCPHP\ConnectAPI::commit();

        $obj_contact                                   = RNCPHP\Contact::fetch(19428);
        
        $incident                                      = new RNCPHP\Incident();
        $incident->PrimaryContact                      = $obj_contact;
        
        $incident->CustomFields->c->tipificacion_sugerida->ID  = 221;  /* Mantencion */
        //$incident->Subject                                     = "SOLICITUD ASISTENCIA TÉCNICA - ESPECIAL - ". strtoupper($incident->CustomFields->c->tipificacion_sugerida->LookupName);
        $incident->Subject                                     ="Mantención Asignada";
        $incident->Product                                     = RNCPHP\ServiceProduct::fetch(68); // Solicitud
        $incident->Category                                    = RNCPHP\ServiceCategory::fetch(34); // Asistencia Técnica
        $incident->Disposition                                 = RNCPHP\ServiceDisposition::fetch(95); // Soporte Especial
        $incident->StatusWithType                              = new RNCPHP\StatusWithType() ;
        $incident->StatusWithType->Status                      = new RNCPHP\NamedIDOptList() ;
        $incident->StatusWithType->Status->ID                  = 1 ;
        

        $incident->CustomFields->c->diagnostico->ID            = 61;  // Mantencion Preventiva
        $incident->CustomFields->c->motivo_solucion->ID        =124;  // Mantenimiento
        $incident->CustomFields->c->tipo->ID                   =30;   // Mantencion Preventiva
        $incident->CustomFields->c->seguimiento_tecnico->ID    =15;  // Visita Técnico Asignado	
        
        $incident->CustomFields->c->soporte_telefonico         =false; // NO

        //Datos de SOLICITUD
        $incident->CustomFields->c->cont1_hh                   = 0;
        $incident->CustomFields->c->cont2_hh                   = 0;
        
        //Datos de HH
        $incident->CustomFields->c->id_hh                   = $a_hh;
        $incident->AssignedTo->Account= RNCPHP\Account::fetch($id_tecnico); 
        /*
        $incident->CustomFields->c->marca_hh                = $a_Infohh['marca'];
        $incident->CustomFields->c->modelo_hh               = $a_Infohh['modelo'];
        $incident->CustomFields->c->convenio                = $a_Infohh['convenio'];
        $incident->CustomFields->c->tipo_contrato           = $a_Infohh['hh_tipo_contrato'];
        $incident->CustomFields->c->sla_hh                  = $a_Infohh['hh_sla'];
        $incident->CustomFields->c->sla_hh_rsn              = $a_Infohh['hh_rsn'];
        $incident->CustomFields->c->cliente_bloqueado       = $a_Infohh['a_hh_direccion_id']["Bloqueado"];
        $incident->CustomFields->c->serie_maq               = $a_Infohh['serie'];
        $incident->CustomFields->c->numero_delfos           = $a_Infohh['numero_delfos'];
        $incident->CustomFields->c->order_number_om_ref     = $a_Infohh['Rut'];
        $incident->CustomFields->c->shipping_instructions   = substr($a_contactInfo['name'] . " " . $a_contactInfo['phone'] . " " . $a_contactInfo['email'], 0, 254);
        
        $incident->CustomFields->c->direccion_incorrecta    = $a_Infohh["is_wrong_address"];
        $incident->CustomFields->c->direccion_correcta      = $a_Infohh["correct_address"];
        $incident->CustomFields->c->codigo_error            = $a_contactInfo['codigo_error'];
        $incident->CustomFields->c->equipo_detenido_cliente = (bool)$a_contactInfo['equipo_detenido_cliente'];

        */
        


        
        $array_Direccion_obj                           = RNCPHP\DOS\Direccion::find('d_id = '. $info_HH->CustomFields->DOS->Direccion->d_id);
        if (is_array($array_Direccion_obj) and is_object($array_Direccion_obj[0]))
            $incident->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
        $incident->save(RNCPHP\RNObject::SuppressAll);
      
       
        // Crear Asociar Asset.
        if ($this->updateAssistanceAsset($incident) === FALSE) // Creación del activo
        {
            RNCPHP\ConnectAPI::rollback();
            return FALSE;
        }

    

        $incident->Save();

        return $incident;

      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        RNCPHP\ConnectAPI::rollback();
        $this->error['message']  = "'createTechAssistanceTicket'. Código : " . $err->getCode() . " " . $err->getMessage() . " Línea: " . $err->getLine();
        $this->error['numberID'] = 1;
        return FALSE;
      }
    }

    public function updateAssistanceAsset($incident)
    {
      $this->CI->load->model('custom/ws/DatosHH'); // Modelo
        try
        {
            $asset = RNCPHP\Asset::first( "SerialNumber = '".$incident->CustomFields->c->id_hh."'");
            if (empty($asset))
            {
                $asset               = new RNCPHP\Asset;
                $nameHH              = $incident->CustomFields->c->id_hh . "-" . $incident->CustomFields->c->marca_hh . "-" . $incident->CustomFields->c->modelo_hh;
                $asset->Name         = substr($nameHH, 0, 80);
                $asset->Contact      = $incident->PrimaryContact;
                $asset->Product      = 2;
                $asset->SerialNumber = $incident->CustomFields->c->id_hh;
                $asset->save(RNCPHP\RNObject::SuppressAll);
            }

            if(intval($incident->CustomFields->c->id_hh) >= 200)
                $asset->CustomFields->DOS->Direccion =  $incident->CustomFields->DOS->Direccion;
            else 
                $incident->CustomFields->DOS->Direccion = $asset->CustomFields->DOS->Direccion;

            $asset->save(RNCPHP\RNObject::SuppressAll);
            $incident->Asset = $asset;
            // $incident->save(RNCPHP\RNObject::SuppressAll);
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $error                  = "Problema al generar activo | Codigo:: " . $err->getCode() . " " . $err->getMessage();
            $this->error['message'] = $error;
            return FALSE;
        }
    }

    public function saveCounters($incident, $array_counters)
    {
      $this->CI->load->model('custom/ws/DatosHH'); // Modelo
      if (is_array($array_counters))
      {
        try
        {
          foreach ($array_counters as $counter)
          {
            // Contadores
            $count_id               = (int) $counter['ID'];
            $count_tipo             = $counter['Tipo'];
            $count_valor            = (int) $counter['Valor'];
            $contador               = new RNCPHP\DOS\Contador();
            $contador->ContadorID   = $count_id;
            $contador->Valor        = $count_valor;
            $contador->Incident     = $incident;
            $contador->TipoContador = RNCPHP\DOS\TipoContador::fetch($count_tipo);
            $contador->Asset        = $incident->Asset;
            $contador->save(RNCPHP\RNObject::SuppressAll);
          }
          return TRUE;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
          $error                  = "'saveCounters' Codigo: " . $err->getCode() . " " . $err->getMessage();
          $this->error['message'] = $error;
          return false;
        }
      }
      else
      {
        $error                  = "Estructura no Valida en los contadores";
        $this->error['message'] = $error;
        return FALSE;
      }
    }

    public function assocLineToIncident($incidentId, $lineId)
    {
      $this->CI->load->model('custom/ws/DatosHH'); // Modelo
      try
      {
        $item           = RNCPHP\OP\OrderItems::fetch($lineId);
        $incident       = RNCPHP\Incident::fetch($incidentId);
        $item->Incident = $incident;
        $item->Save();
        return true;
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        $this->error['message'] = "Error Asociando linea : ".$err->getCode()." ".$err->getMessage();
        return false;
      }
    }

    public function createLine($supplierId, $quantitySuggested, $quantity)
    {
      $this->CI->load->model('custom/ws/DatosHH'); // Modelo
      try
      {
        $supplier                     = RNCPHP\OP\Product::fetch($supplierId);
        $orderitem                    = new RNCPHP\OP\OrderItems();
        $orderitem->QuantitySuggested = $quantitySuggested;
        $orderitem->QuantitySelected  = $quantity;
        $orderitem->IsSuggested       = true;
        $orderitem->Product           = $supplier;
        //$orderitem->Incident          = $incident;
        $orderitem->Save();
        return $orderitem->ID;
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        $this->error = "Error creando la linea : ".$err->getCode()." ".$err->getMessage();
        return false;
      }
    }

    public function updateAsset($a_Infohh, $obj_dir, $obj_contact, $inventoryItemId, $a_suppliers)
    {
      $this->CI->load->model('custom/ws/DatosHH'); // Modelo
      try
      {
        $id_hh                                      = $a_Infohh['hh'];
        $marca                                      = $a_Infohh['brand_hh'];
        $modelo                                     = $a_Infohh['model_hh'];
        $serial                                     = $a_Infohh['serial_hh'];

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


        $asset                                      = RNCPHP\Asset::first( "SerialNumber = '".$id_hh."'");
        if ($asset instanceof RNCPHP\Asset)
        {
          $asset->CustomFields->DOS->Direccion      = $obj_dir;
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
          $asset->Contact                           = $obj_contact;
          $asset->Product                           = 2;
          $asset->SerialNumber                      = $id_hh;
          $asset->CustomFields->DOS->Direccion      = $obj_dir;
          $asset->CustomFields->DOS->SerialDimacofi = $serial;
          $asset->CustomFields->DOS->Product        = $product;
          $asset->save(RNCPHP\RNObject::SuppressAll);
          return $asset;
        }
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        $this->error['message'] = "Problema al generar activo | Codigo : ".$err->getCode()." ".$err->getMessage();
        return false;
      }
    }

    public function getSuggested($asset, $cont1_hh, $cont2_hh, $quantityBlack, $quantityColor)
    {
      $this->CI->load->model('custom/ws/DatosHH'); // Modelo
      try
      {
        //$product = RNCPHP\DOS\Product::fetch($asset->CustomFields->DOS->Product->ID); //Se Obtiene el objeto producto(Equipo), asociado a la HH
        if (is_object ($asset->CustomFields->DOS->Product))
        {
          //Se verfica que exista un producto
          $productId =   $asset->CustomFields->DOS->Product->ID;
          if (empty($productId))
          {
            $this->error['message']  = "HH con id rn {$asset->ID}, no tiene equipo asociado, favor intentar nuevamente ";
            $this->error['numberID'] = 2;
            return false;
          }

          $counterBN         = $cont1_hh;
          $counterColor      = $cont2_hh;

          //Se buscan los Insumos asociados al Equipo
          $a_suppliers       = RNCPHP\OP\SuppliersRelated::find("Product.ID = {$productId} and (EnabledSupplierRequest = 1 or EnabledSupplierRequest is null)");

          $quantitySuppliers = count($a_suppliers);
          if ($quantitySuppliers > 0)
          {
            //Se buscan las ultimas solicitudes creadas para la HH en particular
            $lastSuppliersIncident      = RNCPHP\Incident::first("Asset.ID = {$asset->ID} and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont1_hh != 0 order by ClosedTime DESC");
            $lastSuppliersColorIncident = RNCPHP\Incident::first("Asset.ID = {$asset->ID} and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont2_hh != 0 order by ClosedTime DESC");
            $a_response = array();
            $a_response['supplier'] = array();
            $a_response['message'] = '';
            $a_response['message_black'] = '';
            $a_response['message_color'] = '';

            if (empty($lastSuppliersIncident) and empty($lastSuppliersColorIncident))
            {

              $a_response['message']  =  "No se encontraron tickets pasados para ningún contador, por lo que sugiere lo minimo";
              foreach ($a_suppliers as $key => $supplier)
              {
                $a_TempResponse['supplier_id']        = $supplier->Supplier->ID;
                $a_TempResponse['quantity_suggested'] = 0;
                $a_TempResponse['quantity']           = $quantityBlack;
                $a_TempResponse['toner_type']         = $supplier->Supplier->InputCartridgeType->ID;
                $a_response['supplier'][]             = $a_TempResponse;
              }

              return $a_response;
            }
            else
            {
              $founded       = false;
              $itemSuggested = null;
              $a_colorItems  = array();

              //Preparar todas las lineas por Equopi por equipo
              foreach ($a_suppliers as $key => $supplier)
              {
                //Una vez se encontró el Toner, los suguientes insumos se completan con 0
                if ($founded === true)
                {
                  $a_colorItems[] = $supplier->Supplier;
                  continue;
                }

                //Si existe solo 1, ese será el escogido, sin hacer la búsqueda por nombre
                if ($quantitySuppliers === 1)
                {
                  $itemSuggested = $supplier->Supplier;
                  $founded       = true;
                  continue;
                }

                //Logica que busca si el toner es negro
                $nameProduct = $supplier->Supplier->Name;
                $a_words     = array("negro", "negro", "black");
                $result      = searchArrayText($a_words, $nameProduct); //TODO: llevar a Helper utils

                if ($result === true)
                {
                  $itemSuggested = $supplier->Supplier;
                  $founded       = true;
                  continue;
                }
                else
                {
                  $a_colorItems[] = $supplier->Supplier;
                  continue;
                }
              }

              //Se calcula el sugerido para el campo escogido - Logica B/N
              if ($itemSuggested instanceof RNCPHP\OP\Product)
              {
                if (!empty($lastSuppliersIncident))
                {
                  $consumption    = $counterBN - $lastSuppliersIncident->CustomFields->c->cont1_hh;
                  //Si el consumo desde el último ticket es meno a ;
                  if ($consumption < 0)
                  {
                    $a_response['message_black']          = "Consumo {$consumption} es negativo al restar {$lastSuppliersIncident->CustomFields->c->cont1_hh} - {$counterBN}, por lo que se procede a sugerir lo minimo";
                    $a_TempResponse['supplier_id']        = $itemSuggested->ID;
                    $a_TempResponse['quantity_suggested'] = 0;
                    $a_TempResponse['quantity']           = $quantityBlack;
                    $a_TempResponse['toner_type']         = $supplier->Supplier->InputCartridgeType->ID;
                    $a_response['supplier'][]             = $a_TempResponse;
                  }
                  else if ($itemSuggested->TrueYieldToner <= 0) //Campo rendimiento del item es nulo o vacio
                  {
                    $a_response['message_black']          = "El campo rendimiento {$itemSuggested->TrueYieldToner} del item {$itemSuggested->Name}, es 0 o negativo, por lo que se pasa a sugerir lo minimo";
                    $a_TempResponse['supplier_id']        = $itemSuggested->ID;
                    $a_TempResponse['quantity_suggested'] = 0;
                    $a_TempResponse['quantity']           = $quantityBlack;
                    $a_response['supplier'][]             = $a_TempResponse;
                  }
                  else
                  {
                    //Pre sugerido
                    $preSuggested   = $consumption / $itemSuggested->TrueYieldToner;
                    //Porcentaje
                    $percentage     = $itemSuggested->Threshold / 100;
                    //Sugerido
                    $suggested      = $preSuggested + $percentage;
                    //Sugerido redondeado hacia abajo
                    //$ceilSuggested  = floor($suggested);
                    $ceilSuggested  = round($suggested);

                    //Valor sugerido
                    if ($ceilSuggested > 0)
                    {
                      $a_response['message_black']          = "Se asocio las linea sugerida, según comportamiento anterior. Sugerido = {$suggested}";
                      $a_TempResponse['supplier_id']        = $itemSuggested->ID;
                      $a_TempResponse['quantity_suggested'] = $ceilSuggested;
                      $a_TempResponse['quantity']           = $quantityBlack;
                      $a_TempResponse['toner_type']         = $supplier->Supplier->InputCartridgeType->ID;
                      $a_response['supplier'][]             = $a_TempResponse;
                    }
                    else
                    {
                      $a_response['message_black']          = "Valor sugerido es negativo {$ceilSuggested}, por lo que se pasa a sugerir lo minimo";
                      $a_TempResponse['supplier_id']        = $itemSuggested->ID;
                      $a_TempResponse['quantity_suggested'] = 0;
                      $a_TempResponse['quantity']           = $quantityBlack;
                      $a_TempResponse['toner_type']         = $supplier->Supplier->InputCartridgeType->ID;
                      $a_response['supplier'][]             = $a_TempResponse;
                    }
                  }
                }
                else
                {
                  $a_response['message_black']          = "No se encontraron ticket anteriores de color negro, por lo que sugiere lo minimo";
                  $a_TempResponse['supplier_id']        = $itemSuggested->ID;
                  $a_TempResponse['quantity_suggested'] = 0;
                  $a_TempResponse['quantity']           = $quantityBlack;
                  $a_TempResponse['toner_type']         = $supplier->Supplier->InputCartridgeType->ID;
                  $a_response['supplier'][]             = $a_TempResponse;
                }
              }

              if (count($a_colorItems) <= 0)
              {
                return $a_response;
              }

              //Inicio logica sugerido para Toner Color
              if (!empty($lastSuppliersColorIncident))
              {
                $consumption    = $counterColor - $lastSuppliersColorIncident->CustomFields->c->cont2_hh;
                //Consumo negativo color, se asigna lo minimo
                if ($consumption < 0)
                {
                  $a_response['message_color']  =  "Consumo Color {$consumption} es negativo al restar {$lastSuppliersIncident->CustomFields->c->cont2_hh} - {$counterColor}, por lo que se pasa a sugerir lo minimo";
                  foreach ($a_colorItems as $supplier)
                  {
                    $a_TempResponse['supplier_id']        = $supplier->ID;
                    $a_TempResponse['quantity_suggested'] = 0;
                    $a_TempResponse['quantity']           = $quantityColor;
                    $a_TempResponse['toner_type']         = $supplier->Supplier->InputCartridgeType->ID;
                    $a_response['supplier'][]             = $a_TempResponse;
                  }
                }
                else
                {
                  $rendimientoColorReal = 0;
                  foreach ($a_colorItems as $supplier)
                  {
                    //Rendimiento real es igual a la suma de todos los rendimientos
                    $rendimientoColorReal += $supplier->TrueYieldToner;
                  }

                  if ($rendimientoColorReal <= 0)
                  {
                    //Rendimiento no medible por lo que sugiere lo minimo
                    $a_response['message_color']  =  "El rendimiento de los insumos de color es igual a {$rendimientoColorReal}, por lo que se pasa a sugerir lo minimo";
                    foreach ($a_colorItems as $supplier)
                    {
                      $a_TempResponse['supplier_id']        = $supplier->ID;
                      $a_TempResponse['quantity_suggested'] = 0;
                      $a_TempResponse['quantity']           = $quantityColor;
                      $a_TempResponse['toner_type']         = $supplier->Supplier->InputCartridgeType->ID;
                      $a_response['supplier'][]             = $a_TempResponse;
                    }
                  }
                  else
                  {
                    //Pre sugerido
                    $preSuggested   = $consumption / $rendimientoColorReal;

                    //Sugerido redondeado hacia abajo
                    //$ceilSuggested  = floor($preSuggested);
                    $ceilSuggested  = round($preSuggested);

                    if ($ceilSuggested > 0)
                    {
                      $a_response['message_color']  =  "Se asocio las lineas color sugeridas, según comportamiento anterior. Sugerido = {$ceilSuggested}";
                      foreach ($a_colorItems as $supplier)
                      {
                        $a_TempResponse['supplier_id']        = $supplier->ID;
                        $a_TempResponse['quantity_suggested'] = $ceilSuggested;
                        $a_TempResponse['quantity']           = $quantityColor;
                        $a_TempResponse['toner_type']         = $supplier->Supplier->InputCartridgeType->ID;
                        $a_response['supplier'][]             = $a_TempResponse;
                      }
                    }
                    else
                    {
                      $a_response['message_color']  =  "Valor sugerido color es negativo {$ceilSuggested}, por lo que se pasa a sugerir lo minimo";
                      foreach ($a_colorItems as $supplier)
                      {
                        $a_TempResponse['supplier_id']        = $supplier->ID;
                        $a_TempResponse['quantity_suggested'] = 0;
                        $a_TempResponse['quantity']           = $quantityColor;
                        $a_TempResponse['toner_type']         = $supplier->Supplier->InputCartridgeType->ID;
                        $a_response['supplier'][]             = $a_TempResponse;
                      }
                    }
                  }
                }
              }
              else
              {
                $a_response['message_color']  =  "No se encontraron tickets pasados de color, por lo que se sugiere lo minimo";
                //Todos los demas se marcan en 0
                foreach ($a_colorItems as $supplier)
                {
                  $a_TempResponse['supplier_id']        = $supplier->ID;
                  $a_TempResponse['quantity_suggested'] = 0;
                  $a_TempResponse['quantity']           = $quantityColor;
                  $a_TempResponse['toner_type']         = $supplier->Supplier->InputCartridgeType->ID;
                  $a_response['supplier'][]             = $a_TempResponse;
                }
              }

              return $a_response;
            }
          }
          else
          {
            $this->error['message']  = "No se encontraron insumos asociados al Equipo con id {$productId} indicado ";
            $this->error['numberID'] = 2;
            return false;
          }
        }
        else
        {
          $this->error['message']  = "HH: {$asset->Serial} no tiene un equipo asociado";
          $this->error['numberID'] = 2;
          return false;
        }

      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }
    }

    public function requestTicket($incidentId, $a_items)
    {
      $this->CI->load->model('custom/ws/DatosHH'); // Modelo
      try
      {
        RNCPHP\ConnectAPI::commit();
        $incident = RNCPHP\Incident::fetch($incidentId);
        foreach ($a_items as $item)
        {
          $line                   = RNCPHP\OP\OrderItems::fetch($item['id']);
          $line->QuantitySelected = $item['quantity_selected'];
          $line->Save();
        }
        $incident->StatusWithType->Status->ID = 178;   //enviado
        $incident->Save();
        return true;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        RNCPHP\ConnectAPI::rollback();
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }
    }

    public function getInfoTicket($incidentId)
    {
      $this->CI->load->model('custom/ws/DatosHH'); // Modelo
      try
      {
        $incident                        = RNCPHP\Incident::fetch($incidentId);
        // $a_items                         = RNCPHP\OP\OrderItems::find("Incident.ID = {$incident->ID}");
        $obj_response                    = new \stdClass();

        // HH
        $obj_response->id_hh             = $incident->CustomFields->c->id_hh;
        $obj_response->serial_hh         = $incident->CustomFields->c->serie_hh;
        $obj_response->brand_hh          = $incident->CustomFields->c->marca_hh;
        $obj_response->model_hh          = $incident->CustomFields->c->modelo_hh;
        $obj_response->client_covenant   = $incident->CustomFields->c->convenio;            // Boolean
        $obj_response->client_blocked    = $incident->CustomFields->c->cliente_bloqueado;   // Boolean
        $obj_response->contract_type     = $incident->CustomFields->c->tipo_contrato;
        $obj_response->sla_hh_rsn        = $incident->CustomFields->c->sla_hh_rsn;
        $obj_response->delfos            = $incident->CustomFields->c->numero_delfos;
        $obj_response->machine_serial    = $incident->CustomFields->c->serie_maquina ;
        $obj_response->supplier_covenant = $incident->CustomFields->c->convenio_insumos;    // Boolean
        $obj_response->brackets_covenant = $incident->CustomFields->c->convenio_corchetes;  // Boolean

        // Contadores
        $obj_response->cont1_hh = $incident->CustomFields->c->cont1_hh;
        $obj_response->cont2_hh = $incident->CustomFields->c->cont2_hh;

        // Dirección
        $obj_response->address        = new \stdClass();
        $obj_response->address->id    = $incident->CustomFields->DOS->Direccion->d_id;       // ID de Dirección
        $obj_response->address->name  = $incident->CustomFields->DOS->Direccion->dir_envio;  // ID de Dirección
        $obj_response->suggested_type = $incident->CustomFields->c->tipificacion_sugerida;

        // Dirección de contacto
        $obj_response->contact_info            = $incident->CustomFields->c->shipping_instructions;
        $obj_response->codigo_error            = $incident->CustomFields->c->codigo_error;
        // echo '<pre>';
        // var_dump($incident->CustomFields->c);
        // echo '</pre>';
        $obj_response->equipo_detenido_cliente = $incident->CustomFields->c->equipo_detenido_cliente;
        // $obj_response->equipo_detenido_cliente = TRUE;

        // exit('<' . json_encoe($incident->CustomFields->c->equipo_detenido_cliente) . '>');

        // $a_objItems    = array();
        // foreach ($a_items as $item)
        // {
        //     $obj_item                        = new \stdClass();
        //     $obj_item->lineId                = $item->ID;
        //     $obj_item->name                  = $item->Product->Name;
        //     $obj_item->alias                 = $item->Product->Alias;
        //     $obj_item->part_number           = $item->Product->PartNumber;
        //     $obj_item->quantity_suggested    = $item->QuantitySuggested;
        //     $obj_item->quantity_selected     = $item->QuantitySelected;
        //     $a_objItems[]                    = $obj_item;
        // }

        if ($incident->StatusWithType->Status->ID != 1)
          $obj_response->read_only = true;
        else
          $obj_response->read_only = false;

        $obj_response->items = $a_objItems;

        return $obj_response;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }
    }

    public function getLastCounter($idHH)
    {
      $this->CI->load->model('custom/ws/DatosHH'); // Modelo
      try
      {
        $lastSuppliersIncident      = RNCPHP\Incident::first("CustomFields.c.id_hh = {$idHH} and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont1_hh != 0 order by ClosedTime DESC");
        $lastSuppliersColorIncident = RNCPHP\Incident::first("CustomFields.c.id_hh = {$idHH} and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont2_hh != 0 order by ClosedTime DESC");

        $lastCounter                = new \stdClass();
        $lastCounter->color         = 0;
        $lastCounter->bn            = 0;

        if ($lastSuppliersIncident instanceof RNCPHP\Incident)
        {
          $lastCounter->bn    = $lastSuppliersIncident->CustomFields->c->cont1_hh;
        }

        if ($lastSuppliersColorIncident instanceof RNCPHP\Incident)
        {
          $lastCounter->color = $lastSuppliersColorIncident->CustomFields->c->cont2_hh;
        }

        return $lastCounter;
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
