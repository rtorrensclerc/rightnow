<?php
namespace Custom\Widgets\administration;

class UserAdministration extends \RightNow\Libraries\Widget\Base
{
  /**
   * constructor del widget
   *
   * @param array $attrs
   */
  function __construct($attrs)
  {
    parent::__construct($attrs);

    // Iniciación de métodos AJAX
    $this->setAjaxHandlers(array(
      'saveForm_ajax_endpoint' => array(
        'method'      => 'handle_saveForm_ajax_endpoint',
        'clickstream' => 'saveFormUserAdmin_action',
      ),
    ));

    // Carga de recusos
    $this->CI->load->model("custom/Contact");
    $this->CI->load->helper("utils_helper");
  }

  /**
   * Determina si los contactos tienen la misma organización
   *
   * @param integer $c_id_1 ID del contacto
   * @param integer $c_id_2 ID del contacto
   * 
   * @return boolean
   */
  function validateOrganization($c_id_1, $c_id_2)
  {
    $contact_1 = $this->CI->Contact->get($c_id_1);
    $org_contact_1 = $contact_1->Organization->ID;

    $contact_2 = $this->CI->Contact->get($c_id_2);
    $org_contact_2 = $contact_2->Organization->ID;

    return ($org_contact_1 === $org_contact_2);
  }

  /**
   * Método principal del widget
   *
   * @return void
   */
  function getData()
  {
    $user_id = getUrlParm('u_id');

    $this->data['js']['list']['profiles'] = $this->CI->Contact->getProfileTypes();
    
    // Valores en caso de que esta sea una actualización
    $this->setValues($user_id);

    return parent::getData();
  }

  /**
   * Establece los valores iniciales
   *
   * @param integer $user_id
   * @return void
   */
  private function setValues($user_id)
  {
    if (!empty($user_id))
    {
      $contact = $this->CI->Contact->getContactById($user_id);

      // Validación de organización
      $CI = get_instance();

      if(!$this->validateOrganization($user_id, $CI->session->getProfile()->c_id->value))
      {
        header('Location: /');
      }

      if ($contact !== false)
      {
        $this->data['js']['user']['name']    = $contact->Name->First;
        $this->data['js']['user']['last']    = $contact->Name->Last;
        $this->data['js']['user']['rut']     = $contact->CustomFields->c->rut;
        $this->data['js']['user']['email']   = $contact->Login;
        $this->data['js']['user']['phone']   = $contact->Phones[0]->Number;
        $this->data['js']['user']['profile'] = $contact->CustomFields->PROF->ProfileType->ID;
        $this->data['js']['user']['blocked'] = $contact->CustomFields->c->blocked;
      }
      else
      {
        $this->data['js']['error']['code']    = true;
        $this->data['js']['error']['message'] = 'Error al obtener datos: ' . $this->CI->Contact->getLastError();
      }
    }
  }

  /**
   * Handles the default_ajax_endpoint AJAX request
   * @param array $params Get / Post parameters
   */
  function handle_saveForm_ajax_endpoint($params)
  {
    header('Content-Type: application/json');

    $data = json_decode($params['data']);

    $a_user['u_id']      = $data->id;
    $a_user['name']      = $data->user_name;
    $a_user['last_name'] = $data->user_last;
    $a_user['rut']       = $data->user_rut;
    $a_user['email']     = $data->user_email;
    $a_user['phone']     = $data->user_phone;

    $validate_email =  \RightNow\Utils\Text::isValidEmailAddress($a_user['email']);

    if ($validate_email === false)
    {
      $response      = new \stdClass();
      $response->success = false;
      $response->message = 'El correo electrónico ingresado no tiene un formato correcto, favor verifique e intente nuevamente.';

      echo json_encode($response);

      return false;
    }

    if ($data->user_disabled !== null)
    {
      $a_user['blocked'] = boolval($data->user_disabled);
    }

    $a_user['profile'] = $data->user_profile;

    if (!is_numeric($a_user['u_id']) or $a_user['u_id'] == 0 or  $a_user['u_id'] === null or $a_user['u_id'] === -1)
    {
      $c_id = $this->CI->Contact->getContactByLogin($a_user['email']);

      if ($c_id !== false)
      {
        $response          = new \stdClass();
        $response->success = false;
        $response->message = 'Ya existe un usuario registrado con ese "Correo Electrónico" o "Nombre de Usuario".';

        echo json_encode($response);

        return false;
      }

      $c_id = $this->CI->Contact->getContactByEmail($a_user['email']);

      if ($c_id !== false)
      {
        $response          = new \stdClass();
        $response->success = false;
        $response->message = 'Ya existe un usuario registrado con el "Correo Electrónico" ingresado.';

        echo json_encode($response);

        return false;
      }
    }

    // Hereda la organización del usuario conectado
    $CI               = get_instance();
    $obj_info_contact = $CI->session->getSessionData('info_contact');

    $a_user['organization_id'] = $obj_info_contact['Org_id'];

    // Crea / Actualiza el contacto
    $result = $this->CI->Contact->setUser($a_user);

    // Formando estructura de respuesta
    $response           = new \stdClass();
    $response->success  = ($result)?true:false;
    $response->response = $result;

    if ($result === true)
    {
      $response->message = "Creación / Actualización de usuario realizado con éxito.";
    }
    else
    {
      $response->message = 'Error en procedimiento: '.$this->CI->Contact->getLastError();
    }

    // Exponiendo la respuesta
    echo json_encode($response);
  }
}
