<?php
namespace Custom\Widgets\login;

class LoginResetPassword extends \RightNow\Libraries\Widget\Base {

  //Varibale que almacena los errores surgidos
  public $msgError = "";

  /**
   * Undocumented function
   *
   * @param [type] $attrs
   */
  function __construct($attrs) {
    parent::__construct($attrs);

    $this->setAjaxHandlers(array(
      'reset_password_ajax_endpoint' => array(
        'method' => 'sendEmailCredentials',
        'clickstream' => 'emailCredentials'
      ),
    ));

    $this->CI->load->model('custom/Contact');
  }

  /**
   * AJAX endpoint to send an email to a contact containing either their username, or a password reset notification.
   *
   * If $parameters['requestType'] is 'emailPassword' then $parameters['value'] is the contact's username and a password reset will be performed.
   * Otherwise 'value' is the contact's email address and their username will be emailed.
   *
   * @param array|null $parameters An array of key/value pairs.
   */
  function sendEmailCredentials($param) {
    \RightNow\Libraries\AbuseDetection::check();
    $c_id    = $param['c_id'];
    if(!$c_id) return false;

    $contact = $this->CI->Contact->getContact($c_id);
    
    if($contact) {
      $email = $contact->Emails[0]->Address;

      if($email) {
        $this->renderJSON($this->CI->model('Contact')->sendResetPasswordEmail($email)->result);
      } else {
        $this->renderJSON('El contacto no tiene un correo electrónico asociado.');
      }
    } else {
      $this->renderJSON('El contacto no existe.');
    }

  }
}
