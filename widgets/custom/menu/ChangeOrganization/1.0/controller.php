<?php
namespace Custom\Widgets\menu;
use RightNow\Connect\v1_3 as RNCPHP;

class ChangeOrganization extends \RightNow\Libraries\Widget\Base {
    public $contactId;
    function __construct($attrs) {
        parent::__construct($attrs);

        $this->setAjaxHandlers(array(
            'setorganization_ajax_endpoint' => array(
              'method'    => 'handle_setorganization_ajax_endpoint',
              'clickstream' => 'custom_action',
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
   * Set organization
   *
   * @param array $params Get / Post parameters
   */
  function handle_setorganization_ajax_endpoint($params)
  {
    //$data=json_decode($params);
    $CI = get_instance();
    $data = json_decode($params['data'], TRUE);
    $rut   = $data["rut"];

    $obj_info_contact= $CI->session->getSessionData('info_contact');
    $Organization = $this->CI->Organization->getOrganizationByRut($rut);
    $obj_info_contact['Org_id'] = $Organization->ID;
    $this->CI->session->setSessionData(array('info_contact' => $obj_info_contact));          


    echo json_encode($obj_info_contact['Org_id']);
    
  }
}