<?php
namespace Custom\Widgets\Info;
use RightNow\Connect\v1_2 as RNCPHP;
class CustomerInfo extends \RightNow\Libraries\Widget\Base {
    public $contactId;
    public $blocked='N';
    function __construct($attrs) {
        parent::__construct($attrs);
        
        $this->CI->load->model('custom/GeneralServices');
        $this->data['grupo']=1;
       
        $this->contactId  = $this->CI->session->getProfile()->c_id->value;

    }

    function getData() {
       
        $this->contactId  = $this->CI->session->getProfile()->c_id->value;
        $this->data['js']['main']=$this->CI->session->getProfile();
        $ContactData = $this->CI->GeneralServices->getOrganizationStatus($this->contactId);
        //$this->CI->session->setSessionData(array('info_block' => $a_list_hh));
        
        $this->setSessionVariables($this->contactId);

        $this->data['js']['datos']=$ContactData;
       //$this->CI->session->setSessionData(array('ruts' => $ContactData->Ruts));

        return parent::getData();

    }


    public function setSessionVariables($contactId)
    {
      try
      {
        // Limpiar objeto
        $CI = get_instance();
        $obj_info_contact= $CI->session->getSessionData('info_contact');
        
        
        $this->CI->session->setSessionData(array('info_contact' => $obj_info_contact));

      
          $contact= RNCPHP\Contact::fetch($contactId);

         // print_r($obj_info_contact);
          
            
        

          $obj_info_contact['ProfileType']['LookupName']=$contact->CustomFields->PROF->ProfileType->Name;
          
          $obj_info_contact['Block']=0;
          $obj_info_contact['Blocked']=0;


        
           // print_r($obj_info_contact['json_profile']);
          //Asignación de información a la variable de sesión
          $this->CI->session->setSessionData(array('info_contact' => $obj_info_contact));          
            
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
          echo "Codigo : ".$err->getCode()." ".$err->getMessage();
          return false;
      }
    }

    private function getProfilingModule($profile_type_id)
    {
      $this->CI->load->model('custom/Profiling');

      $profiling_table = $this->CI->Profiling->getProfilingByType($profile_type_id);
      
      if (is_array($profiling_table))
      {
        $a_profiling = array();
        foreach ( $profiling_table as $profile)
        {
          $a_temp                     = array();
          $a_temp['id']               = $profile->ID;
          $a_temp['module']['id']     = $profile->Module->ID;
          $a_temp['module']['name']   = $profile->Module->Name;
          $a_temp['access']           = $profile->Access;
          $a_profiling['modules'][]   = $a_temp;
        }
        return $a_profiling;
      }
      else
      {
        return false;
      }
    }
}