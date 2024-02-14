<?php
namespace Custom\Widgets\Info;
use RightNow\Connect\v1_3 as RNCPHP;

class asset_search extends \RightNow\Libraries\Widget\Base {
    public $contactId;
    function __construct($attrs) {
        parent::__construct($attrs);

        $this->setAjaxHandlers(array(
            'setorganization_ajax_endpoint' => array(
                'method'    => 'handle_setorganization_ajax_endpoint',
                'clickstream' => 'setorganization_ajax_endpoint'
            ),
            'asset_search_ajax_endpoint' => array(
                'method'    => 'handle_asset_search_ajax_endpoint',
                'clickstream' => 'asset_search_ajax_endpoint'
              )
            
        ));


      
        $this->CI->load->model('custom/GeneralServices');
        $this->CI->load->model('custom/Organization');
        $this->CI->load->model('custom/ws/EbsAssets');
        $this->contactId  = $this->CI->session->getProfile()->c_id->value;
        
    }
   
    function getData() {
        //$ContactData = $this->CI->GeneralServices->getOrganizationStatus($this->contactId);
        //$this->data['js']['datos']=$ContactData->Ruts;
        $CI = get_instance();
        $obj_info_contact= $CI->session->getSessionData('info_contact');
        $ContactData = $this->CI->GeneralServices->getOrganizationStatus($this->contactId);
        $organization = $this->CI->Organization->getOrganizationById($obj_info_contact['Org_id']);
        $this->data['js']['datos']=$ContactData->Ruts;
        $this->data['js']['arut']=$organization->CustomFields->c->rut;
        $this->data['js']['aName']=$organization->Name;



        $org_id=$this->CI->session->getProfile()->org_id->value;
        $this->data['js']['org_rut']=$this->CI->Organization->getOrganizationById($org_id)->CustomFields->c->rut;
        $this->data['js']['org_id']=$this->CI->Organization->getOrganizationById($org_id)->ID;

        //echo "-"  .json_encode($this->data['js']['rut_org']->CustomFields->c->rut) . "-";
        return parent::getData();

    }

    /**
     * Handles the asset_search_ajax_endpoint AJAX request
     * @param array $params Get / Post parameters
     */
    function handle_asset_search_ajax_endpoint($params) {
        // Perform AJAX-handling here...
        $this->CI->load->model('custom/GeneralServices');
        $data = json_decode($params['data'], TRUE);
        
        $valor_hh   = $data["valor_hh"];
        $rut = $data["rut"];
        //$asset = RNCPHP\Asset::first( "SerialNumber = '".$params['valor_hh']."'");
        // DEBEMOS LLAMAR A UN PROCEIMIENTO QUE SOLO BUSQUE EN EL HOLDING
        $lista=$this->CI->GeneralServices->getHoldingListHH($this->CI->session->getProfile()->c_id->value,$rut,$valor_hh);
        //$asset = RNCPHP\Asset::find( "SerialNumber = '" . $valor_hh . "'");
        //$params['new']=$params'valor_hh'];
        $this->data['js']['org_id']=$this->CI->Organization->getOrganizationById($org_id)->ID;
        //$asset->CustomFields->DOS->Direccion      = $objDireccion;
        $ObjAsset =  RNCPHP\Asset::first("SerialNumber = '" . $valor_hh . "'");
       
        
        if(!empty($lista['data']))
        {
            $params['org_name']=$lista['data'][0]['nombre_cliente'];
            $params['rut']=$lista['data'][0]['rut_cliente'];
            $params['dir']=$ObjAsset->CustomFields->DOS->Direccion->dir_envio;
            $params['ebs_comuna']=$ObjAsset->CustomFields->DOS->Direccion->ebs_comuna;
            $params['ebs_region']=$ObjAsset->CustomFields->DOS->Direccion->ebs_region;
            $params['Nombre_Equipo']=$ObjAsset->CustomFields->DOS->Product->Description;
            $params['Serie']=$ObjAsset->CustomFields->DOS->Serial_Number;
            $params['id']="1";
            
        }
        else
        {
            $params['org_name']="test" ;
            $params['rut']="";
            $params['id']="0";
            
        }
       
        echo json_encode($params);
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