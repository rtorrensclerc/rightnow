<?php
namespace Custom\Widgets\supplier;
use RightNow\Connect\v1_2 as RNCPHP;
class reverselogistics extends \RightNow\Libraries\Widget\Base {

    public $contactId;

    function __construct($attrs) {
        parent::__construct($attrs);

        $this->setAjaxHandlers(array(
            'getHHDataSelected_ajax_endpoint' => array(
                'method'      => 'handle_getHHDataSelected_ajax_endpoint',
                'clickstream' => 'getHHDataSelected_ajax_endpoint',
            )
        ));
        $this->CI->load->model('custom/GeneralServices');
        $this->CI->load->model("custom/Supplier");

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
