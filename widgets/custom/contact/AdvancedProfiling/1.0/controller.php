<?php
namespace Custom\Widgets\contact;

/**
 * Clase del controlador del widget
 */
class AdvancedProfiling extends \RightNow\Libraries\Widget\Base
{
  // Declaración de variables
  public $c_id               = null;
  public $profiling          = null;
  public $profiles           = null;
  public $contact            = null;
  public $contact_type       = null;
  public $profile_type       = null;
  public $json_custom_profile = null;
  public $modules             = null;
  

  /**
   * Constructor del controlador
   *
   * @param array $attrs
   */
  function __construct($attrs)
  {
    parent::__construct($attrs);

    // Manejadores AJAX
    $this->setAjaxHandlers(array(
      'updateProfiling_ajax_endpoint' => array(
        'method'    => 'handle_updateProfiling_ajax_endpoint',
        'clickstream' => 'updateProfiling_action',
      )
    ));

    $this->CI->load->model('custom/Contact');
    $this->CI->load->model('custom/Profiling');
    $this->CI->load->helper('utils_helper');
  }

  /**
   * Método inicial
   *
   * @return void
   */
  function getData()
  {
    $this->initialize();
    
    return parent::getData();
  }

  /**
   * Método que inicial
   *
   * @return void
   */
  function initialize()
  {
    // Se obtiene la lista de tipos de perfiles
    $menuTypes    = $this->CI->Contact->getProfileTypes();  // Tipos de Perfil
    $modulesTypes = $this->CI->Profiling->getModules();     // Tipos de Modulo

    // Variables
    $this->c_id         = getUrlParm('c_id');
    $this->contact_type = getUrlParm('c_type');
    $this->profiles     = json_decode(json_encode($menuTypes), FALSE); // Convertir a OBj
    $this->modules      = json_decode(json_encode($modulesTypes), FALSE); // Convertir a OBj
    // TODO: Generar dinámicamente.
    $this->contact      = $this->CI->Contact->getContactById($this->c_id);

    if (!empty($this->contact))
    {
      $this->profile_type = (int) $this->contact->CustomFields->PROF->ProfileType->ID;

      if ($this->contact_type != 7)
      {
        $this->profiling               = $this->getProfilingModule($this->profile_type);
        $this->data['js']['profiling'] = json_decode(json_encode($this->profiling), FALSE);
        $this->data['js']['is_custom'] = false;
      }
      else
      {
        if ($this->contact->CustomFields->c->json_custom_profile !== null)
        {
          $this->data['js']['profiling'] = json_decode($this->contact->CustomFields->c->json_custom_profile); 
          
        }
        else
        {
          $prof_custom                   = $this->getProfileModuleCustom(); 
          $this->data['js']['profiling'] = json_decode(json_encode($prof_custom));
        }

        $this->data['js']['is_custom'] = true;
      }

      $this->data['js']['list']['profiles'] = $this->profiles;
      $this->data['js']['list']['modules']  = $this->modules;
      $this->data['js']['profile_type']     = $this->profile_type;
    }
  }

  /**
   * Petición AJAX para la actualización del perfilamiento
   * 
   * TODO: Implementar el método `setProfiling`
   *
   * @param array $params
   * @return void
   */
  function handle_updateProfiling_ajax_endpoint($params)
  {
    header('Content-Type: application/json');

    // Decodifica las variables del parámetro
    $data = json_decode($params['data']);

    // Obtiene el contacto
    $c_id            = (int)$data->c_id;
    //Obtiene el tipo de contacto
    $contact_type    = (int)$data->c_type;  

    // Obtiene el nuevo tipo de perfil
    $profile_type_id = (int)$data->profile_type;

    // Obtiene JSON Custom Profile
    $json_custom_profile     = (empty($data->json_custom_profile))?null:json_encode($data->json_custom_profile);

    // Perfilamiento tradicional
    if ($contact_type !== 7)
    {
      $resp_contact   = $this->CI->Contact->setProfiling($c_id, $profile_type_id);
    }
    else //perfilamiento personalizado
    {
      $resp_contact   = $this->CI->Contact->setProfiling($c_id, null, $json_custom_profile);
    }

    // Manejo de excepción en caso de error en la obtención del contacto
    if ($resp_contact  === false)
    {
      $response          = new \stdClass();
      $response->success = false;
      $response->message = 'Error '.$this->CI->Contact->getLastError();
      echo json_encode($response);
      return;
    }

    // Estructura de respuesta
    $response           = new \stdClass();
    $response->success  = true;
    $response->message  = 'Se actualizo la información del contacto.';
    //$this->resetDataBySelected($type_id);
    // Exponiendo la respuesta
    echo json_encode($response);
  }


  private function resetDataBySelected($type_id)
  {
    $this->profile_type                        = $type_id;
    $profiling                                 = $this->getProfilingModule($this->profile_type);
    $this->data['js']['profile_type']          = $this->profile_type;
    $this->data['js']['profiling']             = json_decode(json_encode($profiling), FALSE);
  }

  private function getProfilingModule($profile_type_id)
  {
  
    $profling_table = $this->CI->Profiling->getProfilingByType($profile_type_id);
    if (empty($profling_table))
    {
      echo $this->CI->Profiling->getLastError();
      return false;
    }
    else
    { $a_profiling["modules"] = array();
      foreach ( $profling_table as $profile)
      {
        $a_temp['id']             = $profile->ID;
        $a_temp['module']["id"]   = $profile->Module->ID;
        $a_temp['module']["name"] = $profile->Module->Name;
        $a_temp['access']         = $profile->Access;
        $a_profiling["modules"][]   = $a_temp;
      }
      return $a_profiling;
    }
  }

  private function getProfileModuleCustom()
  {
    $a_profiling["modules"] = array();

    foreach ($this->modules as $module)
    {
      $a_temp['id']             = null;
      $a_temp['module']["id"]   = $module->ID;
      $a_temp['module']["name"] = $module->name;
      $a_temp['access']         = false;
      $a_profiling["modules"][]   = $a_temp;
    }
    return $a_profiling;
  }

}
