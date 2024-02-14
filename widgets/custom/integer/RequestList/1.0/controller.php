<?php

namespace Custom\Widgets\integer;
/*
"order_detail": {
    "ref_no": "xxxx-xxxx",
    "father_ref_no": "160503-000105",
    "shipping_instructions": "lorem ipsum",
    "action": 1,
    "type": 40,
    "list_items": [{
        "id": 1234,
        "line_id": 1234,
        "quantity": 3,
        "delete": false
    }, {
        "id": 1234,
        "line_id": 1234,
        "quantity": 3,
        "delete": true
    }, {
        "id": 1234,
        "line_id": 1234,
        "quantity": 3,
        "delete": false
    }]
}
*/
class RequestList extends \RightNow\Libraries\Widget\Base
{
    public function __construct($attrs)
    {
        parent::__construct($attrs);
    }

    public function getData()
    {
      $this->CI->load->model('custom/ws/TicketReparation');
      $result = $this->CI->TicketReparation->getActiveReparationIncident($this->data['attrs']['ref_no']);

      // $this->data['js']['order_detail']['list_items']           = array();
      // $this->data['js']['order_detail']['list_items_not_found'] = array();

      if ($result == false)
      {
        $this->data['error'] = $this->CI->TicketReparation->getLastError();
      }
      else
      {

        $this->data['orderItems'] = $result->orderItems;

        // Determina el valor de la disposición
        $disposition_father = $result->order->Disposition->Parent->ID;
        $disposition = 0;
        if ($disposition_father == 25) $disposition = 41;
        if ($disposition_father == 27 or $disposition_father == 28 ) $disposition = 40;

        // Objeto JS
        $this->data['js']['order_detail'];
        $this->data['js']['order_detail']['ref_no']                = $result->order->ReferenceNumber;
        $this->data['js']['order_detail']['father_ref_no']         = $result->order->CustomFields->OP->Incident->ReferenceNumber;
        $this->data['js']['order_detail']['shipping_instructions'] = $result->order->CustomFields->c->shipping_instructions;
        $this->data['js']['order_detail']['action'];
        //$this->data['js']['order_detail']['type'];
        $this->data['js']['order_detail']['type']                 = $disposition;
        $this->data['js']['order_detail']['list_items']           = array();
        $this->data['js']['order_detail']['list_items_not_found'] = array();

        // dev_print($result->orderItems[0],NULL,0,3,"\$result->orderItems[0]");
        for ($i=0; $i < count($result->orderItems); $i++){
          $item['id'] = $result->orderItems[$i]->Product->ID;
          $item['name'] = $result->orderItems[$i]->Product->Name;
          $item['partNumber'] = $result->orderItems[$i]->Product->PartNumber;
          $item['line_id'] = $result->orderItems[$i]->ID;
          $item['quantity'] = $result->orderItems[$i]->QuantitySelected;
          $item['delete'] = false;
          $this->data['js']['order_detail']['list_items'][] = $item;
        }
      }

      return parent::getData();
    }
}
