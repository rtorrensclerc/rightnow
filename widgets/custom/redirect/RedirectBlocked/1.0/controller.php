<?php
namespace Custom\Widgets\redirect;

class RedirectBlocked extends \RightNow\Libraries\Widget\Base
{
  public $contactId;

  function __construct($attrs)
  {
      parent::__construct($attrs);

      $this->CI->load->model("custom/ws/DatosHH");
      $this->CI->load->model("custom/Supplier");
      $this->CI->load->model('Contact');
      $this->CI->load->helper('utils_helper');

      $this->contactId  = $this->CI->session->getProfile()->c_id->value;
  }

  function getData() {
    // Validar que contacto esta autorizado
    $contact = $this->CI->Contact->get($this->contactId);

    if ($contact->result->CustomFields->c->blocked === true)
    {
      $currentURL = \RightNow\Utils\Url::getOriginalUrl(false);
      $this->CI->Contact->doLogout($currentURL);
      header('Location: '.$currentURL."/app/utils/login_form");
      exit;
    }

    // Validaciones de módulos
   
    if($this->data['attrs']['module_id'])
    {
      $arr_modules_ids = explode(',', $this->data['attrs']['module_id']);
      $valid           = false;

      for ($i=0; $i < sizeof($arr_modules_ids); $i++) { 
        if(isEnabled(intval($arr_modules_ids[$i])))
        {
          $valid = true;
        }
      }

      if(!$valid)
      {
        if($this->data['attrs']['error_page'])
        {
          header('Location: ' . $this->data['attrs']['error_page']);
        }
        else
        {
          header('Location: /app/');
        }
      }
    }
  }
}
