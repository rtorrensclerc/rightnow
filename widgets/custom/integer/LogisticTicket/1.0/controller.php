<?php
namespace Custom\Widgets\integer;
use RightNow\Connect\v1_2 as RNCPHP;
class LogisticTicket extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData() {
      $this->CI->load->model('custom/ws/TicketModel');
      $result = $this->CI->TicketModel->getIncident($this->data['attrs']['ref_no']);

      if ($result == false)
      {
        $this->data['error'] = $this->CI->TicketModel->getLastError();
        // $this->CI->session->setSessionData(array('Request_info' => ''));
        // $this->data['Request_info'] = '';
        $this->CI->session->setFlashData('Request_info', serialize(''));
      }
      else {

      $this->data['order']                 = $result->order;

      $a_request_values['idStatus']        = $result->order->StatusWithType->Status->ID;
      $a_request_values['idHH']            = $result->CustomFields->c->id_hh;
      $a_request_values['id']              = $result->order->ID;
      $a_request_values['ref_no']          = $result->order->ReferenceNumber;

      $this->CI->session->setFlashData('Request_info', serialize($a_request_values));


      }
      return parent::getData();

    }
}
