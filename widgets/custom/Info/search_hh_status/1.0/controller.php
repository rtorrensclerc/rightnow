<?php

namespace Custom\Widgets\Info;

use RightNow\Connect\v1_3 as RNCPHP;

class search_hh_status extends \RightNow\Libraries\Widget\Base {
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
        //echo "--->" .json_encode($this->contactId);
       // $contact = $this->CI->Contact->get($this->contactId);

        $a_status_rut = $this->CI->GeneralServices->getOrganizationStatus($this->contactId);
        //echo "--->" .json_encode($a_status_rut);
        //$this->setMenulist();
        $a_Status = $this->getStatus();
        //echo json_encode($a_Status);
        $this->data['js']['list']['rut']=$a_status_rut->Customer->CustomerData->Customer->tRUT;
        $this->data['js']['list']['Status'] = $a_Status; 
        $this->data['js']['list']['Status_rut'] = $this->getRuts($a_status_rut->Ruts->List->data);
        //$this->setMenulist();
        return parent::getData();
    }
    private function setMenulist()
    {
      //$a_list_hh = $this->CI->GeneralServices->getListHH($this->contactId);
      //echo "--->" .json_encode($a_list_hh);
      //$a_status_rut = $this->CI->GeneralServices->getOrganizationStatus($this->contactId);
      //echo "--->" .json_encode($a_status_rut);
      //$hh_list = $this->hhListToMenu($a_list_hh["data"]);
        $ruts=$this->getRuts($this->data['js']['list']['Status_rut']);
      //$brand_filter = $this->hhListToMenu($a_list_hh["data"], TRUE);
      //$hhByBrand = $this->getHHByBrand($a_list_hh["data"], $brand_filter);
      //echo json_encode($ruts);
    }

 /**
   * ¿?
   *
   * @param ¿? $a_data
   * @param ¿? $a_brands
   * @return void
   */
  private function getRuts($a_data)
  {
    $a_menu = array();
    
      if(count($a_data)>1)
      {
      //echo json_encode($a_data);
      foreach($a_data as $data)
      {
         // echo "-->" .$data->rut_cliente;
          $tmp = array(
            "name" => $data->rut_cliente . " - " . $data->nombre_cliente,
            "ID" => $data->rut_cliente
          );
          array_push($a_menu, $tmp);
      }
      }
      else
      {
        $tmp = array(
          "name" => $a_data->rut_cliente . " - " . $a_data->nombre_cliente,
          "ID" => $a_data->rut_cliente
        );
        array_push($a_menu, $tmp);
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
 
      $trx_rut= trim($data["rut_list"]);
      $trx_hh=  trim($data["HH"]);
      $serie= trim($data["Serie"]);

      switch($data["status_list"])
      {
        case "1" :
          $status= 'CONECTADO';
          break;
        case "2" :
          $status= 'DESCONECTADO';
          break;
        case "3" :
          $status= 'NO MONITOREADO';
          break;
        default:
          $status= '';
          break;
      }
     
      //

     
      $Trx=$this->CI->GeneralServices->BuscaInfoHHNbp($trx_rut,$trx_hh,$serie,$status);
  
     

      if($Trx->hhs->status==null)
      {
          $params['Trx_data']=$Trx->hhs->status;
          //echo "ERROR1 ". json_encode($Trx);
      }
      else
      {
        
          if(!array_key_exists(0, $Trx->hhs->status))
          {
              $params['Trx_data'][0]=$Trx->hhs->status;
              //echo "ERROR2 ". json_encode($Trx);
          }
          else
          {
                $params['Trx_data']=$Trx->hhs->status;
              
              //echo "ERROR3 ". json_encode($Trx);
          }
      }
      $params['success']=$Trx->result;
      
      echo json_encode($params);
  }
  public function getStatus()
  {
    $Status = array();
   
    $array_temp = array(
    "ID" => 1,
    "name" => "CONECTADO"
    );
    array_push($Status, $array_temp);
    $array_temp = array(
    "ID" => 2,
    "name" => "DESCONECTADO"
    );
    array_push($Status, $array_temp);
    $array_temp = array(
    "ID" => 3,
    "name" => "NO MONITOREADO"
    );
    array_push($Status, $array_temp);

    $response = $Status;
         
    return $response;
      
  }
}