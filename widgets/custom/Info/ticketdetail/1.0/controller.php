<?php
namespace Custom\Widgets\Info;
use RightNow\Connect\v1_2 as RNCPHP;
class ticketdetail extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);
        $this->setAjaxHandlers(array(
            'get_incident_data_ajax_endpoint' => array(
                'method'    => 'handle_get_incident_data_ajax_endpoint',
                'clickstream' => 'get_incident_data_ajax_endpoint'
            )
        ));
    }
    function getData() {
        $this->data['i_id']=$this->data['attrs']['i_id'];
        return parent::getData();
    }

     /**
     * Handles the handle_get_incident_data_ajax_endpoint AJAX request
     * @param array $params Get / Post parameters
     */
    function handle_get_incident_data_ajax_endpoint($params) {
        // Perform AJAX-handling here...

        $id         = $this->data['attrs']['i_id'];
   
        $incident = RNCPHP\Incident::fetch($id);
                
        $params['incident']=$incident;
        
        echo json_encode($params);
        // FIN - Alternativa Encritpada

    }  
}