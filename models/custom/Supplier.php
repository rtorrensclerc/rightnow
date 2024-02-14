<?php
namespace Custom\Models;
use RightNow\Connect\v1_3 as RNCPHP;

class Supplier extends \RightNow\Models\Base
{
    //public $error = '';
    public  $error          = array ('numberID' => null , 'message' => " ");
    private $nro_referencia = '';

    function __construct()
    {
        parent::__construct();
        $this->CI->load->model('custom/ws/DatosHH'); // Modelo
        $this->CI->load->model('custom/IncidentGeneral'); // Modelo
        $this->CI->load->model('custom/GeneralServices'); // Modelo
        $this->CI->load->helper('utils_helper');
        //\RightNow\Libraries\AbuseDetection::check();
    }

    /**
    * Obtiene las del cliente mediante integración
    *
    * @param $contactId {Integer} ID del contacto con sesión activa
    */
    public function getHHsByOrganizationService($contactId)
    {
      try {
        $contact      = RNCPHP\Contact::fetch($contactId);
        $rut          = $contact->Organization->CustomFields->c->rut;
        $data         = json_decode($this->CI->DatosHH->getHHsByOrganizationService($rut));

        //Hubo una falla en la integración
        if ($data === false)
        {
          $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_ERROR_GETTING_HH_VAR), $this->CI->DatosHH->getLastError());
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
        $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_CODE_VAR_VAR), $err->getCode(), $err->getMessage());
        $this->error['numberID'] = 1;
        return false;
      }

    }

    public function getHHsByOrganization($contactId)
    {
      try
      {
        $contact = RNCPHP\Contact::fetch($contactId);
        $orgId   = $contact->Organization->ID;

        if (empty($orgId))
        {
          $this->error['message'] = getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_CONTACT_WITHOUT_ORG);
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
        $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_CODE_VAR_VAR), $err->getCode(), $err->getMessage());
        $this->error['numberID'] = 1;
        return false;
      }

    }

    public function getListDir($contactId)
    {
      $CI = get_instance();
      try
      {
        $contact  = RNCPHP\Contact::fetch($contactId);
        $orgId    = $contact->Organization->ID;
        if (empty($orgId))
        {
          $this->error['message']  = getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_CONTACT_WITHOUT_ORG);
          return false;
        }
        $contactId  = $this->CI->session->getProfile()->c_id->value;
        $ContactData = $this->CI->GeneralServices->getOrganizationStatus($contactId);
        $find_ruts ='';
        if(count($ContactData->Ruts->List->data)>1)
        {
          foreach($ContactData->Ruts->List->data  as $key => $irut)
          {
            $find_ruts = $find_ruts . "'". $irut->rut_cliente  ."',";
           
          }
        }
        else
        {
          $find_ruts = $find_ruts . "'".$contact->Organization->CustomFields->c->rut ."','" . $ContactData->Ruts->List->data->rut_holding . "'," ;
          
        }
        $find_ruts = $find_ruts . "'0'";
        
        $a_dir    = RNCPHP\DOS\Direccion::find("Organization.CustomFields.c.rut in ({$find_ruts})");
       
        return $a_dir;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_CODE_VAR_VAR), $err->getCode(), $err->getMessage());
        $this->error['numberID'] = 1;
        return false;
      }
    }

    public function createTicket($contactId, $a_Infohh, $a_contactInfo, $dirId)
    {
      try
      {
        RNCPHP\ConnectAPI::commit();

        $obj_contact               = RNCPHP\Contact::fetch($contactId);


        $incident                 = new RNCPHP\Incident();
        $incident->PrimaryContact = $obj_contact;
        $incident->Subject        = getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_SUPPLIER_REQUEST_WEB);

        $incident->Product     = RNCPHP\ServiceProduct::fetch(68);      // Insumo
        $incident->Category    = RNCPHP\ServiceCategory::fetch(51);     // Insumo
        $incident->Disposition = RNCPHP\ServiceDisposition::fetch(24);  // Insumo

        //Datos de SOLICITUD
        $incident->CustomFields->c->cont1_hh = $a_Infohh['contador_bn'];
        $incident->CustomFields->c->cont2_hh = $a_Infohh['contador_color'];

        //Datos de HH
        $incident->CustomFields->c->id_hh              = $a_Infohh['hh'];
        $incident->CustomFields->c->serie_hh           = $a_Infohh['serial_hh'];
        $incident->CustomFields->c->marca_hh           = $a_Infohh['brand_hh'];
        $incident->CustomFields->c->modelo_hh          = $a_Infohh['model_hh'];
        $incident->CustomFields->c->convenio           = $a_Infohh['client_covenant'];
        $incident->CustomFields->c->cliente_bloqueado  = $a_Infohh['client_blocked'];
        $incident->CustomFields->c->tipo_contrato      = $a_Infohh['contract_type'];
        $incident->CustomFields->c->sla_hh_rsn         = $a_Infohh['sla_hh_rsn'];
        $incident->CustomFields->c->numero_delfos      = $a_Infohh['delfos'];
        $incident->CustomFields->c->serie_maquina      = $a_Infohh['machine_serial'];
        $incident->CustomFields->c->convenio_insumos   = $a_Infohh['supplier_covenant'];
        $incident->CustomFields->c->convenio_corchetes = $a_Infohh['brackets_covenant'];
        $incident->CustomFields->c->solution_type      = $a_Infohh['trx_id_erp'];
        $incident->CustomFields->c->priorization       = $a_Infohh['priorization'];

        if($a_Infohh['priorization']=='1' )
        {
            $incident->CustomFields->c->priorization='1000';
           
        }
        $asset = RNCPHP\Asset::first("SerialNumber = '" . $incident->CustomFields->c->id_hh . "'");
        if($incident->CustomFields->c->solution_type=='1662')
        {
          
          $productId =   $asset->CustomFields->DOS->Product->ID;
          $a_suppliers       = RNCPHP\OP\SuppliersRelated::find("Product.ID ={$productId} and (EnabledSupplierRequest = 1 or EnabledSupplierRequest is null)");
          $a_colorItems  =  $a_suppliers;
          $jsonDataEncoded  = '{"condition":"\'-1\'';
          
          foreach( $a_colorItems as $supplier_tmp)
          {
            $supplier = $supplier_tmp->Supplier;
            if ($item->Enabled === false )
              continue;   
            $jsonDataEncoded  = $jsonDataEncoded . ',\'' . $supplier->CodeItem . '\'';
            
            
          }
          $jsonDataEncoded  = $jsonDataEncoded .'"}';

          $incident->CustomFields->c->json_hh =  json_encode($jsonDataEncoded);

          $precios=$this->CI->GeneralServices->getAgreementPriceitems($jsonDataEncoded);
          
          $incident->CustomFields->c->predictiondata =  json_encode($precios);
        }
        $inventoryItemId = $a_Infohh['inventory_item_id'];
        $a_supplier      = $a_Infohh['supplier'];

        // Asociar dirección
        $obj_dir                                       = RNCPHP\DOS\Direccion::first("d_id = $dirId");
        $incident->CustomFields->DOS->Direccion        = $obj_dir;

         // Crear Asociar Asset
        //$this->CI->IncidentGeneral->insertPrivateNote($incident->ID, " -->" . "Creando Ticket", 3);

        //$asset = $this->updateAsset($a_Infohh, $obj_dir, $obj_contact, $inventoryItemId, $a_supplier,$incident);

        if ($asset === false)
        {
          RNCPHP\ConnectAPI::rollback();
          return false;
        }

        $incident->Asset = $asset ;

        // Contact info
        $incident->CustomFields->c->shipping_instructions = $a_contactInfo['name']." ".$a_contactInfo['phone']." ".$a_contactInfo['comments'];

        // Otros
        $incident->CustomFields->c->get_suggested = true;  //calcular sugerido
        $incident->CustomFields->c->supply_reason = 113;   //Consumo Normal

        $incident->Save();

        return $incident;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        RNCPHP\ConnectAPI::rollback();
        $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_CODE_VAR_VAR), $err->getCode(), $err->getMessage());
        $this->error['numberID'] = 1;

        return false;
      }
    }

    public function createTechAssistanceTicket($contactId, $a_Infohh, $a_contactInfo, $dirId)
    {
      try
      {
        RNCPHP\ConnectAPI::commit();

        $obj_contact = RNCPHP\Contact::fetch($contactId);

        $incident                 = new RNCPHP\Incident();
        $incident->PrimaryContact = $obj_contact;
        $incident->Subject        = getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_TECHNICAL_ASSISTANCE_REQUEST_WEB);

        $incident->Product                    = RNCPHP\ServiceProduct::fetch(68);      // Solicitud
        $incident->Category                   = RNCPHP\ServiceCategory::fetch(34);     // Asistencia Técnica
        $incident->Disposition                = RNCPHP\ServiceDisposition::fetch(25);  // Soporte Técnico
        $incident->StatusWithType             = new RNCPHP\StatusWithType();
        $incident->StatusWithType->Status     = new RNCPHP\NamedIDOptList();
        $incident->StatusWithType->Status->ID = 129;

        // Datos de solicitud
        $incident->CustomFields->c->cont1_hh = $a_Infohh['contador_bn'];
        $incident->CustomFields->c->cont2_hh = $a_Infohh['contador_color'];

        // Datos de HH
        $incident->CustomFields->c->id_hh               = $a_Infohh['hh'];
        $incident->CustomFields->c->marca_hh            = $a_Infohh['marca'];
        $incident->CustomFields->c->modelo_hh           = $a_Infohh['modelo'];
        $incident->CustomFields->c->convenio            = $a_Infohh['convenio'];
        $incident->CustomFields->c->tipo_contrato       = $a_Infohh['hh_tipo_contrato'];
        $incident->CustomFields->c->sla_hh              = $a_Infohh['hh_sla'];
        $incident->CustomFields->c->sla_hh_rsn          = $a_Infohh['hh_rsn'];
        $incident->CustomFields->c->cliente_bloqueado   = $a_Infohh['a_hh_direccion_id']["Bloqueado"];
        $incident->CustomFields->c->serie_maq           = $a_Infohh['serie'];
        $incident->CustomFields->c->numero_delfos       = $a_Infohh['numero_delfos'];
        $incident->CustomFields->c->order_number_om_ref = $a_Infohh['Rut'];


        $array_Direccion_obj = RNCPHP\DOS\Direccion::find('d_id = '. $dirId);

        if (is_array($array_Direccion_obj) and is_object($array_Direccion_obj[0]))
        {
          $incident->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
        }

        $incident->save(RNCPHP\RNObject::SuppressAll);
    
        // Crear asociar asset
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

        if($a_contactInfo['detail'])
        {
          $this->CI->IncidentGeneral->insertPrivateNote($incident->ID, $a_contactInfo['detail'], 3);
        }
        
        $incident->CustomFields->c->shipping_instructions = $a_contactInfo['name'] . " " . $a_contactInfo['phone'] . " " . $a_contactInfo['comments'];

        $incident->Save();

        return $incident;

      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        RNCPHP\ConnectAPI::rollback();
        $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_CODE_VAR_VAR), $err->getCode(), $err->getMessage());
        $this->error['numberID'] = 1;
        return FALSE;
      }
    }

    public function updateAssistanceAsset($incident)
    {
      try
      {
        $asset = RNCPHP\Asset::first("SerialNumber = '".$incident->CustomFields->c->id_hh."'");
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
        $incident->save(RNCPHP\RNObject::SuppressAll);
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        $error                  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_ERROR_GENERATING_RESOURCE_CODE_VAR_VAR), $err->getCode(), $err->getMessage());
        $this->error['message'] = $error;
        return FALSE;
      }
    }

    public function saveCounters($incident, $array_counters)
    {
      if (is_array($array_counters))
      {
        try
        {
          foreach ($array_counters as $counter)
          {
            // Contadores
            $count_id               = $counter['ID'];
            $count_tipo             = $counter['Tipo'];
            $count_valor            = $counter['Valor'];
            $contador               = new RNCPHP\DOS\Contador();
            $contador->ContadorID   = $count_id;
            $contador->Valor        = $count_valor;
            $contador->Incident     = $incident;
            $contador->TipoContador = RNCPHP\DOS\TipoContador::fetch($counter['Tipo']);
            $contador->Asset        = $incident->Asset;
            $contador->save(RNCPHP\RNObject::SuppressAll);
          }
          return TRUE;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
          $error                  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_CODE_VAR_VAR), $err->getCode(), $err->getMessage());
          $this->error['message'] = $error;
          return false;
        }
      }
      else
      {
        $error                  = getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_INVALID_STRUCTURE_FOR_COUNTERS);
        $this->error['message'] = $error;

        return FALSE;
      }
    }

    public function createTicketMassive($contactId, $asset, $a_Infohh, $obj_dir, $incidentFatherID )
    {
      try
      {
        $obj_contact              = RNCPHP\Contact::fetch($contactId);
        $incident                 = new RNCPHP\Incident();
        $incident->PrimaryContact = $obj_contact;
        if(!empty( $incidentFatherID))
        {
          $incident->Subject=getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_SUPPLIER_REQUEST_MASIVE_WEB);
        }
        else
        {
          $incident->Subject        = "SOLICITUD DE INSUMOS WS";
        }
        $incident->Product     = RNCPHP\ServiceProduct::fetch(68);      //Solicitud
        $incident->Category    = RNCPHP\ServiceCategory::fetch(51);     //Insumo
         //$incident->Disposition = RNCPHP\ServiceDisposition::fetch(70);  //Insumo Masivo
        // se cambio la disposition
        $incident->Disposition = RNCPHP\ServiceDisposition::fetch(24);  //Insumo

        // Datos de SOLICITUD
        $incident->CustomFields->c->cont1_hh = $a_Infohh['contador_bn'];
        $incident->CustomFields->c->cont2_hh = $a_Infohh['contador_color'];

        // Datos de HH
        $incident->CustomFields->c->id_hh              = $a_Infohh['hh'];
        $incident->CustomFields->c->serie_hh           = $a_Infohh['serial_hh'];
        $incident->CustomFields->c->marca_hh           = $a_Infohh['brand_hh'];
        $incident->CustomFields->c->modelo_hh          = $a_Infohh['model_hh'];
        $incident->CustomFields->c->convenio           = $a_Infohh['client_covenant'];
        $incident->CustomFields->c->cliente_bloqueado  = $a_Infohh['client_blocked'];
        $incident->CustomFields->c->tipo_contrato      = $a_Infohh['contract_type'];
        $incident->CustomFields->c->sla_hh_rsn         = $a_Infohh['sla_hh_rsn'];
        $incident->CustomFields->c->numero_delfos      = $a_Infohh['delfos'];
        $incident->CustomFields->c->serie_maquina      = $a_Infohh['machine_serial'];
        $incident->CustomFields->c->convenio_insumos   = $a_Infohh['supplier_covenant'];
        $incident->CustomFields->c->convenio_corchetes = $a_Infohh['brackets_covenant'];

        // Asociar dirección
        // $obj_dir                                       = RNCPHP\DOS\Direccion::first("d_id = $dirId");
        $incident->CustomFields->DOS->Direccion = $obj_dir;

        // TODO: Asociar Lineas con sugerido
        $incident->Asset = $asset ;

        //
        //$incident->CustomFields->OP->Incident = RNCPHP\Incident::fetch($incidentFatherID);

        // Pone el estado en Informacion Validada
        $incident->StatusWithType->Status->ID =129;
        $incident->CustomFields->c->send_to_om=true;
        /** No se asocia ticket padre en caso de no estar */
        if(!empty( $incidentFatherID))
        {
          $incident->CustomFields->OP->Incident          = RNCPHP\Incident::fetch($incidentFatherID);
        }  

        //otros
        $incident->CustomFields->c->supply_reason = 113; // Consumo Normal

        $incident->Save();
        return $incident;
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        $this->error['message'] = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_ERROR_CREATING_INCIDENT), $err->getCode(), $err->getMessage());
        return false;
      }
    }

    public function createFatherIncident($contactId)
    {
      try
      {
        $obj_contact                                   = RNCPHP\Contact::fetch($contactId);
        $incident                                      = new RNCPHP\Incident();
        $incident->PrimaryContact                      = $obj_contact;
        $incident->Subject                             = "SOLICITUD INSUMOS Masivo";
        $incident->Product                             = RNCPHP\ServiceProduct::fetch(68); //Solicitud
        $incident->Category                            = RNCPHP\ServiceCategory::fetch(71); //Insumo Masivo
        $incident->Disposition                         = RNCPHP\ServiceDisposition::fetch(70); //Insumo Masivo
        $incident->Save();
        return $incident;
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        $this->error['message'] = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_ERROR_CREATING_INCIDENT_FATHER_VAR_VAR), $err->getCode(), $err->getMessage());
        return false;
      }
    }

    public function processFatherIncident($incidentId)
    {
      try
      {
        $incident                                      = RNCPHP\Incident::fetch($incidentId);
        $incident->CustomFields->c->req_auto           = true;
        $incident->Save();
        return $incident;
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        $this->error['message'] = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_ERROR_CREATING_INCIDENT_FATHER_VAR_VAR), $err->getCode(), $err->getMessage());
        return false;
      }
    }

    public function assocLineToIncident($incidentId, $lineId)
    {
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
        return sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_LINE_ERROR_VAR_VAR), $err->getCode(), $err->getMessage());
      }
    }

    public function createLine($supplierId, $quantitySuggested, $quantity,$Consumption)
    {
     
      try
      {
        $supplier                     = RNCPHP\OP\Product::fetch($supplierId);
        $orderitem                    = new RNCPHP\OP\OrderItems();
        $orderitem->QuantitySuggested = $quantitySuggested;
        $orderitem->QuantitySelected  = $quantity;
        $orderitem->Consumption       = $Consumption;
        $orderitem->IsSuggested       = true;
        $orderitem->Product           = $supplier;
        //$orderitem->Incident          = $incident;
        $orderitem->Save();
        return $orderitem->ID;
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        return sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_ERROR_VAR_VAR), $err->getCode(), $err->getMessage());
      }
    }

    public function updateAsset($a_Infohh, $obj_dir, $obj_contact, $inventoryItemId, $a_suppliers)
    {
      //echo json_encode($a_suppliers);
      try
      {
        $id_hh                                      = $a_Infohh['hh'];
        $marca                                      = $a_Infohh['brand_hh'];
        $modelo                                     = $a_Infohh['model_hh'];
        $serial                                     = $a_Infohh['serial_hh'];

        if (empty($inventoryItemId))
        {
          $this->error = getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_INVENTORY_ID_CANT_BE_NULL);

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

          //en caso de ser Convenio con Venta de Insumos, debemos traer los precios de cada insumo y descartar aquellos que no estan en convenio
          //invocar APi para traer precios  de General Service
          //$service=ConnectUrl::requestCURLJsonRaw($cfg2->Value ."/CustomerDataInfo/getItemsUSDValue", $jsonDataEncoded, $token);
  
          //$precios=$this->CI->IncidentGeneral->getAgreementPriceitems($a_suppliers);
          //$this->CI->IncidentGeneral->insertPrivateNote($incident->ID, $precios, 3);
          //$this->CI->IncidentGeneral->insertPrivateNote($incident->ID, json_encode($a_suppliers), 3);
          
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
        $this->error['message'] = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_ERROR_GENERATING_RESOURCE_CODE_VAR_VAR), $err->getCode(), $err->getMessage());
        return false;
      }
    }

    public function calculo_percent($consumption,$rendimientoReal,$supplier)
    {
      $resp = array();
      if($supplier->TeoricYieldToner>0)
      {
        $preSuggested   = $consumption / $supplier->TeoricYieldToner;
      }
      else
      {
        $preSuggested =0;
      }
      $Consumption    = $preSuggested*100;
      //Porcentaje
      $percentage     = $supplier->Threshold / 100;
      //Sugerido
      $suggested      = $preSuggested + $percentage;
      $ceilSuggested  = round($suggested); /* Aproxima ah arroba sobre 0.5   */
      //echo "->[" . json_encode($supplier->TrueYieldToner) . "-" .   $consumption ."-" .   $Consumption ."]<br>";
         
            $rendimientoReal=$supplier->TeoricYieldToner;
       
            if ($consumption < 0)
            {
              $resp['message_color']  =  sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_CONSUMPTION_COLOR_NEGATIVE), $consumption, $lastSuppliersIncident->CustomFields->c->cont2_hh, $counterColor);
            
              if($supplier->TeoricYieldToner>0)
                {
                  $preSuggested   = $consumption / $supplier->TeoricYieldToner;
                }
                else
                {
                  $preSuggested =0;
                }
                $Consumption    = $preSuggested*100;
                //Porcentaje
                $percentage     = $supplier->Threshold / 100;
                //Sugerido
                $suggested      = $preSuggested + $percentage;

                $resp['supplier_id']        = $supplier->ID;
                $resp['quantity_suggested'] = 0;
                $resp['quantity']           = 0;
                $resp['toner_type']         = $supplier->InputCartridgeType->ID;
                $resp['Consumption']           = $Consumption;
              
              
            }
            else
            {
              

              if ($rendimientoReal <= 0)
              {
                //Rendimiento no medible por lo que sugiere lo minimo
                //, $supplier->InputCartridgeType->TonerType
         
                $resp['message_color']  =  sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_COLOR_ITEM_MIN),$rendimientoReal);
                
                if($supplier->TeoricYieldToner>0)
                {
                  $preSuggested   = $consumption / $supplier->TeoricYieldToner;
                }
                else
                {
                  $preSuggested =0;
                }
                  $Consumption    = $preSuggested*100;
                  //Porcentaje
                  $percentage     = $supplier->Threshold / 100;
                  //Sugerido
                  $suggested      = $preSuggested + $percentage;
                  $resp['supplier_id']        = $supplier->ID;
                  $resp['quantity_suggested'] = 0;
                  $resp['quantity']           = 0;
                  $resp['toner_type']         = $supplier->InputCartridgeType->ID;
                  $resp['Consumption']           = $Consumption;
                  
                
              }
              else
              {
                //Pre sugerido
                $preSuggested   = $consumption / $rendimientoReal;
                //Sugerido redondeado hacia abajo
                //$ceilSuggested  = floor($preSuggested);


                $ceilSuggested  = round($preSuggested);


                if ($ceilSuggested > 0)
                {
                  $resp['message_color']  =  sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_COLOR_BACKSTORY), $ceilSuggested);
                 
                  if($supplier->TeoricYieldToner>0)
                  {
                    //$preSuggested   = $consumption / $supplier->TrueYieldToner;
                    $preSuggested   = $consumption / $supplier->TeoricYieldToner;
                    
                  }
                  else
                  {
                    $preSuggested =0;
                  }
                  $Consumption    = $preSuggested*100;
                  //Porcentaje
                  $percentage     = $supplier->Threshold / 100;
                  //Sugerido
                  $suggested      = $preSuggested + $percentage;
                  $ceilSuggested  = round($preSuggested);
                    $resp['supplier_id']        = $supplier->ID;
                    $resp['quantity_suggested'] = $ceilSuggested;
                    $resp['quantity']           = $quantityColor;
                    $resp['toner_type']         = $supplier->InputCartridgeType->ID;
                    $resp['Consumption']           = $Consumption;
                 
                  
                }
                else
                {
      
                  //$supplier->InputCartridgeType->TonerType,
                  $resp['message_color'] = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_NEGATIVE_VALUE_COLOR_MIN), $ceilSuggested);
                
                  
                  if($supplier->TeoricYieldToner>0)
                  {
                    $preSuggested   = $consumption / $supplier->TeoricYieldToner;
                  }
                  else
                  {
                    $preSuggested =0;
                  }
                    $Consumption    = $preSuggested*100;
                    //Porcentaje
                    $percentage     = $supplier->Threshold / 100;
                    //Sugerido
                    $suggested      = $preSuggested + $percentage;

                    $resp['supplier_id']        = $supplier->ID;
                    $resp['quantity_suggested'] = 0;
                    $resp['quantity']           = $quantityColor;
                    $resp['toner_type']         = $supplier->InputCartridgeType->ID;
                    $resp['Consumption']           = $Consumption;
                  
                }
              }
            }

      return $resp;
      
    }


    public function getSuggested($asset, $cont1_hh, $cont2_hh, $quantityBlack, $quantityColor,$quantityCyan, $quantityYellow, $quantityMagenta,$Actual,$Ultimo)
    {
    
      try
      {
        //$product = RNCPHP\DOS\Product::fetch($asset->CustomFields->DOS->Product->ID); //Se Obtiene el objeto producto(Equipo), asociado a la HH
        if (is_object ($asset->CustomFields->DOS->Product))
        {
          
          //Se verfica que exista un producto
          $productId =   $asset->CustomFields->DOS->Product->ID;
          if (empty($productId))
          {
            $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_HH_WITHOUT_DEVICE), $asset->ID);
            $this->error['numberID'] = 2;
            return false;
          }

          $counterBN         = $cont1_hh;
          $counterColor      = $cont2_hh;
         

          //Se buscan los Insumos asociados al Equipo
          // TODO:rtorrens , debemos buscar una manera de no usar 2 o mas items del mismo tipo, por ejemplo. solo elegir el mas popular o 
          // el items con mas stock. 
          $a_suppliers       = RNCPHP\OP\SuppliersRelated::find("Product.ID = {$productId} and (EnabledSupplierRequest = 1 or EnabledSupplierRequest is null)");

         
          
          $quantitySuppliers = count($a_suppliers);
          //echo "estamos <br>" . $quantitySuppliers . "<br>" ;
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
              
              $a_response['message'] = getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_SUGGEST_MIN);
              foreach ($a_suppliers as $key => $supplier_tmp)
              {
             
                $supplier=$supplier_tmp->Supplier;
                $a_TempResponse['supplier_id']        = $supplier->ID;
                $a_TempResponse['quantity_suggested'] = 0;
                switch($supplier->InputCartridgeType->ID)
                {
                  case 1:
                    $a_TempResponse['quantity']           = $quantityCyan;
                    break;
                  case 2:
                    $a_TempResponse['quantity']           = $quantityYellow;
                    break;
                  case 3:
                    $a_TempResponse['quantity']           = $quantityMagenta;
                    break;
                  case 4:
                    $a_TempResponse['quantity']           = $quantityBlack;
                    break;
                  case 5:
                    $a_TempResponse['quantity']           = $quantityBlack;
                    break;
                  case 18:
                    $a_TempResponse['quantity']           = $quantityBlack;
                    break;
                  case 23:
                    $a_TempResponse['quantity']           = $quantityBlack;
                    break;
                  
                  default:
                  $a_TempResponse['quantity']           = 0;
                }
                $a_TempResponse['toner_type']         = $supplier->InputCartridgeType->ID;
                $a_response['supplier'][]             = $a_TempResponse;
              }

              return $a_response;
            }
            else
            {
              
              $founded       = false;
              $itemSuggested = null;
              $a_colorItems  = array();


              $a_colorItems  =  $a_suppliers;
              //echo "estamos <br>" . json_encode($a_colorItems) . "<br>" ;
              //Inicio logica sugerido para Toner 
              if (!empty($lastSuppliersIncident) or !empty($lastSuppliersColorIncident))
              {
              
                $rendimientoColorReal = 0;
                foreach ($a_colorItems as $supplier)
                {
                  //echo "estamos <br>" . json_encode($supplier) . "<br>" ;
                  //Rendimiento real es igual a la suma de todos los rendimientos
                  if($supplier->InputCartridgeType->TonerType<>'Black')
                    {
                      
                      $rendimientoColorReal += $supplier->TeoricYieldToner;
                    }
                    else
                    {
                      $rendimientoBNReal = $supplier->TeoricYieldToner; 
                    }
                }
              
                foreach ($a_colorItems as $supplier_tmp)
                { 
                  $supplier=$supplier_tmp->Supplier;
                      //Sugerido consumo
                      if($supplier->InputCartridgeType->TonerType=='Black')
                      {
                        $consumption    = $counterBN - $lastSuppliersIncident->CustomFields->c->cont1_hh;
                        $sugerido  = $this->calculo_percent($consumption,$rendimientoBNReal,$supplier,$counterBN,$lastSuppliersIncident->CustomFields->c->cont1_hh);
                      }
                      else
                      {

                        $consumption    = $counterColor + $counterBN - $lastSuppliersIncident->CustomFields->c->cont2_hh - $lastSuppliersIncident->CustomFields->c->cont1_hh;
                        
                        
                        //$counterColor - $lastSuppliersIncident->CustomFields->c->cont2_hh;
                        $sugerido  = $this->calculo_percent($consumption,$rendimientoColorReal,$supplier,$counterColor,$lastSuppliersIncident->CustomFields->c->cont2_hh);
                      }

                      $a_response['message_color'] =$a_response['message_color']  . '-<br>' . $sugerido['message_color'];
                     
                      $a_TempResponse['supplier_id']        = $supplier->ID ;
                      $a_TempResponse['quantity_suggested'] = $sugerido['quantity_suggested'];
                      switch($supplier->InputCartridgeType->ID)
                      {
                        case 1:
                          $a_TempResponse['quantity']           = $quantityCyan;
                          break;
                        case 2:
                          $a_TempResponse['quantity']           = $quantityYellow;
                          break;
                        case 3:
                          $a_TempResponse['quantity']           = $quantityMagenta;
                          break;
                        case 4:
                          $a_TempResponse['quantity']           = $quantityBlack;
                          break;
                        case 5:
                          $a_TempResponse['quantity']           = $quantityBlack;
                          $a_TempResponse['quantity_suggested']=0;
                          break;
                        case 18:
                          $a_TempResponse['quantity']           = $quantityBlack;
                          break;
                        case 23:
                          $a_TempResponse['quantity']           = $quantityBlack;
                          break;
                        default:
                        $a_TempResponse['quantity']           = 0;
                      }
                      
                      $a_TempResponse['toner_type']         = $sugerido['toner_type'];
                      $a_TempResponse['Consumption']        = $sugerido['Consumption'];
                    
                     
                      $a_response['supplier'][]             = $a_TempResponse;
                }
              }
              else
              {
                $a_response['message_color'] = getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_MIN_COLOR);

                //Todos los demas se marcan en 0
                foreach ($a_colorItems as $supplier)
                {
                  $a_TempResponse['supplier_id']        = $supplier->ID;
                  $a_TempResponse['quantity_suggested'] = 0;
                  $a_TempResponse['quantity']           = $quantityColor;
                  $a_TempResponse['toner_type']         = $supplier->InputCartridgeType->ID;
                  $a_TempResponse['Consumption']        = 0;
                  $a_response['supplier'][]             = $a_TempResponse;
                }
              }
              return $a_response;
            }
          }
          else
          {
            $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_NO_SUPPLY_TO_DEVICE), $productId);
            $this->error['numberID'] = 2;
            return false;
          }
        }
        else
        {
          $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_HH_NO_DEVICE), $asset->Serial);
          $this->error['numberID'] = 2;
          return false;
        }

      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_ERROR_VAR_VAR), $err->getCode(), $err->getMessage());
        $this->error['numberID'] = 1;
        return false;
      }
    }

    public function requestTicket($incidentId, $a_items)
    {
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
        $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_ERROR_VAR_VAR), $err->getCode(), $err->getMessage());
        $this->error['numberID'] = 1;
        return false;
      }
    }

    public function getInfoTicket($incidentId)
    {
      try
      {
        $incident                        = RNCPHP\Incident::fetch($incidentId);
        
        $a_items                         = RNCPHP\OP\OrderItems::find("Incident.ID = {$incident->ID}");
        $obj_hh = RNCPHP\Asset::first("SerialNumber = '" . $incident->CustomFields->c->id_hh . "'");
       
        $obj_response                    = new \stdClass();

        // HH
        $obj_response->id_hh             = $incident->CustomFields->c->id_hh;
        $obj_response->serial_hh         = $incident->CustomFields->c->serie_hh;
        $obj_response->brand_hh          = $incident->CustomFields->c->marca_hh;
        $obj_response->model_hh          = $incident->CustomFields->c->modelo_hh;
        $obj_response->client_covenant   = $incident->CustomFields->c->convenio; //Boolean
        $obj_response->client_blocked    = $incident->CustomFields->c->cliente_bloqueado; //Boolean
        $obj_response->contract_type     = $incident->CustomFields->c->tipo_contrato;
        $obj_response->sla_hh_rsn        = $incident->CustomFields->c->sla_hh_rsn;
        $obj_response->delfos            = $incident->CustomFields->c->numero_delfos;
        $obj_response->machine_serial    = $incident->CustomFields->c->serie_maquina ;
        $obj_response->supplier_covenant = $incident->CustomFields->c->convenio_insumos; //Boolean
        $obj_response->brackets_covenant = $incident->CustomFields->c->convenio_corchetes; //Boolean

        //Contadores
        $obj_response->cont1_hh           = $incident->CustomFields->c->cont1_hh;
        $obj_response->cont2_hh           = $incident->CustomFields->c->cont2_hh;

        //Dirección
        $obj_response->address            = new \stdClass();
        $obj_response->address->id        = $incident->CustomFields->DOS->Direccion->d_id; //ID de Dirección
        $obj_response->address->name      = $incident->CustomFields->DOS->Direccion->dir_envio; //ID de Dirección

        //Dirección de contacto
        $obj_response->contact_info       = $incident->CustomFields->c->shipping_instructions;
        $obj_response->predictiondata     = $incident->CustomFields->c->predictiondata;

        $a_objItems    = array();
        foreach ($a_items as $item)
        {
            $obj_item                        = new \stdClass();
            $obj_item->lineId                = $item->ID;
            $obj_item->name                  = $item->Product->Name;
            $obj_item->alias                 = $item->Product->Alias;
            $obj_item->part_number           = $item->Product->PartNumber;
            $obj_item->InventoryItemId       = $item->Product->InventoryItemId;
            $obj_item->quantity_suggested    = $item->QuantitySuggested;
            $obj_item->quantity_selected     = $item->QuantitySelected;
            $a_objItems[]                    = $obj_item;
        }

        if ($incident->StatusWithType->Status->ID != 1)
          $obj_response->read_only = true;
        else
          $obj_response->read_only = false;

        $obj_response->items = $a_objItems;

        return $obj_response;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_ERROR_VAR_VAR), $err->getCode(), $err->getMessage());
        $this->error['numberID'] = 1;
        return false;
      }
    }

    public function cancelIncident($incidentId)
    {
      try
      {
        $incident                                = RNCPHP\Incident::fetch($incidentId);
        $incident->CustomFields->c->cancel_order = true; //Cancelar
        $incident->Save();
        return true;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_ERROR_VAR_VAR), $err->getCode(), $err->getMessage());
        $this->error['numberID'] = 1;
        return false;
      }
    }

    public function getLastCounter($idHH)
    {
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
        $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_ERROR_VAR_VAR), $err->getCode(), $err->getMessage());
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
