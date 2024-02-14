<?php
namespace Custom\Models\hooks;
use RightNow\Connect\v1_3 as RNCPHP;

class ContactLogin extends \RightNow\Models\Base
{

    function __construct()
    {


      parent::__construct();
    }

    public function getPreBlocked(&$data)
    {
        $login   = $_POST['login'];
        $contact = RNCPHP\Contact::first("Login = '{$login}'");

        if($contact->CustomFields->c->temporal_key_vigency && $contact->CustomFields->c->temporal_key === $_POST['password'])
        {
          $this->CI->session->setSessionData(array('temporal_key' => TRUE));

          if($contact->CustomFields->c->temporal_key_vigency < time())
          {
              $message['showLink'] = false;
              $message['success']  = false;
              $message['blocked']  = false;
              $message['errors']   = array(array('externalMessage' => "Contraseña temporal expirada."));
              $message['message']  = "Solicite una nueva contraseña.";

              echo json_encode($message);

              return true;
          }
        }
        else
        {
          $this->CI->session->setSessionData(array('temporal_key' => FALSE));
        }

        if ($contact instanceof RNCPHP\Contact)
        {
          $blocked = $contact->CustomFields->c->blocked;

          //if($blocked)
          if($blocked === true)
          {
            //$meesage['message']  = "Tu usuario aún no esta autorizado, favor comunicarse con los administradores del portal web.</a>";
            $message['showLink'] = false;
            $message['success']  = false;
            $message['blocked']  = $blocked;
            $message['errors']   = array(array('externalMessage' => "Tu cuenta no está autorizada para acceder al portal."));
            $message['message']  = "Tu cuenta no está autorizada para acceder al portal.";
            echo json_encode($message);
            exit;
          }
          return true;
        }
    }

    /**
     * Establece la variables de sesion de la información del contacto
     *
     * @param object $data
     * @return void
     */
    public function setSessionVariables(&$data)
    {
      $blocked="0";
      try
      {
        // Limpiar objeto
        $obj_info_contact = new \stdClass();

        $this->CI->session->setSessionData(array('info_contact' => $obj_info_contact));
        

        $this->CI->load->model('custom/GeneralServices');
        $ContactData = $this->CI->GeneralServices->getOrganizationStatus($data['returnValue']->contactID);
    
        if($ContactData->Customer->CustomerData->Customer->tBLOQUEADO=="SI" || $ContactData->Customer->CustomerData->Customer->tbloqued=='Y')
        {
          $blocked=1;
        }
        /*
        if(count($ContactData->Address->AddressData->Address)>1)
        {
            foreach($ContactData->Address->AddressData->Address as $key => $Address)
            {

              if($Address->CREDIT_HOLD =='Y')
              {
                $blocked=1;
              }
            }
        } 
         else
         {          
          if($Address->CREDIT_HOLD=="Y")
          {
            $blocked=1;
           
          }
         }
*/
        $POPUP   = RNCPHP\Configuration::fetch( CUSTOM_CFG_POPUP_MONITOR );
        $POPUPHOUR   = RNCPHP\Configuration::fetch( CUSTOM_CFG_POPUP_HORARIO );

        if (is_object($data['returnValue']))
        {
          $contact                                   = RNCPHP\Contact::fetch($data['returnValue']->contactID);
          $obj_info_contact->ContactType             = new \stdClass();
          $obj_info_contact->ContactType->ID         = $contact->ContactType->ID;
          $obj_info_contact->ContactType->LookupName = $contact->ContactType->LookupName;
          $obj_info_contact->Org_id                  = $contact->Organization->ID;
          $obj_info_contact->ProfileType             = new \stdClass();
          if($blocked)
          {
            $obj_info_contact->ProfileType->ID         = 1; // TODO: Mejorar valor por defecto
          }
          else
          {
          $obj_info_contact->ProfileType->ID         = ($contact->CustomFields->PROF->ProfileType->ID)?$contact->CustomFields->PROF->ProfileType->ID:3; // TODO: Mejorar valor por defecto
          }
          $obj_info_contact->ProfileType->LookupName = $contact->CustomFields->PROF->ProfileType->Name;
          $obj_info_contact->Block=$blocked;
          // 23/09/2020   para registrar el bloqueo
          $obj_info_contact->Blocked=$blocked;
          
          $obj_info_contact->tKAM       = bin2hex($ContactData->Customer->CustomerData->Customer->tKAM);
          
          $obj_info_contact->tKAM_EMAIL = bin2hex($ContactData->Customer->CustomerData->Customer->tKAM_EMAIL);
          
          $obj_info_contact->tPM        = bin2hex($ContactData->Customer->CustomerData->Customer->tPM);
          
          $obj_info_contact->tPM_EMAIL  = bin2hex($ContactData->Customer->CustomerData->Customer->tPM_EMAIL);
          


          $obj_info_contact->Inicio=$POPUP->Value;
          $obj_info_contact->PopHour=$POPUPHOUR->Value;
          
          
       

         // Contacto Personalizado  
          if ($obj_info_contact->ContactType->ID == 7)
          {
            if ($contact->CustomFields->c->json_custom_profile != null)
            {
              //echo $contact->CustomFields->c->json_custom_profile;
              //$json_p = json_decode($contact->CustomFields->c->json_custom_profile, TRUE);
              //print_r($json_p);
              //exit();
              // $json_p = json_decode($contact->CustomFields->c->json_custom_profile), TRUE);
              $json_p = json_decode($contact->CustomFields->c->json_custom_profile, TRUE);
              $obj_info_contact->json_profile = $json_p;
            }
            else
              $obj_info_contact->json_profile = null;
          }
          else
          {
            $profileType = $obj_info_contact->ProfileType->ID;
            $profiling   = $this->getProfilingModule($profileType);
            if ($profiling === false)
            {
              $obj_info_contact->json_profile = null;
            }
            else
            {
              $json_p = json_decode(json_encode($profiling), true);
              $obj_info_contact->json_profile = $json_p;
            }
           }

          //Asignación de información a la variable de sesión
          $this->CI->session->setSessionData(array('info_contact' => $obj_info_contact));          
        }
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
          // echo "Codigo : ".$err->getCode()." ".$err->getMessage();
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
