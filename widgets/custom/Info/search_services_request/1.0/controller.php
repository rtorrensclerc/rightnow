<?php

namespace Custom\Widgets\Info;

use RightNow\Connect\v1_3 as RNCPHP;

class search_services_request extends \RightNow\Libraries\Widget\Base {
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
        $this->CI->load->model('custom/PaymentsInvoicesServices');
        $this->CI->load->model('custom/Contact');
        $this->contactId  = $this->CI->session->getProfile()->c_id->value;
    }
    function getData() {
      $contracts = array();
        $contact = $this->CI->Contact->get($this->contactId);
          
        $a_Status = $this->getStatus();

        $this->data['js']['list']['Status'] = $a_Status; //array de ID, name de contratos

        /*$obj_incident = RNCPHP\Incident::fetch( '220606-000170');
        //$filescount=$obj_incident->FileAttachments[1]->ID .'-' . $obj_incident->FileAttachments[1]->CreatedTime;
        $filescount=json_encode($obj_incident->FileAttachments[1]);
        $obj_incident->FileAttachments[1]->Private = "false";
        $obj_incident->save(RNCPHP\RNObject::SuppressAll);
        */  
        return parent::getData();
    }
    private function setMenulist()
    {
      $a_list_hh = $this->CI->GeneralServices->getListHH($this->contactId);
      $a_status_rut = $this->CI->GeneralServices->getOrganizationStatus($this->contactId);
      $hh_list = $this->hhListToMenu($a_list_hh["data"]);
    

      $brand_filter = $this->hhListToMenu($a_list_hh["data"], TRUE);
      $hhByBrand = $this->getHHByBrand($a_list_hh["data"], $brand_filter);
      $estados='Varios';

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

  public function getOTfile($id)
  {
   
    $incident = RNCPHP\Incident::fetch($id);
    
    $link='';
    $f_count = count($incident->FileAttachments);
    //return $id . '-' .$f_count ;
    if($f_count > 0 )
    {
      foreach ($incident->FileAttachments as $i)
      {
        
        if(substr($i->FileName,0,3)=='OT-')
        {
        $link=$link .'<a href="https://soportedimacoficl.custhelp.com/ci/fattach/get/'. $i->ID .'/'. $i->CreatedTime .'/filename'. '/'. $i->FileName .'">' . $i->FileName .'</a><br>';
        
        }
      }
    }
    return $link;
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
      $ContactData = $this->CI->GeneralServices->getOrganizationStatus($this->contactId);
      $data = json_decode($params['data'], TRUE);
    
      $rut="''";
      if($ContactData->Ruts->List->data->rut_cliente)
      {
      
        $rut="'" . $ContactData->Ruts->List->data->rut_cliente . "'";
      }
      else
      {
       
        foreach($ContactData->Ruts->List->data as $key => $value)
        {
          $rut=$rut . ",'". $value->rut_cliente ."'";
        }
    
      }

     // $trx_rut=trim($rut);
      $trx_hh=  trim($data["HH"]);
      $serie= trim($data["Serie"]);
      // DEBEMOS LLAMAR A UN PROCEIMIENTO QUE SOLO BUSQUE la Factura

      //BUSCA UN REPorte cON un filto

      $a_request = new \stdClass();
      $a_request->id_reporte=102274;
      $a_request->status_values=array('');
      $a_request->ruts =  $rut ;
      $a_request->tipo_soporte=array("Insumo");
      //$Trx=$this->CI->GeneralServices->BuscaReporte($a_request);
      $report_id = 102309 ;
      $filter_value= $rut ;
      $filter_hh=$trx_hh;
      $nro_referencias="";
  
      $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
    $status_filter->Name = 'rut_org';
    $status_filter->Values = array( $filter_value );
    $status_filter->Operator->ID=10;
    //echo json_encode($status_filter);
    $filters = new RNCPHP\AnalyticsReportSearchFilterArray; 
    $filters[] = $status_filter;
  

    $disp='23,9';
    $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
    $status_filter->Name = 'disp';
    $status_filter->Values = array( $disp );
    $status_filter->Operator->ID=10;
    $filters[] = $status_filter;
  
      if($trx_hh)
      {
        $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
        $status_filter->Name = 'hh';
        $status_filter->Values = array( $filter_hh );
        $status_filter->Operator->ID=1;
        //$filters = new RNCPHP\AnalyticsReportSearchFilterArray; 
        $filters[] = $status_filter;
      }

      if($serie)
      {
        $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
        $status_filter->Name = 'Serie';
        $status_filter->Values = array( $serie );
        $status_filter->Operator->ID=1;
        //$filters = new RNCPHP\AnalyticsReportSearchFilterArray; 
        $filters[] = $status_filter;
      }
      $ar= RNCPHP\AnalyticsReport::fetch( 102309);
      $arr= $ar->run(0,$filters);
      
  
      
      for ( $i = $arr->count(); $i--; )
      {
        $row = $arr->next();
       
          $row['link']=$this->getOTfile($row['ID2']);
          //$row['link']=$row['ID2'];
          $array_response['Tickets'][] = $row;
    
      }
      $response = json_encode($array_response);
      //$this->sendResponse(json_encode($array_response));
    
      $Trx=json_decode($response);
              
      $params['Trx_data']=$Trx->Tickets;
      
      echo json_encode($params);
  }
  public function getStatus()
    {
      $Status = array();
     
      $array_temp = array(
      "ID" => 1,
      "name" => "Conectado"
      );
      array_push($Status, $array_temp);
      $array_temp = array(
      "ID" => 2,
      "name" => "No Conectado"
      );
      array_push($Status, $array_temp);
      $array_temp = array(
      "ID" => 3,
      "name" => "No Monitoreado"
      );
      array_push($Status, $array_temp);

      $response = $Status;
           
      return $response;
        
    }
}