<?php
namespace Custom\Widgets\reports;
class OrgHistory extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);        $this->setAjaxHandlers(array(            'default_ajax_endpoint' => array(
                'method'      => 'handle_getTickets_ajax_endpoint',
                'clickstream' => 'custom_action',
            ),
        ));
        $this->CI->load->model('custom/Contact');
        $this->CI->load->model('custom/IncidentGeneral'); // Modelo
    }
    function getData() {
        $report_id = $this->data['attrs']['report_id'];
        $this->data['js']['total_pages']                = "texto";
        $this->data['js']['report_id']= $report_id;


        $c_id = $this->CI->session->getProfile()->c_id->value;
        $user = $this->CI->Contact->getContactById($c_id);
       
            $organization = $user->Organization;
            // var_dump($organization->CustomFields->c->rut);
            $this->data['js']['rut'] = $organization->CustomFields->c->rut;
        
            $this->data['js']['incidents'] = $this->CI->IncidentGeneral->FindIncident($organization->CustomFields->c->rut);

            
        return parent::getData();
    }    /**
     * Handles the default_ajax_endpoint AJAX request
     * @param array $params Get / Post parameters
     */
    function handle_getTickets_ajax_endpoint($params) {
        // Perform AJAX-handling here...
        // echo response
        $c_id = $this->CI->session->getProfile()->c_id->value;
        $user = $this->CI->Contact->getContactById($c_id);
       
            $organization = $user->Organization;
            // var_dump($organization->CustomFields->c->rut);
            $params['incidents'] = $this->CI->IncidentGeneral->FindIncident($organization->CustomFields->c->rut);
            
            echo json_encode($params);
    }
}