<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_2 as RNCPHP;

class TicketDevolution extends \RightNow\Models\Base
{
    public  $error          = array ('numberID' => null , 'message' => null);
    private $nro_referencia = '';

    function __construct()
    {
        parent::__construct();
        //\RightNow\Libraries\AbuseDetection::check();
    }

    public function create($omNumberOrder, $a_items, $omNumberOrderDev = null)
    {
      try
      {
        RNCPHP\ConnectAPI::commit();
        $incidentOM = $this->getInfoTicketReparation($omNumberOrder);
        if ($incidentOM === false)
        {
          $this->error['message']  = "Número OM no encotrado en RN";
          $this->error['numberID'] = 2;
          return false;
        }

        $incident                                         = new RNCPHP\Incident();
        $incident->Subject                                = "Solicitud de Devolución de Número de Orden ".$omNumberOrder;
        $incident->Disposition                            = RNCPHP\ServiceDisposition::fetch(42); //Solicitud de Devolución
        //$incident->CustomFields->OP->Incident           = RNCPHP\incident::fetch($fatherRefNo);
        $incident->AssignedTo->Account                    = RNCPHP\Account::fetch($incidentOM->AssignedTo->Account->ID);
        $incident->PrimaryContact                         = RNCPHP\Contact::fetch($incidentOM->PrimaryContact->ID);
        $incident->CustomFields->c->order_number_om_ref   = $omNumberOrder;
        $incident->CustomFields->c->order_number_om       = $omNumberOrderDev;
        $incident->Save();


        foreach ($a_items as $selectedProduct)
        {
          $item                   = new RNCPHP\OP\OrderItems();
          $item->QuantitySelected = $selectedProduct['ordered_quantity'];
          $product                = RNCPHP\OP\Product::first("InventoryItemId = {$selectedProduct['Inventory_item_id']}");
          if (empty($product))
          {
            $this->error['message']  = "No se encontro producto con ID {$selectedProduct['Inventory_item_id']}";
            $this->error['numberID'] = 3;
            RNCPHP\ConnectAPI::rollback();
            return false;
          }

          //Actualizar Precio del Producto
          if (is_object($product))
          {
            $product->UnitCostPrice      = $selectedProduct['unit_cost_price'];
            $product->Save();
          }

          $item->Product          = $product;
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

    private function getInfoTicketReparation($omNumberOrder)
    {
      $obj_incident = RNCPHP\incident::first("CustomFields.c.order_number_om ='{$omNumberOrder}'");
      if (is_object($obj_incident) and ($obj_incident instanceof RNCPHP\Incident))
        return $obj_incident;
      else
        return false;
    }

    private function insertPrivateNote($incident, $textoNP)
    {
        try{
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

    public function getLastError()
    {
      return $this->error['message'];
    }

    public function getNumberError()
    {
      return $this->error['numberID'];
    }

}
