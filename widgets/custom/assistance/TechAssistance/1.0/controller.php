<?php

namespace Custom\Widgets\assistance;

use RightNow\Connect\v1_3 as RNCPHP;

class TechAssistance extends \RightNow\Libraries\Widget\Base
{

  public $contactId;
  private $errorMessage;

  function __construct($attrs)
  {
    parent::__construct($attrs);

    $this->setAjaxHandlers(array(
      'getHHDataSelected_ajax_endpoint' => array(
        'method'    => 'handle_getHHDataSelected_ajax_endpoint',
        'clickstream' => 'getHHDataSelected_ajax_endpoint',
      ),
      'requestIncident_ajax_endpoint' => array(
        'method'    => 'handle_requestIncident_ajax_endpoint',
        'clickstream' => 'requestIncident_ajax_endpoint',
      ),
      'getInfoIncident_ajax_endpoint' => array(
        'method'    => 'handle_getInfoIncident_ajax_endpoint',
        'clickstream' => 'getInfoIncident_ajax_endpoint',
      ),
      'cancelIncident_ajax_endpoint' => array(
        'method'    => 'handle_cancelIncident_ajax_endpoint',
        'clickstream' => 'cancelIncident_ajax_endpoint',
      )
    ));

    $this->CI->load->model("custom/ws/DatosHH");
    $this->CI->load->model("custom/TechAssistance");
    $this->CI->load->model('Contact');
    $this->CI->load->model('custom/GeneralServices');
    $this->contactId  = $this->CI->session->getProfile()->c_id->value;
  }

  function getData()
  {
    // validar que contacto esta autorizado
    $contact = $this->CI->Contact->get($this->contactId);
    
    $this->data['contact_name']  = $contact->Name->First . ' ' . $contact->Name->Last;
    $this->data['contact_phone'] = $contact->Phones[0]->Number;

    if($contact->result->CustomFields->c->blocked === true) 
    {
      $currentURL = \RightNow\Utils\Url::getOriginalUrl(false);
      $this->CI->Contact->doLogout($currentURL);
      header('Location: ' . $currentURL . "/app/utils/login_form");
      exit;
    }

    // Solo lecutra
    $read_only = $this->data['attrs']['read_only'];

    if ($read_only === TRUE) 
    {
      $incidentId                = getUrlParm('i_id');
      $incident_data             = $this->CI->TechAssistance->getInfoTicket($incidentId);

      $this->data['hh_selector']              = $incident_data->id_hh;
      $this->data['marca_hh']                 = $incident_data->brand_hh;
      $this->data['model_hh']                 = $incident_data->model_hh;
      $this->data['hh_counter_bw']            = $incident_data->cont1_hh;
      $this->data['hh_counter_color']         = $incident_data->cont2_hh;
      $this->data['dispatch_address']         = $incident_data->address->name;
      $this->data['dispatch_address_correct'] = 1;
      $this->data['suggested_type']           = $incident_data->suggested_type;
      $this->data['codigo_error']             = $incident_data->codigo_error;
      $this->data['equipo_detenido_cliente']  = $incident_data->equipo_detenido_cliente;
      $this->data['items']                    = $incident_data->items;
    }

    // TODO: Obtener Datos
    $this->setMenulist();
    return parent::getData();
  }


  /**
   * Obtiene las listas del formulario
   */
  private function setMenulist()
  {
    
    $c_list = RNCPHP\ConnectAPI::getNamedValues("RightNow\\Connect\\v1_3\\Incident.CustomFields.c.tipificacion_sugerida");

    if($c_list)
    {
      $suggested_type = $this->transformListToMenu($c_list);
      $this->data['js']['list']['suggested_type'] = $suggested_type;
    }
    else
    {
      $this->data['js']['list']['suggested_type'] = [];
    }
    

    // HH según organización
    $a_list_hh = $this->CI->GeneralServices->getListHH($this->contactId);
    $a_status_rut = $this->CI->GeneralServices->getOrganizationStatus($this->contactId);
    
    if($a_list_hh === FALSE)
    {
      if($this->CI->GeneralServices->getLastErrorCode() === 2)
      {
        echo "<strong>Ha ocurrido un error obteniendo las HH. Si el problema persiste comuníquese con el Call Center (600 600 1001) de Dimacofi.</strong>";
      }
    }
    else
    {
      $hh_list = $this->hhListToMenu($a_list_hh["data"]);
      
      if($hh_list)
      {
        $this->data["js"]["list"]["list_hh"] = $hh_list;
      }
      else
      {
        echo "No se pudo obtener las hh";
      }

      // Filtro de marcas
      $brand_filter = $this->hhListToMenu($a_list_hh["data"], TRUE);

      if($brand_filter)
      {
        $this->data["js"]["list"]["brands"] = $brand_filter;
      }
      else
      {
        echo "No se pudo obtener las marcas";
      }

      // Marcas y sus hh
      if($brand_filter)
      {
        $hhByBrand = $this->getHHByBrand($a_list_hh["data"], $brand_filter);

        if($hhByBrand)
        {
          $this->data["js"]["list"]["brands_hh"] = $hhByBrand;
        }
        else
        {
          echo "No se pudieron obtener las HH por marca";
        }
      }
    }
    // Dirección
    $result = $this->CI->TechAssistance->getListDir($this->contactId);

    
    $this->data["js"]["list"]["list_dir"] = array();
    $this->data["js"]["list"]["HHbloqued"]  = $a_status_rut;
    if ($result === FALSE)
    {
      echo 'Error: ' . $this->CI->TechAssistance->getLastError();
    } 
    else 
    {
      foreach ($result as $dir) 
      {
        $a_tempDir ["name"]                     = $dir->dir_envio. '-' . $dir->ebs_comuna . '-' . $dir->ebs_region ;;
        $a_tempDir ["ID"]                       = $dir->d_id;
        $this->data["js"]["list"]["list_dir"][] = $a_tempDir;
      }
    }
  }

  /**
   * ¿?
   *
   * @param ¿? $a_data
   * @param ¿? $a_brands
   * @return void
   */
  private function getHHByBrand($a_data, $a_brands)
  {
    $a_menu = array();

    foreach($a_brands as $brand)
    {
      foreach($a_data as $data)
      {
        if($brand["ID"] == $data["marca"])
        {
          $tmp = array(
            "ID" => $data["hh"],
            "name" => $data["hh"]
          );
          $a_menu[$brand["ID"]][] = $tmp;
        }
      }
    }

    return $a_menu;
  }

  /**
   * ¿?
   *
   * @param ¿? $a_data
   * @param boolean $is_filter
   * @return void
   */
  private function hhListToMenu($a_data, $is_filter = FALSE)
  {
   
    $a_menu = array();
    foreach($a_data as $data)
    {
      if($is_filter === FALSE)
      {
        $tmp = array(
          "name" => $data["hh"] . " - " . $data["nombre"],
          "ID" => $data["hh"],
          "trx_id_erp" => $data["trx_id_erp"]
        );
        array_push($a_menu, $tmp);
        
      }
      elseif($is_filter === TRUE)
      {
        if(!empty($a_menu))
        {
          $exists = FALSE;
          foreach($a_menu as $men)
          {
            if($men["ID"] == $data["marca"])
            {
              $exists = TRUE;
              break;
            }
          }
          if($exists === FALSE)
          {
            $tmp = array(
              "name" => $data["marca"],
              "ID" => $data["marca"]
            );
            array_push($a_menu, $tmp);
          }
        }
        else
        {
          $tmp = array(
            "name" => $data["marca"],
            "ID" => $data["marca"]
          );
          array_push($a_menu, $tmp);
        }
      }
    }

    return $a_menu;
  }

  private function simpleArrayToList($arr)
  {
    if(!empty($arr))
    {
      $list = array();
      foreach($arr as $a)
      {
        $temp = array(
          "ID"   => $a,
          "name" => $a
        );
        array_push($list, $temp);
      }
      return $list;
    }
    else
    {
      return FALSE;
    }
  }


  /**
   * Transforma listas a menus
   */
  private function transformListToMenu($a_menuRN)
  {
    $a_menu = array();
    foreach ($a_menuRN as $key => $menu) 
    {
      $a_tempMenu["name"] = $menu->LookupName;
      $a_tempMenu["ID"]   = $menu->ID;
      $a_menu[]       = $a_tempMenu;
    }
    return $a_menu;
  }

  /**
   * Obtiene la información del HH
   *
   * @param array $params Get / Post parameters
   */
  function handle_getHHDataSelected_ajax_endpoint($params)
  {
    header('Content-Type: application/json');

    $data = json_decode($params['data'], TRUE);
    $hh   = $data["hh"];
    $trx_id_erp         = $data['trx_id_erp'];
    // Obtiene la información del HH
    $result = $this->CI->DatosHH->getDatosHHInsumos($hh);

    // Formando estructura de respuesta
    $response          = new \stdClass();
    $response->success = ($result) ? TRUE : FALSE;

    if ($result === FALSE) 
    {
      $response->response = FALSE;
      $response->message  = $this->CI->DatosHH->getLastError();
    } 
    else 
    {
      $a_temp_json_result = json_decode($result, TRUE);

      $a_temp_json_result["respuesta"]["lastCounters"] = $this->getQuantityCounters($a_temp_json_result["respuesta"]["Contadores"]);
      $a_temp_json_result['trx_id_erp']= $trx_id_erp;
      $json               = json_encode($a_temp_json_result);
      $response->response = $json;
      $response->message  = "Información HH obtenida con éxito";
    }

    // Exponiendo la respuesta
    echo json_encode($response);
  }

  /**
   * ¿?
   *
   * @param ¿? $a_counters
   * @return void
   */
  private function getQuantityCounters($a_counters)
  {
    $a_quantityCounters = array(
      "copia_bn"        => 0,
      "copia_color"     => 0,
      "scanner_bn"      => 0,
      "scanner_color"   => 0,
      "impresion_bn"    => 0,
      "impresion_color" => 0
    );

    if(count($a_counters) > 0)
    {
      foreach($a_counters as $counter)
      {
        switch ($counter["Tipo"]) 
        {
          case 'COPIA B\/N':
            $a_quantityCounters["copia_bn"] = (int) $counter["Valor"];
            break;
          case 'COPIA COLOR':
            $a_quantityCounters["copia_color"] = (int) $counter["Valor"];
            break;
          case 'SCANNER B\/N':
            $a_quantityCounters["scanner_bn"] = (int) $counter["Valor"];
            break;
          case 'SCANNER COLOR':
            $a_quantityCounters["scanner_color"] = (int) $counter["Valor"];
            break;
          case 'IMPRESION B\/N':
            $a_quantityCounters["impresion_bn"] = (int) $counter["Valor"];
            break;
          case 'IMPRESION COLOR':
            $a_quantityCounters["impresion_color"] = (int) $counter["Valor"];
            break;
          default:
            $a_quantityCounters["copia_bn"] = (int) $counter["Valor"];
            break;
        }
      }
    }

    return $a_quantityCounters;
  }

  /**
   * Solicita los insumos enviando el formulario
   *
   * @param array $params Get / Post parameters
   */
  function handle_requestIncident_ajax_endpoint($params)
  {
    header('Content-Type: application/json');

    $data = json_decode($params['data'], TRUE);

    // Datos de Contadores
    $a_Infohh['contador_bn']    = $data["info_form"]["bw_counter"];
    $a_Infohh['contador_color'] = $data["info_form"]["color_counter"];

    
    // Datos de HH
    $a_Infohh['hh']                = $data["info_hh"]["respuesta"]["ID_HH"];
    $a_Infohh['serie']             = $data["info_hh"]["respuesta"]["Serie"];
    $a_Infohh['marca']             = $data["info_hh"]["respuesta"]["Marca"];
    $a_Infohh['modelo']            = $data["info_hh"]["respuesta"]["Modelo"];
    $a_Infohh['convenio']          = $data["info_hh"]["respuesta"]["Convenio"];      
    $a_Infohh["hh_sla"]            = $data["info_hh"]["respuesta"]['SLA'];
    $a_Infohh["hh_rsn"]            = $data["info_hh"]["respuesta"]['RSN'];
    $a_Infohh["a_hh_contadores"]   = $data["info_hh"]["respuesta"]['Contadores'];
    $a_Infohh["a_hh_direccion_id"] = $data["info_hh"]["respuesta"]['Direccion'];
    $a_Infohh["hh_tipo_contrato"]  = $data["info_hh"]["respuesta"]['TipoContrato'];
    $a_Infohh["numero_delfos"]     = $data["info_hh"]["respuesta"]['delfos'];
    $a_Infohh["Rut"]               = $data["info_hh"]["respuesta"]['Rut'];
    $a_Infohh["is_wrong_address"]  = (bool) $data["info_form"]["direccion_incorrecta"];
    $a_Infohh["correct_address"]   = ($a_Infohh["is_wrong_address"] === TRUE) ? $data["info_form"]["direccion_correcta"] : $a_Infohh["a_hh_direccion_id"]["Direccion"];
    $a_Infohh["suggested_type"]    = $data["info_form"]["suggested_type"];
    $a_Infohh['trx_id_erp']        = $data["info_hh"]["trx_id_erp"];
    
    /* echo '<pre>';
    print_r($data);
    echo '</pre>';
    exit(); */
    // Datos de contacto
    $a_contactInfo['name']                    = $data["info_form"]["contact_name"];
    $a_contactInfo['phone']                   = $data["info_form"]["contact_phone"];
    $a_contactInfo['email']                   = $data["info_form"]["contact_email"];
    $a_contactInfo['codigo_error']            = $data["info_form"]["codigo_error"];
    $a_contactInfo['equipo_detenido_cliente'] = $data["info_form"]["equipo_detenido_cliente"];

    // Dirección
    $dirId = $a_Infohh["a_hh_direccion_id"]["ID_direccion"];

    // Detalle del requerimiento
    $a_contactInfo['detail'] = $data["info_form"]["contact_detail"];

    // Crea el incidente
    $result = $this->CI->TechAssistance->createTechAssistanceTicket($this->contactId, $a_Infohh, $a_contactInfo, $dirId);

    $response          = new \stdClass();
    $response->success = ($result) ? TRUE : FALSE;
    $response->id      = $result->ID;
    $response->refNo   = $result->ReferenceNumber;

    if ($result == TRUE) 
    {
      $response->message = "Solicitud de asistencia técnica creada con éxito."; 
    }
    else 
    {
      $response->message = $this->CI->TechAssistance->getLastError();
    }
  
    // Exponiendo la respuesta
    echo json_encode($response);
  }

  /**
   * Solicita la información de insumos asociados al incidente
   *
   * @param array $params Get / Post parameters
   */
  function handle_getInfoIncident_ajax_endpoint($params)
  {
    header('Content-Type: application/json');

    $data = json_decode($params['data']);
    $i_id = $data->i_id;

    // Obtiene la información del incidente
    $result = $this->CI->TechAssistance->getInfoTicket($i_id);

    // Formando estructura de respuesta
    $response           = new \stdClass();
    $response->success  = ($result) ? TRUE : FALSE;
    $response->response = $result;

    if ($result === TRUE)
    {
      $response->message = "Información de ticket obtenida con éxito";
    }
    else
    {
      $response->message = $this->CI->TechAssistance->getLastError();
    }
  

    // Exponiendo la respuesta
    echo json_encode($response);
  }

  /**
   * Cancela la solicitud de insumos
   *
   * @param array $params Get / Post parameters
   */
  function handle_cancelIncident_ajax_endpoint($params)
  {
    header('Content-Type: application/json');

    // Formando estructura de respuesta
    $response           = new \stdClass();
    $response->success  = TRUE;
    $response->response = $result;

    // Exponiendo la respuesta
    echo json_encode($response);
  }
}
