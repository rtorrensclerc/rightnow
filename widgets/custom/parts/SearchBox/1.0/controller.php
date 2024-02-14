<?php

namespace Custom\Widgets\parts;

class SearchBox extends \RightNow\Libraries\Widget\Base
{
    public function __construct($attrs)
    {
        parent::__construct($attrs);
    }

    public function getData()
    {
      $this->CI->load->model('custom/ws/ItemsProducts');
      $this->data['result']          = $this->CI->ItemsProducts->getTypeProducts();


      $this->CI->load->model('custom/ws/TicketReparation');
      $obj_incident = $this->CI->TicketReparation->getObjectTicket($this->data['attrs']['ref_no']);

      if ($obj_incident == false)
      {
        $this->data['error']           = $this->CI->TicketReparation->getLastError();
        $this->data['js']['onlyParts'] = true;
      }
      else
      {
        if (($obj_incident->CustomFields->c->tipo_contrato == 'Cargo') and ($obj_incident->Disposition->ID == 25 ))
        {
          $type = 47; // Repuestos Cargo
        }
        else
        {
          $type = $this->CI->TicketReparation->getTypeFlowFromFatherDisposition($obj_incident->Disposition->ID); //Obtiene valor de tipo de flujo, según disposición.
        }

        switch ($type) {
          case 47: //ID de Disposición Cargo
            $this->data['js']['onlyParts'] = false;
            break;
          case 40: //ID de Disposición Taller
            $this->data['js']['onlyParts'] = true;
            break;
          case 41: //ID de Disposición Servicio Técnico
            $this->data['js']['onlyParts'] = true;
            break;
          default:
            $this->data['js']['onlyParts'] = true;
            break;
        }
      }

      return parent::getData();
    }

}
