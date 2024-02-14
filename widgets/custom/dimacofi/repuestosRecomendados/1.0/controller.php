<?php
namespace Custom\Widgets\dimacofi;

class repuestosRecomendados extends \RightNow\Libraries\Widget\Base {
    public function __construct($attrs) {
        parent::__construct($attrs);
    }
   public function getData() {
		
		$this->CI->load->model('custom/ws/TicketReparation');
       $result = $this->CI->TicketReparation->getActiveReparationIncident($this->data['attrs']['ref_no']);
	   
	     if ($result == false)
      {
        $this->data['error'] = $this->CI->TicketReparation->getLastError();
        // $this->CI->session->setSessionData(array('Request_info' => ''));
        // $this->data['Request_info'] = '';
        $this->CI->session->setFlashData('Request_info', serialize(''));
      }
      else {
	
        $this->data['order']                 = $result->order;
        $a_request_values['idStatus']        = $result->order->StatusWithType->Status->ID;
        $a_request_values['idHH']            = $result->order->CustomFields->c->id_hh;
        $a_request_values['id']              = $result->order->ID;
        $a_request_values['ref_no']          = $result->order->ReferenceNumber;
        // $this->CI->session->setSessionData(array('Request_info' => $a_request_values));
        // $this->data['Request_info'] = $a_request_values;
        $this->CI->session->setFlashData('Request_info', serialize($a_request_values));

      }
        return parent::getData();

    }
}