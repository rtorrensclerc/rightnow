<?php
namespace Custom\Widgets\supplier;
use RightNow\Connect\v1_2 as RNCPHP;
class SupplierRequest extends \RightNow\Libraries\Widget\Base {

    public $contactId;

    function __construct($attrs) {
        parent::__construct($attrs);

        $this->setAjaxHandlers(array(
            'getHHDataSelected_ajax_endpoint' => array(
                'method'      => 'handle_getHHDataSelected_ajax_endpoint',
                'clickstream' => 'getHHDataSelected_ajax_endpoint',
            ),
            'createIncident_ajax_endpoint' => array(
                'method'      => 'handle_createIncident_ajax_endpoint',
                'clickstream' => 'createIncident_ajax_endpoint',
            ),
            'requestIncident_ajax_endpoint' => array(
                'method'      => 'handle_requestIncident_ajax_endpoint',
                'clickstream' => 'requestIncident_ajax_endpoint',
            ),
            'getInfoIncident_ajax_endpoint' => array(
                'method'      => 'handle_getInfoIncident_ajax_endpoint',
                'clickstream' => 'getInfoIncident_ajax_endpoint',
            ),
            'cancelIncident_ajax_endpoint' => array(
                'method'      => 'handle_cancelIncident_ajax_endpoint',
                'clickstream' => 'cancelIncident_ajax_endpoint',
            ),
            'requestpending_request_list_ajax_endpoint' => array(
              'method'      => 'handle_requestpending_request_list_ajax_endpoint',
              'clickstream' => 'requestpending_request_list_ajax_endpoint',
          )

            
        ));

        $this->CI->load->model("custom/ws/DatosHH");
        $this->CI->load->model("custom/Supplier");
        $this->CI->load->model('Contact');
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

      // Sólo lecutra
      $read_only = $this->data['attrs']['read_only'];
      if ($read_only === true)
      {
        $incidentId                             = getUrlParm('i_id');
        $incident_data                          = $this->CI->Supplier->getInfoTicket($incidentId);
        $this->data['hh_selector']              = $incident_data->id_hh;
        $this->data['marca_hh']                 = $incident_data->brand_hh;
        $this->data['model_hh']                 = $incident_data->model_hh;
        $this->data['hh_counter_bw']            = $incident_data->cont1_hh;
        $this->data['hh_counter_color']         = $incident_data->cont2_hh;
        $this->data['dispatch_address']         = $incident_data->address->name;
        $this->data['dispatch_address_correct'] = 1;
        $this->data['contact_comment']          = $incident_data->contact_info;
        $this->data['items']                    = $incident_data->items;
      }

      $this->setMenulist();
      return parent::getData();
    }

    /**
     * 
     *
     * @param $a_data
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
            "tipo_transaccion_id" => $data["tipo_transaccion_id"],
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
     * 
     *
     * @param  $a_data
     * @param $a_brands
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
    * Obtiene las listas del formulario
    */
    private function setMenulist()
    {
      // HH según organización
      // $result                              = $this->CI->Supplier->getHHsByOrganizationService($this->contactId);
      $a_list_hh = $this->CI->GeneralServices->getListHH($this->contactId);
      $a_status_rut = $this->CI->GeneralServices->getOrganizationStatus($this->contactId);
      /* $a_list_hh["data"] = array($a_list_hh["data"]);

      echo "<pre>";
      echo json_encode($a_list_hh);
      echo "</pre>"; */
      

      $this->data["js"]["list"]["list_hh"]   = array();
      $this->data["js"]["list"]["brands"]    = array();
      $this->data["js"]["list"]["list_dir"]  = array();
      $this->data["js"]["list"]["HHbloqued"]  = $a_status_rut;

      if ($a_list_hh === false)
      {
        echo $this->CI->GeneralServices->getLastError();
      }
      else
      {
        
        $hh_list = $this->hhListToMenu($a_list_hh["data"]);
        if($hh_list)
        {
          $this->data["js"]["list"]["list_hh"] = $hh_list;

          $hh_brands_lists                     = $this->hhListToMenu($a_list_hh["data"], TRUE);
          if($hh_brands_lists)
          {
            $this->data["js"]["list"]["brands"] = $hh_brands_lists;
            // Marcas y sus hh
            if($hh_brands_lists)
            {
              $hhByBrand = $this->getHHByBrand($a_list_hh["data"], $hh_brands_lists);

              if($hhByBrand)
              {
                $this->data["js"]["list"]["brands_hh"] = $hhByBrand;
              }
              else
              {
                echo getMessageBase(CUSTOM_MSG_SUPPLIER_REQUEST_CANT_GET_HH_BY_BRAND);
                //CUSTOM_MSG_SUPPLIER_REQUEST_CANT_GET_HH_BY_BRAND
              }
            }
          }
          else
            echo getMessageBase(CUSTOM_MSG_SUPPLIER_REQUEST_CANT_GET_BRANDS);
            //CUSTOM_MSG_SUPPLIER_REQUEST_CANT_GET_BRANDS
        }
        else
        {
          echo getMessageBase(CUSTOM_MSG_SUPPLIER_REQUEST_CANT_GET_HH);
          //CUSTOM_MSG_SUPPLIER_REQUEST_CANT_GET_HH
        }
      }


      // Dirección
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
    * Transforma listas a menus
    */
    private function transformListToMenu($a_menuRN)
    {
      $a_menu = array();
      foreach ($a_menuRN as $key => $menu) {
        $a_tempMenu["name"]    = $menu->LookupName;
        $a_tempMenu["ID"]      = $menu->ID;
        $a_menu[] = $a_tempMenu;
      }
      return $a_menu;
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

        // Datos Solicitud
        $a_Infohh['contador_bn']       = $data->cont_bn;
        $a_Infohh['contador_color']    = $data->cont_color;
        $a_Infohh['hh']                = $data->id_hh;

        // Datos de HH
        $a_Infohh['serial_hh']         = $data->serial_hh;
        $a_Infohh['brand_hh']          = $data->brand_hh;
        $a_Infohh['model_hh']          = $data->model_hh;
        $a_Infohh['client_covenant']   = $data->client_covenant; // Boolean
        $a_Infohh['client_blocked']    = $data->client_blocked; // Boolean
        $a_Infohh['contract_type']     = $data->contract_type;
        $a_Infohh['sla_hh_rsn']        = $data->sla_hh_rsn;
        $a_Infohh['delfos']            = $data->delfos;
        $a_Infohh['machine_serial']    = $data->machine_serial;
        $a_Infohh['supplier_covenant'] = $data->supplier_covenant; // Boolean
        $a_Infohh['brackets_covenant'] = $data->brackets_covenant; // Boolean
        $a_Infohh['inventory_item_id'] = $data->inventory_item_id;
        $a_Infohh['trx_id_erp']        = $data->trx_id_erp;
        $a_Infohh['priorization']      = $data->priorization;
        if (!empty($obj_supplier))
        {
          $a_Infohh['supplier']        = $data->suppliers;
        }

        // Datos de contacto
        $a_contactInfo['name']         = $data->contact_name;
        $a_contactInfo['phone']        = $data->contact_phone;
        $a_contactInfo['comments']     = $data->contact_comments;

        // Dirección
        $dirId                         = $data->dir_id;

        //echo "Contact ID ".$this->contactId;

        // Crea el incidente

        //info_hh.respuesta.trx_id_erp


        $result                        = $this->CI->Supplier->createTicket($this->contactId, $a_Infohh, $a_contactInfo, $dirId);

        $response                      = new \stdClass();
        $response->success             = ($result)?true:false;
        $response->id                  = $result->ID;
        $response->refNo               = $result->ReferenceNumber;

        if ($result == true)
        {
          $response->message =getMessageBase(CUSTOM_MSG_SUPPLIER_REQUEST_SUCCESS_CREATED);
        }
        else
        {
          $response->message = $this->CI->Supplier->getLastError();
        }

        // Exponiendo la respuesta
        echo json_encode($response);
    }

    /**
     * Obtiene la información del HH
     *
     * @param array $params Get / Post parameters
     */
    function handle_requestpending_request_list_ajax_endpoint($params)
    {
      header('Content-Type: application/json');
  
      $data               = json_decode($params['data']);
      $hh                 = $data->hh;
      // Obtiene la información del HH
      $result             = $this->CI->DatosHH->getDatosHHInsumos($hh);
      $asset = RNCPHP\Asset::first("SerialNumber = " . $hh );
      
      
       /* Aca buscamos los ticket pendientes paralelos
        estado 
        1 ingresado
        104 Rechazado por Crédito
        148 Cerrado Por Usuario
        149 Cancelado
        2 Cerrado
        196 Retenido por Supervisión
      */
     // $ticket_paralelo = RNCPHP\Incident::find("Disposition.ID = 24 and StatusWithType.Status.ID not in(104,148,149,2,196)  and Asset.ID = {$asset->ID}");

      $response           = new \stdClass();
      $response->success  = true;
      $response->hh  = $data->hh;
      /*
      if(count($ticket_paralelo)>0)
        {
        
          $response->ticket_paralelo=count($ticket_paralelo);
          $response->ref_ticket_paralelo=$ticket_paralelo[0]->ReferenceNumber;
          $response->Subject_ticket_paralelo=$ticket_paralelo[0]->Subject;

        }
        else
        {
          $response->ticket_paralelo=count($ticket_paralelo);
        }
      */
        

      // Exponiendo la respuesta
      echo json_encode($response);
    }

  
    /**
     * Obtiene la información del HH
     *
     * @param array $params Get / Post parameters
     */
    function handle_getHHDataSelected_ajax_endpoint($params)
    {
      header('Content-Type: application/json');

      $data               = json_decode($params['data']);
      $hh                 = $data->hh;
      $trx_id_erp         = $data->trx_id_erp;

      // Obtiene la información del HH
      $result             = $this->CI->DatosHH->getDatosHHInsumos($hh);
      
      $asset = RNCPHP\Asset::first("SerialNumber = " . $hh );
      
      
      $ticket_paralelo = RNCPHP\Incident::find("Disposition.ID = 24 and StatusWithType.Status.ID not in(104,148,149,2,196)  and Asset.ID = {$asset->ID}");
   
      
      

      // Formando estructura de respuesta
      $response           = new \stdClass();
      $response->success  = ($result)?true:false;


      if ($result === false)
      {
        $response->response                         = false;
        $response->message                          = $this->CI->DatosHH->getLastError();
      }
      else
      {
        $a_temp_json_result                         = json_decode($result, true);

        $obj_response                               = $this->CI->Supplier->getLastCounter($hh);
        if ($obj_response !== false)
        {
          $a_temp_json_result['lastCounter']['color'] = $obj_response->color;
          $a_temp_json_result['lastCounter']['bn']    = $obj_response->bn;
        }
        else
        {
          $a_temp_json_result['lastCounter']['color'] = 0;
          $a_temp_json_result['lastCounter']['bn']    = 0;
        }

    
        if(count($ticket_paralelo)>0)
        {
        
          $a_temp_json_result['ticket_paralelo']=count($ticket_paralelo);
          $a_temp_json_result['ref_ticket_paralelo']=$ticket_paralelo[0]->ReferenceNumber;
          $a_temp_json_result['Subject_ticket_paralelo']=$ticket_paralelo[0]->Subject;
          $a_temp_json_result['trx_id_erp']= $trx_id_erp;
         

        }
        else
        {
          $a_temp_json_result['ticket_paralelo']=count($ticket_paralelo);
          $a_temp_json_result['trx_id_erp']= $trx_id_erp;
          
        }
        

        $json               = json_encode($a_temp_json_result);

        $response->response = $json;
        $response->message  = getMessageBase(CUSTOM_MSG_SUPPLIER_REQUEST_HH_INFO_SUCCESS);
        
        
      }

      // Exponiendo la respuesta
      echo json_encode($response);
    }

    /**
     * Solicita los insumos enviando el formulario
     *
     * @param array $params Get / Post parameters
     */
    function handle_requestIncident_ajax_endpoint($params)
    {
      header('Content-Type: application/json');

      $data               = json_decode($params['data']);
      // TODO: Lista de ítems + cantidades
      $i_id               = $data->i_id;


      // TODO:  Reviza que no tenga ticket con insumo solicitado paralelo
      //

      $a_lineItems        = array();
      foreach ($data->lines_items as $item)
      {
        $a_tempLines['id']                = $item->id;
        $a_tempLines['quantity_selected'] = $item->quantity_selected;
        $a_lineItems[] = $a_tempLines;
      }

      $result             = $this->CI->Supplier->requestTicket($i_id, $a_lineItems);
      // Formando estructura de respuesta
      $response           = new \stdClass();
      $response->success  = ($result)?true:false;
      $response->response = $result;

      if ($result === true)
      {
        $response->message = getMessageBase(CUSTOM_MSG_SUPPLIER_REQUEST_QUANTITY_UPDATED);
      }
      else
      {
        $response->message = $this->CI->Supplier->getLastError();
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

      $data               = json_decode($params['data']);
      $i_id               = $data->i_id;

      // Ontiene la información del incidente
      $result             = $this->CI->Supplier->getInfoTicket($i_id);

      // Formando estructura de respuesta
      $response           = new \stdClass();
      $response->success  = ($result)?true:false;
      $response->response = $result;


      /* Aca buscamos los ticket pendientes paralelos
        estado 
        1 ingresado
        104 Rechazado por Crédito
        148 Cerrado Por Usuario
        149 Cancelado
        2 Cerrado
        196 Retenido por Supervisión
      */

      $hh                 = $result ->id_hh;
      $asset = RNCPHP\Asset::first("SerialNumber = " . $hh );
      
      $ticket_paralelo = RNCPHP\Incident::find("Disposition.ID = 24 and StatusWithType.Status.ID not in(1,104,148,149,2)  and Asset.ID = {$asset->ID} and ID not in($i_id)");
      
      if(count($ticket_paralelo)>0)
      {
      
        $a_temp_json_result['ticket_paralelo']=count($ticket_paralelo);
        
        $a_temp_json_result['tickets']=$ticket_paralelo;
        $a_items                         = RNCPHP\OP\OrderItems::find("Incident.ID = {$ticket_paralelo[0]->ID}");
        $a_objItems    = array();
        foreach($a_temp_json_result['tickets'] as $ticket)
        {
          $a_items                         = RNCPHP\OP\OrderItems::find("Incident.ID = {$ticket->ID}");
        
          foreach ($a_items as $item)
          {
              if( $item->QuantitySelected>0)
              {
                $obj_item                        = new \stdClass();
                $obj_item->lineId                = $item->ID;
                $obj_item->name                  = $item->Product->Name;
                $obj_item->alias                 = $item->Product->Alias;
                $obj_item->part_number           = $item->Product->PartNumber;
                $obj_item->InventoryItemId       = $item->Product->InventoryItemId;
                $obj_item->quantity_suggested    = $item->QuantitySuggested;
                $obj_item->quantity_selected     = $item->QuantitySelected;
                $obj_item->ReferenceNumber       = $ticket->ReferenceNumber;
                switch($ticket->StatusWithType->Status->ID)
                {
                  case 196: // Retenido por Supervisión
                    $obj_item->Estado                = 'Procesando';
                    break;
                  case 177: //  incidents.status_id = 177
                    $obj_item->Estado                = 'En Aprobación';
                    break;
                  case 157: //Repuesto No Abastecido
                    $obj_item->Estado                = 'Procesando';
                    break;
                  case 129:
                    $obj_item->Estado                = 'Procesando';
                    break;
                  case 175:
                    $obj_item->Estado                = 'Supervision';
                    break;
                  case 176:
                    $obj_item->Estado                = 'Procesando';
                    break;
                  case 193:
                    $obj_item->Estado                = 'En Evaluacion Comercial';
                    break;
                  case 195:
                    $obj_item->Estado                = 'Despachado';
                    break; 
                  default:  // 140  Despachado   
                    $obj_item->Estado                = $ticket->StatusWithType->Status->LookupName;
                    break;

                }
                
                $a_objItems[]                    = $obj_item;
              }
          }
        }
        $a_temp_json_result['items_paralelo']=$a_objItems;
      }
      else
      {
        $a_temp_json_result['ticket_paralelo']=count($ticket_paralelo);
      }
      $response->tickets_paralelos = $a_temp_json_result;
      if ($result === true)
      {
        $response->message = getMessageBase(CUSTOM_MSG_SUPPLIER_REQUEST_TICKET_INFO_SUCCESS);
      }
      else
      {
        $response->message = $this->CI->Supplier->getLastError();
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

      $data               = json_decode($params['data']);
      $i_id               = $data->i_id;

      // Cancela el incidente
      // TODO: Cancelar solicitud
      $result             = $this->CI->Supplier->cancelIncident($i_id);

      // Formando estructura de respuesta
      $response           = new \stdClass();
      $response->success  = ($result)?true:false;
      $response->response = $result;

      if ($result === true)
      {
        $response->message = getMessageBase(CUSTOM_MSG_SUPPLIER_REQUEST_INCIDENT_CANCEL_SUCCESS);
      }
      else
      {
        $response->message = getMessageBase(CUSTOM_MSG_SUPPLIER_REQUEST_ERROR_MSG_VAR);
      }

      // Exponiendo la respuesta
      echo json_encode($response);
    }

}
