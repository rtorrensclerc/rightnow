<?php
namespace Custom\Widgets\menu;
use RightNow\Connect\v1_3 as RNCPHP;

class ChangeOrganization2 extends \RightNow\Libraries\Widget\Base {
    public $contactId;
    function __construct($attrs) {
        parent::__construct($attrs);

        $this->setAjaxHandlers(array(
            'setorganization_ajax_endpoint' => array(
                'method'      => 'handle_setorganization_ajax_endpoint',
                'clickstream' => 'setorganization_ajax_endpoint',
            ),
        ));
        $this->CI->load->model('custom/GeneralServices');
        $this->CI->load->model('custom/Organization');
        $this->contactId  = $this->CI->session->getProfile()->c_id->value;
    }

    function getData() {
        $CI = get_instance();
        $obj_info_contact= $CI->session->getSessionData('info_contact');
        $ContactData = $this->CI->GeneralServices->getOrganizationStatus($this->contactId);
        $this->data['js']['datos']=$ContactData->Ruts;
        /** Obtiene la organización Actual */
        $organization = $this->CI->Organization->getOrganizationById($obj_info_contact['Org_id']);

        $this->data['js']['arut']=$organization->CustomFields->c->rut;
        $this->data['js']['aName']=$organization->Name;


        return parent::getData();

    }

    /**
     * Handles the setorganization_ajax_endpoint AJAX request
     * @param array $params Get / Post parameters
     */
    function handle_setorganization_ajax_endpoint($params) {
        // Perform AJAX-handling here...
         echo "response";
    }
}