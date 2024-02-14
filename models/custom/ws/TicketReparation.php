<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_2 as RNCPHP;

class TicketReparation extends \RightNow\Models\Base
{
    //public $error = '';
    public  $error          = array ('numberID' => null , 'message' => null);
    private $nro_referencia = '';

    function __construct()
    {
        parent::__construct();
        //\RightNow\Libraries\AbuseDetection::check();
    }

    public function createOrder($fatherRefNo, $accountID, $contactID, $shippingInstructions, $a_items,$despachar)
    {
      try
      {
          if (!empty($fatherRefNo))
          {
            $fatherIncident = RNCPHP\incident::fetch($fatherRefNo);
            if ($fatherIncident->AssignedTo->Account->ID != $accountID )
            {
              $this->error['message']  = "Cuenta no es propietaria del incidente Padre";
              $this->error['numberID'] = 1;
              return false;
            }


            $a_status_valids = array(167,137, 105,171); //167 : PRE SOLICITUD DE REPUESTOS // Solicitud de Cotización
            if (!in_array($fatherIncident->StatusWithType->Status->ID, $a_status_valids))
            {
              $this->error['message']  = "Incidente Padre no se encuentra en el estado necesario";
              $this->error['numberID'] = 1;
              return false;
            }


            if ($this->existActiveReparationIncident($fatherRefNo))
            {
              $this->error['message']  = "Existen actualmente ticket de reparación activos para ese ticket padre";
              $this->error['numberID'] = 1;
              return false;
            }
          }

          RNCPHP\ConnectAPI::commit();
          $incident                                         = new RNCPHP\Incident();
          $incident->Subject                                = "Solicitud de Reparación de ".$fatherRefNo;


          if (($fatherIncident->CustomFields->c->tipo_contrato == 'Cargo') and ($fatherIncident->Disposition->ID == 25 ))
          {
            $type = 47; // Repuestos Cargo
          }
          else
          {
            $type = $this->getTypeFlowFromFatherDisposition($fatherIncident->Disposition->ID); //Obtiene valor de tipo de flujo, según disposición.
          }

          $incident->Disposition                            = RNCPHP\ServiceDisposition::fetch($type);
          $incident->CustomFields->c->shipping_instructions = $shippingInstructions;
          $incident->CustomFields->c->external_reference=$despachar;
          $incident->CustomFields->OP->Incident             = RNCPHP\incident::fetch($fatherRefNo);
          $incident->PrimaryContact                         = RNCPHP\Contact::fetch($contactID);
          $incident->AssignedTo->Account                    = RNCPHP\Account::fetch($accountID);
          $incident->Save(RNCPHP\RNObject::SuppressExternalEvents);
   /* 
          Copia los datos de Ticket Padre 
          */
          $incident->CustomFields->c->soporte_telefonico=$fatherIncident->CustomFields->c->soporte_telefonico;
          if($fatherIncident->CustomFields->c->soporte_telefonico)
          {
            $incident->CustomFields->c->soporte_telefonico=$fatherIncident->CustomFields->c->soporte_telefonico;
          }

          if($fatherIncident->CustomFields->c->diagnostico->ID)
          {
            $incident->CustomFields->c->diagnostico->ID=$fatherIncident->CustomFields->c->diagnostico->ID;
          }
          if($fatherIncident->CustomFields->c->support_type->ID)
          {
            $incident->CustomFields->c->support_type->ID=$fatherIncident->CustomFields->c->support_type->ID;
          }

          foreach ($a_items as $selectedProduct) {
            if($selectedProduct['delete'] == true)
              continue;
            $item                   = new RNCPHP\OP\OrderItems();
            $item->QuantitySelected = $selectedProduct['quantity'];
            $item->Product          = RNCPHP\OP\Product::fetch($selectedProduct['id']);
            $item->Incident         = $incident;
            $item->Save();
          }
          return $incident->ReferenceNumber;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        RNCPHP\ConnectAPI::rollback();
        return false;
      }

    }

    public function updateOrder($refNo, $accountID, $shippingInstructions, $a_items, $send = false, $a_productNotFound = null,$despachar)
    {
      $incident = $this->getObjectTicket($refNo);

      if ($incident->AssignedTo->Account->ID != $accountID )
      {
        $this->error['message']  = "Cuenta no es propietaria del Ticket de Reparación";
        $this->error['numberID'] = 1;
        return false;
      }

      if ($incident->StatusWithType->Status->ID != 1)
      {
        //$estado = $incident->StatusWithType->Status->LookupName;
        $this->error['message']  = "El ticket no se encuetra en un estado para edición";
        $this->error['numberID'] = 1;
        return false;
      }

      if ($incident !== false)
      {
        try
        {
          RNCPHP\ConnectAPI::commit();

          $incident->CustomFields->c->shipping_instructions = $shippingInstructions;
          $incident->CustomFields->c->request_date_om       = date('U');
          $incident->CustomFields->c->gasto=0;
          $incident->CustomFields->c->external_reference=$despachar;
          $incident->Save(RNCPHP\RNObject::SuppressExternalEvents);

          foreach ($a_items as $selectedProduct)
          {

            if ($selectedProduct['line_id'] == null)
            {
                //Creación
                $item                   = new RNCPHP\OP\OrderItems();
                $item->QuantitySelected = $selectedProduct['quantity'];
                $item->Product          = RNCPHP\OP\Product::fetch($selectedProduct['id']);
                $item->Incident         = $incident;
                logMessage("Leyendo item x " . $item->Product->UnitCostPrice . " - " . $selectedProduct['quantity']);

                $incident->CustomFields->c->gastos =$incident->CustomFields->c->gasto + $item->Product->UnitCostPrice*$selectedProduct['quantity'];
                $item->Save();
            }
            else
            {
              //Actualización
              $item = RNCPHP\OP\OrderItems::fetch($selectedProduct['line_id']);
              if($selectedProduct['delete'] == false)
              {
                logMessage("Leyendo item d " . $item->Product->UnitCostPrice . " - " . $selectedProduct['quantity']);
logMessage("Leyendo item a " . $incident->CustomFields->c->gasto);
                $incident->CustomFields->c->gasto =$incident->CustomFields->c->gasto + $item->Product->UnitCostPrice*$selectedProduct['quantity'];
logMessage("Leyendo item b " . $incident->CustomFields->c->gasto);
                $item->QuantitySelected = $selectedProduct['quantity'];
                $item->Save();
              }
              else{
                $item->Destroy();
              }
            }
            //Se recomienda revisar el destroy, porque no vuelve a sumar
          }

          //caso Solicitar
          if ($send == true)
          {
            $cfg2 = RNCPHP\Configuration::fetch( CUSTOM_CFG_MONTO_MINIMO_EVALUACION );

            if(($incident->CustomFields->OP->Incident->CustomFields->c->diagnostico->ID==176) and $incident->CustomFields->OP->Incident->CustomFields->c->commercial_approval=='' and $incident->CustomFields->c->gasto > $cfg2->Value)
            {
              $CI = get_instance();
              $username = $CI->session->getSessionData("username");
              $password = $CI->session->getSessionData("password");
              initConnectAPI($username,$password);
              logMessage("Leyendo Condiciones3 " . $incident->CustomFields->c->gasto   . "  EVAL "  .  $cfg2->Value );
             $incident->StatusWithType->Status->ID = 186;  // Esperando informe //22-08 R.S
             $incident->CustomFields->OP->Incident->StatusWithType->Status->ID = 186;

             $incident->Save(RNCPHP\RNObject::SuppressExternalEvents);

             $incident->CustomFields->OP->Incident->Save(RNCPHP\RNObject::SuppressExternalEvents);
            }
            else
            {
             //caso Solicitar
             logMessage("Leyendo Condiciones4");
             $incident->StatusWithType->Status->ID = 178; //estado enviado
             //Lineas Productos no encontrados
             if (is_array($a_productNotFound))
             {
              $incident->CustomFields->c->require_new_parts = true;
                $resp   = $this->createProductNotFound($incident->ReferenceNumber,  $a_productNotFound);
                if ($resp === false)
                {
                  RNCPHP\ConnectAPI::rollback();
                  return false;
                }
             }

             $incident->Save(RNCPHP\RNObject::SuppressExternalEvents);
            }
            return $incident->ReferenceNumber;
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
      else
      {
        return false;
      }
    }

    private function createProductNotFound($refNo, $a_items)
    {
      $incident = $this->getObjectTicket($refNo);

      if (!is_array($a_items) and (count($a_items) > 0))
      {
        $this->error['message']  = "No se encontro una lista de items a registrar";
        $this->error['numberID'] = 1;
        return false;
      }

      try
      {
        foreach ($a_items as $key => $item)
        {
          $objPNF              = new RNCPHP\OP\ProductNotFound();
          $objPNF->Description = $item["description"];
          $objPNF->Quantity    = $item["quantity"];
          $objPNF->PartNumber  = $item["partNumber"];
          $objPNF->Incident    = $incident;
          $objPNF->State       = RNCPHP\OP\StateProductNotFound::fetch(1); // Estado Solicitado
          $objPNF->Save();
        }
        return true;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }

    }

    public function getObjectTicket($nro_referencia)
    {
        try
        {
            $incident = RNCPHP\Incident::fetch($nro_referencia);
            if (is_object($incident))
            {
                return $incident;
            }
            else
            {
              $this->error['message']  = "Ticket de Reparación no Encontrado";
              $this->error['numberID'] = 2;
              return false;
            }
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
            $this->error['numberID'] = 1;
            return false;
        }
    }

    public function reservationItems($nro_referencia, $omNumberOrder, $a_infoitems)
    {
      $incident = $this->getObjectTicket($nro_referencia);
      if ($incident !== false and ($incident instanceof RNCPHP\Incident ))
      {
        if ($incident->Disposition->Parent->ID != 39 and $incident->Disposition->ID != 24)
        {

          $this->error['message']  = "El ticket no es de tipo reparación";
          $this->error['numberID'] = 3;
          return false;
        }

        if ($incident->StatusWithType->Status->ID != 176) // Estado no es => 'Enviada a OM'
        {
          if ($incident->StatusWithType->Status->ID != 177 ){ //Estado no es => 'Aprobación de Despacho'
          	if ($incident->StatusWithType->Status->ID != 157 ){ //Estado no es => 'Repuesto No Abastesido'
          	  $this->error['message']  = "El ticket no se encuentra en el estado necesario 1";
          	  $this->error['numberID'] = 4;
          	  return false;
            }
          }
        }

        try
        {
          $this->insertPrivateNote($incident, "Items en Reserva ".json_encode($a_infoitems)); //Nota privada con el array de items ingresados
          RNCPHP\ConnectAPI::commit();
          $itemsSelected = RNCPHP\OP\OrderItems::find("Incident= '{$incident->ID}'");

          //Ciclo para Items de la lista inicial
          foreach ($itemsSelected as $itemSelected)
          {
           $found = false;
           foreach ($a_infoitems as $itemReserved)
           {
             if ($itemSelected->ID == $itemReserved['line_id'])
             {
               $itemSelected->QuantityReserved = $itemReserved['ordered_quantity'];
               $found = true;
               break;
             }
           }
           if ($found === false)
           {
              $itemSelected->QuantityReserved = 0;
           }
           $product                     = RNCPHP\OP\Product::fetch($itemSelected->Product->ID);
           $itemSelected->ConfirmedCost = $product->UnitCostPrice * $itemSelected->QuantityReserved;

           if ($itemSelected->Enabled === true)
              $itemSelected->State = 2; //Estado Item : Reservado
              $itemSelected->Save();
          }
          $a_new_items = array();
          //Ciclo para items nuevos
          foreach ($a_infoitems as $itemReserved)
          {
           $lineId   = $itemReserved['line_id'];
           $cadena   = "OE_ORDER_LINES_ALL";
           //Si encuentra una nueva linea que agregar
           if (!is_numeric($lineId) and strpos($lineId, $cadena) !== false )
           {
             $InventoryItemId = $itemReserved['Inventory_item_id'];
             $newProduct      = RNCPHP\OP\Product::first("InventoryItemId = '{$InventoryItemId}'");

             if (empty($newProduct))
             {
               $this->error['message']  = "El item {$itemReserved['Inventory_item_id']} no se encontro en la base de datos de Oracle Service Cloud";
               $this->error['numberID'] = 1;
               RNCPHP\ConnectAPI::rollback();
               return false;
             }

             $item                   = new RNCPHP\OP\OrderItems();
             $item->QuantitySelected = 0;
             $item->QuantityReserved = $itemReserved['ordered_quantity'];
             $item->Product          = $newProduct;
             $item->State            = 2; //reservado
             $item->Alternative      = true;
             $item->RefLineOM        = $lineId;
             $item->Incident         = $incident;
             $item->ConfirmedCost    = $newProduct->UnitCostPrice * $itemReserved['ordered_quantity']; //Sumar Precio
             $item->Save();
             $a_new_items[] = array("Inventory_item_id" => $itemReserved['Inventory_item_id'], "line_id" => $item->ID, "ref_line_om" => $lineId );
           }
          }

          $incident->StatusWithType->Status->ID = 177; //Estado en Confirmación de Despacho

          //if ($incident->CustomFields->OP->Incident->ReferenceNumber != null)
          if ($incident->CustomFields->OP->Incident->ReferenceNumber != null and $incident->Disposition->ID != 24)
          {
           $fatherIncident = RNCPHP\Incident::fetch($incident->CustomFields->OP->Incident->ReferenceNumber);
           $fatherIncident->StatusWithType->Status->ID = 177;
           $this->CierraCanival($fatherIncident);
           $fatherIncident->Save(RNCPHP\RNObject::SuppressExternalEvents);
          }

          if (!empty($omNumberOrder) and empty($incident->CustomFields->c->order_number_om))
          {
           $incident->CustomFields->c->order_number_om = $omNumberOrder;
          }
          $incident->Save(RNCPHP\RNObject::SuppressExternalEvents);
          //return true;
          return $a_new_items;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
          $this->error['message'] = "Codigo : ".$err->getCode()." ".$err->getMessage();
          $this->error['numberID'] = 1;
          RNCPHP\ConnectAPI::rollback();
          $this->insertPrivateNote($incident, $this->error['message']);
          return false;
        }
      }
      else {
        return false;
      }
    }

    public function confirmItems($nro_referencia, $a_infoitems, $guide_dispatch)
    {
      $incident = $this->getObjectTicket($nro_referencia);
      if ($incident !== false and ($incident instanceof RNCPHP\Incident ))
      {


        if ($incident->Disposition->Parent->ID != 39 and $incident->Disposition->ID != 24 and $incident->Disposition->ID != 70 )   // No es ticket de Reparación
        {


          $this->error['message']  = "El ticket no es de tipo reparación" ;
          $this->error['numberID'] = 3;
          return false;
        }

         /* Si PAsa por aqui es porque tiene Numero de OM
          Cuando el estado es => 'Cerrado', con la idea de no retornar un error.
           2 Cerrado
           149 Cancelado
           129 Información Validada
           111 Por Despachar
          
           175 Supervisión
         */
        
        /*if($incident->StatusWithType->Status->ID == 2 or $incident->StatusWithType->Status->ID==149 or $incident->StatusWithType->Status->ID==111  or $incident->StatusWithType->Status->ID==140  or $incident->StatusWithType->Status->ID==129){
          return true;
        }
        */
        switch ($incident->StatusWithType->Status->ID)
        {
          case 2:
          case 149:
          case 111:
          case 140:
         

          
                return true;
                break;
          case 129: 
          case 175:
          case 177:
          case 178:
          case 176:
          case 157:
          case 134:
                break;
          default:
                $this->error['message']  = "El ticket no se encuentra en el estado necesario 2: StatusID = {$incident->StatusWithType->Status->ID}";
                $this->error['numberID'] = 4;
                return false;
                break;
        }
       /*
        if ($incident->StatusWithType->Status->ID != 177 and $incident->StatusWithType->Status->ID != 176 and $incident->StatusWithType->Status->ID != 157) //Estado no es => 'Aprobación de Despacho' o 'Enviado a OM' o 'Repuesto no Abastecido'
        {
          $this->error['message']  = "El ticket no se encuentra en el estado necesario 2: StatusID = {$incident->StatusWithType->Status->ID}";
          $this->error['numberID'] = 4;
          return false;
        }*/

        try
        {
          $this->insertPrivateNote($incident, "Items Confirmados ".json_encode($a_infoitems)); //Nota privada con el array de items ingresados
          RNCPHP\ConnectAPI::commit();
          $itemsSelected = RNCPHP\OP\OrderItems::find("Incident= '{$incident->ID}'");
          foreach ($itemsSelected as $itemSelected)
          {
           $found = false;
           foreach ($a_infoitems as $itemConfirmed)
           {
             if ($itemSelected->ID == $itemConfirmed['line_id'])
             {
               // Si se salta la reservación(por alguna falla), se debe indicar que los reservados fueron iguales a los confirmados.
               if ($incident->StatusWithType->Status->ID === 176)
                  $itemSelected->QuantityReserved = $itemConfirmed['ordered_quantity'];
               $itemSelected->QuantityConfirmed = $itemConfirmed['ordered_quantity'];
               $found = true;
               break;
             }
           }

           if ($found === false)
           {
              $itemSelected->QuantityConfirmed = 0;
           }
           $product                     = RNCPHP\OP\Product::fetch($itemSelected->Product->ID);

           if ($found === true)
           {
             //Inicio -Actualizar precio del producto
             $product->UnitCostPrice      = $itemConfirmed['unit_cost_price'];
             $product->Save();
             //Fin - Actualización de precio
           }

           $itemSelected->ConfirmedCost = $product->UnitCostPrice * $itemSelected->QuantityConfirmed;

           if ($itemSelected->Enabled === true)
           {
              $itemSelected->State = 3; //Estado Item 2
           }
           $itemSelected->Save();
         }
		 //R.S.C 31/05/17 $incident->StatusWithType->Status->ID      = 2; //Estado en Cerrado
         $incident->StatusWithType->Status->ID      = 111; //Estado en Por Despachar
         $incident->CustomFields->c->guide_dispatch = $guide_dispatch; //Guia de Despacho
         $incident->Save(RNCPHP\RNObject::SuppressExternalEvents);

         if ($incident->CustomFields->OP->Incident->ReferenceNumber != null and $incident->Disposition->ID != 24)
         {
          $fatherIncident = RNCPHP\Incident::fetch($incident->CustomFields->OP->Incident->ReferenceNumber);
          if ($fatherIncident->Disposition->ID == 27)  //taller
          {
            $fatherIncident->StatusWithType->Status->ID = 137; //Estado Padre = "HH en Preparación".
            $this->CierraCanival($fatherIncident);
          }
          else
          {
            $fatherIncident->StatusWithType->Status->ID = 111; //Estado Padre = Por despachar.
            $this->CierraCanival($fatherIncident);
          }
          $fatherIncident->Save(RNCPHP\RNObject::SuppressExternalEvents);
         }

         if ($incident->Disposition->ID == 47)  //Repuestos Cargo
         {
           //Codigo para cambiar de estado de PPTO a despachado.
           $ppto = RNCPHP\Opportunity::first("CustomFields.OP.IncidentReparation.ID = {$incident->ID} and StatusWithType.Status.ID != 11");
           if (is_object($ppto) and ($ppto instanceof RNCPHP\Opportunity))
           {
             $ppto->StatusWithType->Status->ID      = 182; // Estado Despachado
             $ppto->Save(RNCPHP\RNObject::SuppressExternalEvents);
           }
         }

         return true;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
          $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
          $this->error['numberID'] = 1;
          RNCPHP\ConnectAPI::rollback();
          $this->insertPrivateNote($incident, $this->error['message']);
          return false;
        }
      }
      else {
        return false;
      }

    }

    public function cancelOrder($nro_referencia)
    {
      $incident = $this->getObjectTicket($nro_referencia);
      if ($incident !== false and ($incident instanceof RNCPHP\Incident ))
      {
        if ($incident->Disposition->Parent->ID != 39 and $incident->Disposition->ID != 24)
        {
          $this->error['message']  = "El ticket no es de tipo reparación";
          $this->error['numberID'] = 3;
          return false;
        }

        try
        {
          RNCPHP\ConnectAPI::commit();
          $incident->StatusWithType->Status->ID = 149; //Estado en Cerrado
          $incident->Save(RNCPHP\RNObject::SuppressExternalEvents);

          //if ($incident->CustomFields->OP->Incident->ReferenceNumber != null)
          $canibal= RNCPHP\Incident::find("CustomFields.OP.Incident.ReferenceNumber = '{$incident->CustomFields->OP->Incident->ReferenceNumber}'  and StatusWithType.StatusType != 2 and  Disposition.ID =43");
          $CountCanibal=count($canibal);
          if ($incident->CustomFields->OP->Incident->ReferenceNumber != null and $incident->Disposition->ID != 24)
          {
            $fatherIncident                                           = RNCPHP\Incident::fetch($incident->CustomFields->OP->Incident->ReferenceNumber);
            if($CountCanibal==0)
            {
              $fatherIncident->StatusWithType->Status->ID               = 162; //Estado Padre = Visita Técnico Asignado.
              $fatherIncident->CustomFields->c->seguimiento_tecnico->ID = 15; //Sub estado Padre = Visita Técnico Asignado.
            }
            else
            {
              $fatherIncident->StatusWithType->Status->ID               = 151; //Estado Padre = Por Despachar Canibal
              //$fatherIncident->CustomFields->c->seguimiento_tecnico->ID = 44; //Sub estado Padre = Visita Solicitud Repuesto Canibal.
            }
            $fatherIncident->Save(RNCPHP\RNObject::SuppressExternalEvents);
          }

          return true;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
          $this->error['message'] = "Codigo : ".$err->getCode()." ".$err->getMessage();
          $this->error['numberID'] = 1;
          RNCPHP\ConnectAPI::rollback();
          $this->insertPrivateNote($incident, $this->error['message']);
          return false;
        }

      }
      else {
        return false;
      }
    }

    private function CierraCanival($fatherIncident) 
    {
      $canibal= RNCPHP\Incident::find("CustomFields.OP.Incident.ReferenceNumber = '" . $fatherIncident->ReferenceNumber ."' and StatusWithType.StatusType not in(2,149) and  Disposition.ID =43"  ); 
        // $canibal= RNCPHP\Incident::find("CustomFields.OP.Incident.ReferenceNumber = '{$fatherIncident->ReferenceNumber}'  and StatusWithType.StatusType not in(2,149) and  Disposition.ID =43");
  
      foreach ($canibal as $tk)
      {
        $tk->StatusWithType->Status->ID=2;
        $tk->Save(RNCPHP\RNObject::SuppressAll);
      }
      return;
    }

    private function existActiveReparationIncident($fatherRefNo)
    {
      $a_incidents = RNCPHP\incident::find("CustomFields.OP.Incident.ReferenceNumber ='{$fatherRefNo}' and StatusWithType.StatusType != 2");
      if (count ($a_incidents) > 0)
        return true;
      else
        return false;
    }

    public function getActiveReparationIncident($fatherRefNo)
    {
      try
      {
        $obj_incident             = RNCPHP\incident::first("CustomFields.OP.Incident.ReferenceNumber ='{$fatherRefNo}' and StatusWithType.StatusType != 2");
        if (!empty($obj_incident) and ($obj_incident instanceof RNCPHP\Incident))
        {
          $a_orderItems           = RNCPHP\OP\OrderItems::find("Incident ='{$obj_incident->ID}' ");

          $obj_result             = new \stdClass();
          $obj_result->order      = $obj_incident;
          $obj_result->orderItems = $a_orderItems;

          return $obj_result;
        }
        else
        {
          return false;
          $this->error['message']  = "No se encuentra incidente activo";
          $this->error['numberID'] = 1;
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

    private function insertPrivateNote($incident, $textoNP)
    {
        try
        {
            $incident->Threads = new RNCPHP\ThreadArray();
            $incident->Threads[0] = new RNCPHP\Thread();
            $incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
            $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
            $incident->Threads[0]->Text = $textoNP;
            $incident->Save(RNCPHP\RNObject::SuppressAll);
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            return false;
        }
    }

    public function getTypeFlowFromFatherDisposition($disposition_father)
    {
      $disposition = 41; // Por defecto Servicio
      if ($disposition_father == 25)
          $disposition = 41;
      if ($disposition_father == 27 or $disposition_father == 28 )
          $disposition = 40;
      return $disposition;
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
