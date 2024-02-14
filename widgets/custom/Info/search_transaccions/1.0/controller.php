<?php

namespace Custom\Widgets\Info;

use RightNow\Connect\v1_3 as RNCPHP;

class search_transaccions extends \RightNow\Libraries\Widget\Base {
  public $contactId;
  private $errorMessage;
    function __construct($attrs) {
        parent::__construct($attrs);
        $this->setAjaxHandlers(array(
          'getTrx_ajax_endpoint' => array(
              'method'    => 'handle_getTrx_ajax_endpoint',
              'clickstream' => 'getTrx_ajax_endpoint'
          )
          
      ));
        $this->CI->load->model("custom/ws/DatosHH");
        $this->CI->load->model("custom/TechAssistance");
        $this->CI->load->model('Contact');
        $this->CI->load->model('custom/GeneralServices');
        $this->contactId  = $this->CI->session->getProfile()->c_id->value;
    }
    function getData() {

        $contact = $this->CI->Contact->get($this->contactId);

        $this->setMenulist();
        return parent::getData();
    }
    private function setMenulist()
    {
      $a_list_hh = $this->CI->GeneralServices->getListHH($this->contactId);
      $a_status_rut = $this->CI->GeneralServices->getOrganizationStatus($this->contactId);
      $hh_list = $this->hhListToMenu($a_list_hh["data"]);
    

      $brand_filter = $this->hhListToMenu($a_list_hh["data"], TRUE);
      $hhByBrand = $this->getHHByBrand($a_list_hh["data"], $brand_filter);
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
          "ID" => $data["hh"]
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
     * Handles the handle_getTrx_ajax_endpoint AJAX request
     * @param array $params Get / Post parameters
     */
    function handle_getTrx_ajax_endpoint($params) {
      // Perform AJAX-handling here...
      
     
      $contact = $this->CI->Contact->get($this->contactId);
      $rut = $contact->Organization->CustomFields->c->rut;
      $data = json_decode($params['data'], TRUE);
      
      
   
      $trx_rut=trim($rut);
      $trx_hh=  trim($data["HH"]);
      $trx_from= trim($data["trx_from"]);
      $trx_to=trim($data["trx_to"]);
      $serie= trim($data["Serie"]);
     
      // DEBEMOS LLAMAR A UN PROCEIMIENTO QUE SOLO BUSQUE la Factura


      $Trx=$this->CI->GeneralServices->SearchTrx($trx_rut,$trx_from,$trx_to,$trx_hh,$serie);
  
     

      if($Trx->Contadores->Contador==null)
      {
          $params['Trx_data']=$Trx->Contadores->Contador;
          //echo "ERROR1 ". json_encode($Trx);
      }
      else
      {
        
          if(!array_key_exists(0, $Trx->Contadores->Contador))
          {
              $params['Trx_data'][0]=$Trx->Contadores->Contador;
              //echo "ERROR2 ". json_encode($Trx);
          }
          else
          {
                $params['Trx_data']=$Trx->Contadores->Contador;
              
              //echo "ERROR3 ". json_encode($Trx);
          }
      }
      $params['success']=$Trx->result;
      
      echo json_encode($params);
  }

}