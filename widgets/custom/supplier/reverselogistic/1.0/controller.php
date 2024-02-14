<?php
namespace Custom\Widgets\supplier;
use RightNow\Connect\v1_3 as RNCPHP;
class reverselogistic extends \RightNow\Libraries\Widget\Base {
    public $contactId;

    function __construct($attrs) {
        parent::__construct($attrs);

        $this->setAjaxHandlers(array(
            'createIncident_ajax_endpoint' => array(
              'method'      => 'handle_createIncident_ajax_endpoint',
              'clickstream' => 'createIncident_ajax_endpoint',
            )
        ));
        $this->CI->load->model('custom/GeneralServices');
        $this->contactId  = $this->CI->session->getProfile()->c_id->value;
    }

    function getData()
    {
      // Validar que contacto esta autorizado
      $contact = $this->CI->Contact->get($this->contactId);

      if ($contact->result->CustomFields->c->blocked === true)
      {
        $currentURL = \RightNow\Utils\Url::getOriginalUrl(false);
        $this->CI->Contact->doLogout($currentURL);
        header('Location: '.$currentURL."/app/utils/login_form");

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
     * Creación de incidente inicial
     *
     * @param array $params Get / Post parameters
     */
    function handle_createIncident_ajax_endpoint($params)
    {
        header('Content-Type: application/json');
        // Parámetros
        $data                          = json_decode($params['data']);

        $incident                 = new RNCPHP\Incident();
        
        $incident->PrimaryContact = RNCPHP\Contact::fetch($this->contactId);
        $incident->Subject        = "Solicitud Logistica Inversa - WEB";

        $incident->Product     = RNCPHP\ServiceProduct::fetch(68);      // Solicitud
        $incident->Category    = RNCPHP\ServiceCategory::fetch(122);     // Retiro de Insumos
        $incident->Disposition = RNCPHP\ServiceDisposition::fetch(124);  // Logistica Inversa

        //Datos de SOLICITUD
        $incident->CustomFields->c->marca_hh           = $data->brand_hh;
        $incident->CustomFields->c->gasto              = $data->Cantidad;

        // Datos Solicitud
        // Datos de contacto
        $a_contactInfo['name']         = $data->contact_name;
        $a_contactInfo['phone']        = $data->contact_phone;
        $a_contactInfo['comments']     = $data->contact_comments;

        // Dirección
        //$dirId                         = $data->dir_id;
        // Asociar dirección
        //$obj_dir                                       = RNCPHP\DOS\Direccion::first("d_id = $dirId");
        //$incident->CustomFields->DOS->Direccion        = $obj_dir;
        $direccion                         = $data->Direccion;
        $Comuna                         = $data->Comuna;
        
        $incident->CustomFields->c->direccion_correcta=substr($direccion .' -' . $Comuna,0,254);
        
        if( strlen($data->Comments)>0)
        {
          $incident->Threads = new RNCPHP\ThreadArray();
          $incident->Threads[0] = new RNCPHP\Thread();
          $incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
          $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
          $incident->Threads[0]->Text = $data->Comments;
        }

        if(strlen($direccion)>0 )
        {
          $incident->Save();
          $response                      = new \stdClass();
          $response->success             = true;
          $response->id                  = $incident->ID;
          $response->refNo               = $incident->ReferenceNumber;
          $response->message ="OK"; 

          
        }
        else
        {

          $response                      = new \stdClass();
          $response->success             = false;
          
          $response->message ="Debe ingresar la dirección de retiro"; 
        }
       
        
      
       
      
        // Exponiendo la respuesta
        echo json_encode($response);
    }

 /**
   * Obtiene las listas del formulario
   */
  private function setMenulist()
  {
    // HH según organización
    $a_list_hh = $this->CI->GeneralServices->getListHH($this->contactId);
    $a_status_rut = $this->CI->GeneralServices->getOrganizationStatus($this->contactId);


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
      $result = $this->CI->Supplier->getListDir($this->contactId);
      
      if ($result === false)
      {
        echo $this->CI->Supplier->getLastError();
      }
      else
      {
       
        foreach ($result as $dir)
        {
          
          $a_tempDir["name"]                      = $dir->dir_envio . '-' . $dir->ebs_comuna . '-' . $dir->ebs_region ;
          $a_tempDir["ID"]                        = $dir->d_id;
          $this->data["js"]["list"]["list_dir"][] = $a_tempDir;
        }
      }
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
}
