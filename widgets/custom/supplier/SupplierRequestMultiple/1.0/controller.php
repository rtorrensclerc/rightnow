<?php
namespace Custom\Widgets\supplier;
use RightNow\Connect\v1_3 as RNCPHP;

class SupplierRequestMultiple extends \RightNow\Libraries\Widget\Base {

    public $contactId;

    function __construct($attrs) {
        parent::__construct($attrs);

        $this->setAjaxHandlers(array(
            'sendCSV_ajax_endpoint' => array(
                'method'      => 'handle_sendCSV_ajax_endpoint',
                'clickstream' => 'sendCSV_ajax_endpoint',
            ),
            'requestIncident_ajax_endpoint' => array(
                'method'      => 'handle_requestIncident_ajax_endpoint',
                'clickstream' => 'requestIncident_ajax_endpoint',
            ),
            'parseCSV_ajax_endpoint' => array(
              'method'      => 'handle_parseCSV_ajax_endpoint',
              'clickstream' => 'parseCSV_ajax_endpoint',
          ),
            'processCSV_ajax_endpoint' => array(
              'method'      => 'handle_processCSV_ajax_endpoint',
              'clickstream' => 'processCSV_ajax_endpoint',
          ),
          'CreateTickettIncident_ajax_endpoint' => array(
            'method'      => 'handle_CreateTickettIncident_ajax_endpoint',
            'clickstream' => 'CreateTickettIncident_ajax_endpoint',
         )
        ));

        $this->CI->load->model("custom/ws/DatosHH");
        $this->CI->load->model("custom/Supplier");
        $this->CI->load->model('custom/Contact');
        $this->CI->load->model('custom/Organization');
        $this->CI->load->model('custom/IncidentGeneral');
        $this->CI->load->model('custom/GeneralServices');
        
        $this->CI->load->helper('utils');

        $this->contactId = $this->CI->session->getProfile()->c_id->value;
    }

    function getData()
    {
      return parent::getData();
    }
    /**
     * Recibe la información CSV de la carga masiva
     *
     * @param array $params Get / Post parameters
     */
    function handle_CreateTickettIncident_ajax_endpoint($params)
    {
      header('Content-Type: application/json');
      $data         = json_decode($params['data']);
      $data->id=$data->id+1;
      echo json_encode($data);
    }


     /**
     * Recibe la información CSV de la carga masiva
     *
     * @param array $params Get / Post parameters
     */
    function handle_parseCSV_ajax_endpoint($params)
    {
      header('Content-Type: application/json');
      $data         = json_decode($params['data']);

      $csv          = $data->data;
        
      $a_data       = parserTextCSV($csv);
      
      $header       = $a_data["header"];
      $a_csv        = $a_data["csv"];

      $data->id=$data->id;
      $data->csv=$a_data['csv'];

      $data->a_data=$a_data;

      $c_id         = $this->CI->session->getProfile()->c_id->value;
      $CI = get_instance();
      $obj_info_contact= $CI->session->getSessionData('info_contact');
      
      $user         = $this->CI->Contact->getContactById($c_id);
      $ContactData = $this->CI->GeneralServices->getOrganizationStatus($c_id);
      $data->ContactData=$ContactData;
      
      $find_ruts ='';
      if(count($ContactData->Ruts->List->data)>1)
      {
        foreach($ContactData->Ruts->List->data  as $key => $irut)
        {
          $find_ruts = $find_ruts . "'". $irut->rut_cliente  ."',";
        
        }
      }
      else
      {
        $find_ruts = $find_ruts . "'". $ContactData->Ruts->List->data->rut_cliente ."',";
        
      }
      $find_ruts =$find_ruts = $find_ruts . "'0'";

      $busca_dir=0;
      $a_directions     = RNCPHP\DOS\Direccion::find("Organization.CustomFields.c.rut in ({$find_ruts})");
     
       
      $c_org_id =$obj_info_contact['Org_id'] ;
      //if (!empty($user->Organization->ID))
      if (!empty($c_org_id))
      {
        //$a_directions = $this->CI->Organization->getDirectionsByOrgId($user->Organization->ID);
        //$a_directions = $this->CI->Organization->getDirectionsByOrgId($c_org_id);

        if (count($a_directions) > 0)
        {
          foreach ($a_directions as $direction)
          {
            $a_tempSelect['ID']   = $direction->ID;
            $a_tempSelect['name'] = $direction->dir_envio;
            $a_tempSelect['d_id'] = $direction->d_id;
            $a_address[]          = $a_tempSelect;
            
          }
        }
      }
      else
      {
        throw new \Exception("Su contacto no tiene organización asociada " .$c_org_id);
      }
      $data->a_address=$a_address;



        /* BUSCA TODAS LAS DIRECCIONES */
       
        // Validar de Campo HH
        if (array_search('HH EQUIPO', $header) === false)
        {
          throw new \Exception("Cabecera de CSV no contiene el valor 'HH'");
        }

        // Valida que el CSV no esté vacío o mal formado
        if (!count($a_csv))
        {
          throw new \Exception("Archivo adjunto mal formado o vacío.");
        }

        if (array_search('Contador 1 B/N',$header) === false)
        {
          throw new \Exception("Cabecera de CSV no contiene el valor cont1_hh'");
        }
        $data->find_ruts=$find_ruts;
      echo json_encode($data);
    }

    /**
     * Recibe la información CSV de la carga masiva
     *
     * @param array $params Get / Post parameters
     */
    function handle_processCSV_ajax_endpoint($params)
    {
      try
      {  
        header('Content-Type: application/json');

        // Parámetros
        $data         = json_decode($params['data']);
  
        
       $csv          = $data->data;
        
       $a_data       = parserTextCSV($csv);
      
      
       $a_csv        = $a_data["csv"];
       $find_ruts=$data->find_ruts;

        $dirId    =0;
        //Obtener direcciones asociadas al contacto
        $c_id         = $this->CI->session->getProfile()->c_id->value;
        $user         = $this->CI->Contact->getContactById($c_id);
        if (count($data->a_address) > 0)
        {
          foreach ($data->a_address as $direction)
          {
            $a_tempSelect['ID']   = $direction->ID;
            $a_tempSelect['name'] = $direction->name;
            $a_tempSelect['d_id'] = $direction->d_id;
            $a_address[]          = $a_tempSelect;
            
          }
        }
        
  
      


        
        $a_errors = array();
        $a_no_errors = array();
      
        $err=0;
        $line=$a_csv[$data->id];
        /*foreach ($a_csv as $key => $line)
        {*/
            $a_temp['errors']        = array();
            $a_temp['hh']            = $line["HH EQUIPO"];
            $a_temp['counter_black'] = $line["Contador 1 B/N"];
            $a_temp['counter_color'] = $line["Contador 2 Color"];
            $a_temp['count_black']   = $line["Toner Negro"];
            $a_temp['count_cyan']    = $line["Toner Cyan"];
            $a_temp['count_magenta'] = $line["Toner Magenta"];
            $a_temp['count_yellow']  = $line["Toner Amarillo"];
          

            if (!is_numeric($a_temp['hh']))
            {
              $a_temp['errors'][] = "El valor de 'HH' no es numérico.  (".$a_temp["hh"].")";
              $err=1;
            }

            if (!is_numeric($a_temp['counter_black']) and !empty($a_temp['counter_black']))
            {
              $a_temp['errors'][] = "El valor de 'contador 1' no es numérico.  (".$a_temp["counter_black"].")";
              $err=1;
              $counter_black      = false;
            }
            else
            {
              $counter_black      = true;
            }

            if (!is_numeric($a_temp['counter_color']) and !empty($a_temp['counter_color']))
            {
              $err=1;
              $a_temp['errors'][] = "El valor de 'contador 2' no es numérico.  (".$a_temp["counter_color"].")";
              $counter_color      = false;
            }
            else
            {
              $counter_color      = true;
            }

            if (!is_numeric($a_temp['count_black']) and !empty($a_temp['count_black']))
            {
              $err=1;
              $a_temp['errors'][] = "El valor de 'Toner Negro' no es numérico.  (".$a_temp["count_black"].")";
              $count_black        = false;
            }
            else
            {
              $count_black = true;
            }


            if (!is_numeric($a_temp['count_cyan']) and !empty($a_temp['count_cyan']))
            {
              $err=1;
              $a_temp['errors'][] = "El valor de 'Toner Cyan' no es numérico.  (".$a_temp["count_cyan"].")";
              $count_cyan = false;
            }
            else
            {
              $count_cyan = true;
            }

            if (!is_numeric($a_temp['count_magenta']) and !empty($a_temp['count_magenta']))
            {
              $err=1;
              $a_temp['errors'][] = "El valor de 'Toner Magenta' no es numérico.  (".$a_temp["count_magenta"].")";
              $count_magenta = false;
            }
            else
            {
              $count_magenta = true;
            }

            if (!is_numeric($a_temp['count_yellow']) and !empty($a_temp['count_yellow']))
            {
              $err=1;
              $a_temp['errors'][] = "El valor de 'Toner Amarillo' no es numérico.  (".$a_temp["count_yellow"].")";
              $count_yellow = false;
            }
            else
            {
              $count_yellow = true;
            }

            if($counter_black === true && $counter_color === true)
            {
              
              $cc_black = (int) $a_temp['counter_black'];
              $cc_color = (int) $a_temp['counter_color'];

              if(($cc_black + $cc_color) < 1)
              {
                $err=1;
                $a_temp['errors'][] = "Al menos uno de los contadores de B/N y Color deben ser mayores que cero.";
              }
            }

            if($count_black === true && $count_cyan === true && $count_magenta === true && $count_yellow === true)
            {
              $c_black    = (int) $a_temp['count_black'];
              $c_cyan     = (int) $a_temp['count_cyan'];
              $c_magenta  = (int) $a_temp['count_magenta'];
              $c_yellow   = (int) $a_temp['count_yellow'];

              if(($c_black + $c_cyan + $c_magenta + $c_yellow) < 1)
              {
                $err=1;
                $a_temp['errors'][] = "Debe solicitar al menos un toner para continuar.";
              }
            }

            if (
              intval($a_temp['count_black'])   > intval(RNCPHP\Configuration::fetch(CUSTOM_CFG_MAX_QTY_ITEM_MULTIPLE)->Value) ||
              intval($a_temp['count_cyan'])    > intval(RNCPHP\Configuration::fetch(CUSTOM_CFG_MAX_QTY_ITEM_MULTIPLE)->Value) ||
              intval($a_temp['count_magenta']) > intval(RNCPHP\Configuration::fetch(CUSTOM_CFG_MAX_QTY_ITEM_MULTIPLE)->Value) ||
              intval($a_temp['count_yellow'])  > intval(RNCPHP\Configuration::fetch(CUSTOM_CFG_MAX_QTY_ITEM_MULTIPLE)->Value)
            )
            {
              $err=1;
              $a_temp['errors'][] = 'La cantidad máxima de insumos es \'' . strval(RNCPHP\Configuration::fetch(CUSTOM_CFG_MAX_QTY_ITEM_MULTIPLE)->Value) . '\', realice la corrección y suba el CSV nuevamente.';
            }

            

         

            
            // Lógica para validar que pertenece a la organización
            //echo "1[" . json_encode($a_response) . "]<br>";
            //echo "2[" . json_encode($a_temp) . "]<br>";
            $responseService = $this->CI->DatosHH->getDatosHHInsumos($a_temp['hh']);
            
            if ($responseService === false)
            {
              $a_temp['errors'][] = $this->CI->DatosHH->getLastError();
              $a_errors[]         = $a_temp;
  
              //continue;
            }
            else
            {
              $a_response_pre = json_decode($responseService, true);
  
              if (array_key_exists ('respuesta', $a_response_pre) !== true)
              {
                $a_temp['errors'][] = "Respuesta no esperada desde servicio.";
                $a_errors[] = $a_temp;
                //continue;
              }
  
              $a_response = $a_response_pre["respuesta"];
             
              if ($a_response["resultado"] !== "OK")
              {
                $a_temp['errors'][] = "Número de HH no identificado en Dimacofi";
              }
  
              // if ($a_response["Convenio"] === false or $a_response["convenio_insumos"] === false)
              // {
              //   $a_temp['errors'][] = "La HH no tiene convenio de insumos.";
              // }
              //
              // if ($a_response["Direccion"]["Bloqueado"] === true)
              // {
              //   $a_temp['errors'][] = "Contrato registra deuda.";
              // }
  
              // Lógica para validar que pertenece a la organización
              $dirId    = $a_response["Direccion"]["ID_direccion"];
              
              $CI = get_instance();
         
            $obj_info_contact= $CI->session->getSessionData('info_contact');
            $c_org_id = $obj_info_contact['Org_id'] ;
            //$c_org_id = $user->Organization->ID;
            //$obj_dir  = $this->CI->Organization->getDirectionByEbsId($dirId);
            if($a_response["TipoContrato"]=="Cargo")
            {
              $err=1;
              $a_temp['errors'][] = "HH Sin Contrato asociado. " . $a_temp['hh'] . "-" . $a_response["TipoContrato"];
            }
            else
            {
              if($a_response["Bloqueado"]=='true')
              {
                $err=1;
                $a_temp['errors'][] = "Dierccion  HH Bloqueada por credito. " . $a_response["Direccion"];
                
              }
            }

            $id_dir_selected=0;
      
            if ($a_address != false)
            {
              //echo "ID ORG Contacto ". $c_org_id ."ID Dirección ". $obj_dir->ID ." ID ORG HH ". $obj_dir->Organization->ID;
             
              $id_dir_selected=0;
              foreach($a_address as $dir)
              {
                 
                  if($dir['d_id']== $dirId)
                  {
                    $id_dir_selected=$dir['ID'];
                    break;
                  }
              }
              /*  if ($obj_dir->Organization->ID !== $c_org_id)
                {
                  $a_temp['errors'][] = "La HH no figura a la organización asociada a su contrato.";
                }
                */
            }
            else
            {
              $a_temp['errors'][] = "La dirección asociada no figura en el sistema, favor comunicarse con los administradores " ;
              $err=1;
            }


            if($id_dir_selected==0)
            {
              $err=1;
              $a_temp['errors'][] = "EL HH " . $a_temp['hh'] . "-" . $id_dir_selected ."-" . $dirId. "-" . " no pertenece a la empresa  asosciada a su contacto " ;
          
            }


            if ($err > 0)
            {
              $a_errors[] = $a_temp;
              //continue;
            }
            else
            {
            //echo "[" . json_encode($a_temp) . "]";
            // Exitoso
              $a_temp['id_dir_selected'] = $id_dir_selected;
              $a_temp['info_service']    = $a_response;

              $a_temp['Bloqueado']=$a_response['Bloqueado'];

              $a_no_errors[]             = $a_temp;
            }
            $a_response=null;
            $err=0;
          }
        /*}*/ 
       
        $response = new \stdClass;
        $response->id=$data->id+1;
        $response->ContactData=$data->ContactData;
        $response->a_data = $data->a_data;
        $response->data=$data->data;
        $response->Contact=json_encode($ContactData->Ruts->List->data);
        $response->ruts = $find_ruts;

        $response->success   = true;
        $response->a_address   = $a_address;
        $response->a_directions   = $a_directions;
        $response->errors    = $a_errors;
        $response->no_errors = $a_no_errors;
        
        if (count($a_errors) < 1)
          $response->message   = "Solicitud de insumos analizada sin errores." . $hhs;
        else
          $response->message   = "Solicitud de insumos analizada con errores, favor revisar la tabla.";

        // Exponiendo la respuesta
        echo json_encode($response);
      }
      catch (\Exception $e)
      {
        $response          = new \stdClass;
        $response->success = false;
        $response->message = $e->getMessage();
        echo json_encode($response);
      }
    }
    /**
     * Recibe la información CSV de la carga masiva
     *
     * @param array $params Get / Post parameters
     */
    function handle_sendCSV_ajax_endpoint($params)
    {

      
      try
      {
        header('Content-Type: application/json');

        // Parámetros
        $data         = json_decode($params['data']);
        $csv          = $data->data;
        
        $a_data       = parserTextCSV($csv);
      
        $header       = $a_data["header"];
        $a_csv        = $a_data["csv"];

        //Obtener direcciones asociadas al contacto
        $c_id         = $this->CI->session->getProfile()->c_id->value;
        $user         = $this->CI->Contact->getContactById($c_id);
        $a_address    = array();

        $CI = get_instance();
        $obj_info_contact= $CI->session->getSessionData('info_contact');
         /* BUSCA TODAS LAS DIRECCIONES */
         $contactId  = $this->CI->session->getProfile()->c_id->value;
         $ContactData = $this->CI->GeneralServices->getOrganizationStatus($contactId);
         $find_ruts ='';
         if(count($ContactData->Ruts->List->data)>1)
         {
           foreach($ContactData->Ruts->List->data  as $key => $irut)
           {
             $find_ruts = $find_ruts . "'". $irut->rut_cliente  ."',";
            
           }
         }
         else
         {
           $find_ruts = $find_ruts . "'". $ContactData->Ruts->List->data->rut_cliente ."',";
           
         }
         $find_ruts =$find_ruts = $find_ruts . "'0'";

         $busca_dir=0;
         $a_directions     = RNCPHP\DOS\Direccion::find("Organization.CustomFields.c.rut in ({$find_ruts})");
         
         
        $c_org_id =$obj_info_contact['Org_id'] ;
        //if (!empty($user->Organization->ID))
        if (!empty($c_org_id))
        {
          //$a_directions = $this->CI->Organization->getDirectionsByOrgId($user->Organization->ID);
          //$a_directions = $this->CI->Organization->getDirectionsByOrgId($c_org_id);

          if (count($a_directions) > 0)
          {
            foreach ($a_directions as $direction)
            {
              $a_tempSelect['ID']   = $direction->ID;
              $a_tempSelect['name'] = $direction->dir_envio;

              $a_address[]          = $a_tempSelect;
              
            }
          }
        }
        else
        {
          throw new \Exception("Su contacto no tiene organización asociada " .$c_org_id);
        }

        // Validar de Campo HH
        if (array_search('HH EQUIPO', $header) === false)
        {
          throw new \Exception("Cabecera de CSV no contiene el valor 'HH'");
        }

        // Valida que el CSV no esté vacío o mal formado
        if (!count($a_csv))
        {
          throw new \Exception("Archivo adjunto mal formado o vacío.");
        }

        if (array_search('Contador 1 B/N',$header) === false)
        {
          throw new \Exception("Cabecera de CSV no contiene el valor cont1_hh'");
        }

        $a_errors = array();
        $a_no_errors = array();
       
       
        foreach ($a_csv as $key => $line)
        {
          $a_temp['errors']        = array();
          $a_temp['hh']            = $line["HH EQUIPO"];
          $a_temp['counter_black'] = $line["Contador 1 B/N"];
          $a_temp['counter_color'] = $line["Contador 2 Color"];
          $a_temp['count_black']   = $line["Toner Negro"];
          $a_temp['count_cyan']    = $line["Toner Cyan"];
          $a_temp['count_magenta'] = $line["Toner Magenta"];
          $a_temp['count_yellow']  = $line["Toner Amarillo"];


          if (!is_numeric($a_temp['hh']))
          {
            $a_temp['errors'][] = "El valor de 'HH' no es numérico.  (".$a_temp["hh"].")";
          }

          if (!is_numeric($a_temp['counter_black']) and !empty($a_temp['counter_black']))
          {
            $a_temp['errors'][] = "El valor de 'contador 1' no es numérico.  (".$a_temp["counter_black"].")";
            $counter_black      = false;
          }
          else
          {
            $counter_black      = true;
          }

          if (!is_numeric($a_temp['counter_color']) and !empty($a_temp['counter_color']))
          {
            $a_temp['errors'][] = "El valor de 'contador 2' no es numérico.  (".$a_temp["counter_color"].")";
            $counter_color      = false;
          }
          else
          {
            $counter_color      = true;
          }

          if (!is_numeric($a_temp['count_black']) and !empty($a_temp['count_black']))
          {
            $a_temp['errors'][] = "El valor de 'Toner Negro' no es numérico.  (".$a_temp["count_black"].")";
            $count_black        = false;
          }
          else
          {
            $count_black = true;
          }


          if (!is_numeric($a_temp['count_cyan']) and !empty($a_temp['count_cyan']))
          {
            $a_temp['errors'][] = "El valor de 'Toner Cyan' no es numérico.  (".$a_temp["count_cyan"].")";
            $count_cyan = false;
          }
          else
          {
            $count_cyan = true;
          }

          if (!is_numeric($a_temp['count_magenta']) and !empty($a_temp['count_magenta']))
          {
            $a_temp['errors'][] = "El valor de 'Toner Magenta' no es numérico.  (".$a_temp["count_magenta"].")";
            $count_magenta = false;
          }
          else
          {
            $count_magenta = true;
          }

          if (!is_numeric($a_temp['count_yellow']) and !empty($a_temp['count_yellow']))
          {
            $a_temp['errors'][] = "El valor de 'Toner Amarillo' no es numérico.  (".$a_temp["count_yellow"].")";
            $count_yellow = false;
          }
          else
          {
            $count_yellow = true;
          }

          if($counter_black === true && $counter_color === true)
          {
            $cc_black = (int) $a_temp['counter_black'];
            $cc_color = (int) $a_temp['counter_color'];

            if(($cc_black + $cc_color) < 1)
            {
              $a_temp['errors'][] = "Al menos uno de los contadores de B/N y Color deben ser mayores que cero.";
            }
          }

          if($count_black === true && $count_cyan === true && $count_magenta === true && $count_yellow === true)
          {
            $c_black    = (int) $a_temp['count_black'];
            $c_cyan     = (int) $a_temp['count_cyan'];
            $c_magenta  = (int) $a_temp['count_magenta'];
            $c_yellow   = (int) $a_temp['count_yellow'];

            if(($c_black + $c_cyan + $c_magenta + $c_yellow) < 1)
            {
              $a_temp['errors'][] = "Debe solicitar al menos un toner para continuar.";
            }
          }

          if (
            intval($a_temp['count_black'])   > intval(RNCPHP\Configuration::fetch(CUSTOM_CFG_MAX_QTY_ITEM_MULTIPLE)->Value) ||
            intval($a_temp['count_cyan'])    > intval(RNCPHP\Configuration::fetch(CUSTOM_CFG_MAX_QTY_ITEM_MULTIPLE)->Value) ||
            intval($a_temp['count_magenta']) > intval(RNCPHP\Configuration::fetch(CUSTOM_CFG_MAX_QTY_ITEM_MULTIPLE)->Value) ||
            intval($a_temp['count_yellow'])  > intval(RNCPHP\Configuration::fetch(CUSTOM_CFG_MAX_QTY_ITEM_MULTIPLE)->Value)
          )
          {
            $a_temp['errors'][] = 'La cantidad máxima de insumos es \'' . strval(RNCPHP\Configuration::fetch(CUSTOM_CFG_MAX_QTY_ITEM_MULTIPLE)->Value) . '\', realice la corrección y suba el CSV nuevamente.';
          }

          if (count($a_temp['errors']) > 0)
          {
            $a_errors[] = $a_temp;

            //continue;
          }

          $responseService = $this->CI->DatosHH->getDatosHHInsumos($a_temp['hh']);

          if ($responseService === false)
          {
            $a_temp['errors'][] = $this->CI->DatosHH->getLastError();
            $a_errors[]         = $a_temp;

            //continue;
          }
          else
          {
            $a_response_pre = json_decode($responseService, true);

            if (array_key_exists ('respuesta', $a_response_pre) !== true)
            {
              $a_temp['errors'][] = "Respuesta no esperada desde servicio.";
              $a_errors[] = $a_temp;
              //continue;
            }

            $a_response = $a_response_pre["respuesta"];

            if ($a_response["resultado"] !== "OK")
            {
              $a_temp['errors'][] = "Número de HH no identificado en Dimacofi";
            }

            // if ($a_response["Convenio"] === false or $a_response["convenio_insumos"] === false)
            // {
            //   $a_temp['errors'][] = "La HH no tiene convenio de insumos.";
            // }
            //
            // if ($a_response["Direccion"]["Bloqueado"] === true)
            // {
            //   $a_temp['errors'][] = "Contrato registra deuda.";
            // }

            // Lógica para validar que pertenece a la organización
            $dirId    = $a_response["Direccion"]["ID_direccion"];
            $CI = get_instance();
            $obj_info_contact= $CI->session->getSessionData('info_contact');
            $c_org_id = $obj_info_contact['Org_id'] ;
            //$c_org_id = $user->Organization->ID;
            //$obj_dir  = $this->CI->Organization->getDirectionByEbsId($dirId);
            if($a_response["TipoContrato"]=="Cargo")
            {
              $a_temp['errors'][] = "HH Sin Contrato asociado. " . $a_temp['hh'] . "-" . $a_response["Direccion"]["TipoContrato"];
            }
            else
            {
              if($a_response["Bloqueado"]=='true')
              {
                $a_temp['errors'][] = "Dierccion  HH Bloqueada por credito. " . $a_response["Direccion"]["Direccion"];
                
              }
            }

            



            $id_dir_selected=0;
            if ($a_directions != false)
            {
              //echo "ID ORG Contacto ". $c_org_id ."ID Dirección ". $obj_dir->ID ." ID ORG HH ". $obj_dir->Organization->ID;
              $id_dir_selected=0;
              foreach($a_directions as $dir)
              {
                  if($dir->d_id==$dirId)
                  {
                    $id_dir_selected=$dir->ID;
                  }
              }
            /*  if ($obj_dir->Organization->ID !== $c_org_id)
              {
                $a_temp['errors'][] = "La HH no figura a la organización asociada a su contrato.";
              }
              */
            }
            else
            {
              $a_temp['errors'][] = "La dirección asociada no figura en el sistema, favor comunicarse con los administradores " ;
            }

            if($id_dir_selected==0)
            {
              $a_temp['errors'][] = "EL HH " . $a_temp['hh'] . " no pertenece a la empresa  asosciada a su contacto " ;
            }
            if (count($a_temp['errors']) > 0)
            {
              $a_errors[] = $a_temp;
              //continue;
            }

            // Exitoso
            if (count($a_temp['errors']) == 0)
            {
            $a_temp['id_dir_selected'] = $id_dir_selected;
            $a_temp['info_service']    = $a_response;

            $a_temp['Bloqueado']=$a_response['Direccion']['Bloqueado'];

            $a_no_errors[]             = $a_temp;
            }
           
          }

        }

        $response = new \stdClass;
        $response->Contact=json_encode($ContactData->Ruts->List->data);
        $response->ruts = $find_ruts;
        $response->success   = true;
        $response->address   = $a_address;
        $response->errors    = $a_errors;
        $response->no_errors = $a_no_errors;
        
        if (count($a_errors) < 1)
          $response->message   = "Solicitud de insumos analizada sin errores." . $hhs;
        else
          $response->message   = "Solicitud de insumos analizada con errores, favor revisar la tabla.";

        // Exponiendo la respuesta
        echo json_encode($response);
      }
      catch (\Exception $e)
      {
        $response          = new \stdClass;
        $response->success = false;
        $response->message = $e->getMessage();
        echo json_encode($response);
      }
    }

    /**
     * Crea las solicitudes asociadas
     *
     * @param array $params Get / Post parameters
     */
    function handle_requestIncident_ajax_endpoint($params)
    {
      header('Content-Type: application/json');
      try
      {
        // Parámetros
        $data = json_decode($params['data']);
        $c_id     = $this->CI->session->getProfile()->c_id->value;
        $a_items =  $data->lines_items;
        
        if (count($a_items) < 1)
          throw new \Exception("No se puede ejecutar una solicitud sin ítems.");

        \RightNow\Connect\v1_3\ConnectAPI::commit();
        //TODO: Crear Inciente Padre - Insumos Múltiples
        $fatherResponse = $this->CI->Supplier->createFatherIncident($c_id);

        
        if ($fatherResponse === false)
          throw new \Exception("Error al crear incidente masivo ".$this->CI->Supplier->getLastError());


        foreach ($a_items as $item)
        {
          $obj_dir = $this->CI->Organization->getDirectionById($item->id_dir_selected);
          if (empty($obj_dir))
          {
            \RightNow\Connect\v1_3\ConnectAPI::rollback();
            throw new \Exception("Debe seleccionarse una dirección");
          }

          //TODO: Verificar HH existe, si no crearla
          //TODO: Verificar Relación de HH - Equipo- Insumos con Insumos, si no esta crearla
          $a_infohh['hh']        = $item->hh;
          $a_infohh['brand_hh']  = $item->info_service->Marca;
          $a_infohh['model_hh']  = $item->info_service->Modelo;
          $a_infohh['serial_hh'] = $item->info_service->Serie;
          $inventoryItemId       = $item->info_service->inventory_item_id;
          $a_suppliers           = $item->info_service->suppliers;

          $obj_hh  = $this->CI->Supplier->updateAsset($a_infohh, $obj_dir, $obj_contact, $inventoryItemId, $a_suppliers);

          if ($obj_hh !== false)
          {
            $cont1_hh        = (int) $item->counter_black;
            $cont2_hh        = (int) $item->counter_color;
            $quantityBlack   = (int) $item->count_black;
            $quantityCyan    = (int) $item->count_cyan;
            $quantityYellow  = (int) $item->count_yellow;
            $quantityMagenta = (int) $item->count_magenta;
            $quantityColor   = $quantityCyan + $quantityYellow + $quantityMagenta;

            //TODO: Obtener Sugeridos
            $a_response_suggested  = $this->CI->Supplier->getSuggested($obj_hh, $cont1_hh, $cont2_hh, $quantityBlack, $quantityColor,$quantityCyan, $quantityYellow, $quantityMagenta,$Actual,$Ultimo);
         
            if ($a_response_suggested === false)
            {
              //$message = "Error obteniendo sugeridos, se sugiere lo minimo ". $this->CI->Supplier->getLastError();
            
              \RightNow\Connect\v1_3\ConnectAPI::rollback();
              throw new \Exception("Error obteniendo sugeridos, se sugiere lo mínimo. ". $this->CI->Supplier->getLastError());
            }
            else
            {
              $message             = $a_response_suggested['message'];
              $message_black       = $a_response_suggested['message_black'];
              $message_color       = $a_response_suggested['message_color'];
              $a_supplierSuggested = $a_response_suggested['supplier'];
            }

            if ($a_supplierSuggested > 0)
            {
              $isBlack   = 0;
              $isCyan    = 0;
              $isYellow  = 0;
              $isMagenta = 0;
              $isNot     = 0;

              $a_lines = array();
              foreach ($a_supplierSuggested as $supplier)
              {
                //TODO: Ronny: Intervenir para seter el valor correccto según tipo de toner

                $tonerTypeId = $supplier['toner_type'];

                switch ($tonerTypeId) {
                  case 1: //Cyan
                    if($isCyan == 0)
                    {
                      $resultCL = $this->CI->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $quantityCyan,$supplier['Consumption']);
                      $isCyan   = 1;
                    }
                    break;
                  case 2: //Yellow
                    if($isYellow==0)
                    {
                      $resultCL = $this->CI->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $quantityYellow,$supplier['Consumption']);
                      $isYellow = 1;
                    }
                    break;
                  case 3: //Magenta
                    if($isMagenta==0)
                    {
                      $resultCL = $this->CI->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $quantityMagenta,$supplier['Consumption']);
                      $isMagenta = 1;
                    }
                    
                    break;
                  case 4: //Black
                    if($isBlack==0)
                    {
                      $resultCL = $this->CI->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $quantityBlack,$supplier['Consumption']);
                      $isBlack  = 1;
                    }
                    
                    break;
                  default:
                    if($isNot ==0)
                    {
                      $resultCL = $this->CI->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $supplier['quantity'],$supplier['Consumption']);
                      $isNot = 1;
                    }
                    
                    break;
                }



                if ($resultCL === false)
                {
                  \RightNow\Connect\v1_3\ConnectAPI::rollback();
                  throw new \Exception("Error creando linea ".$this->CI->Supplier->getLastError());
                }
                else {
                  $a_lines[] = $resultCL;
                }
              }



              $a_infohh['contador_bn']    = $cont1_hh;
              $a_infohh['contador_color'] = $cont2_hh;

              //Datos de HH
              $a_infohh['client_covenant']   = $item->info_service->Convenio;
              $a_infohh['client_blocked']    = $item->info_service->Direccion->Bloqueado;
              $a_infohh['contract_type']     = $item->info_service->TipoContrato;
              $a_infohh['sla_hh_rsn']        = $item->info_service->sla_hh_rsn;
              $a_infohh['delfos']            = $item->info_service->delfos;
              $a_infohh['machine_serial']    = $item->info_service->inventory_item_id;
              $a_infohh['supplier_covenant'] = $item->info_service->convenio_insumos;
              $a_infohh['brackets_covenant'] = $item->info_service->convenio_corchetes;

              // TODO: crear Incidente, con sus líneas
              // TODO: Asociar Incidente hijo con su padre
              $result = $this->CI->Supplier->createTicketMassive($c_id, $obj_hh, $a_infohh, $obj_dir, $fatherResponse->ID);
              if ($result === false)
              {
                \RightNow\Connect\v1_3\ConnectAPI::rollback();
                throw new \Exception("Error creando solicitud para HH {$a_infohh['hh']}:  ".$this->CI->Supplier->getLastError());
              }
              else
              {
                $incidentId    = $result->ID;
                $incidents_id .= ",".$incidentId;

                foreach ($a_lines as $lineId)
                {
                  $resultAL  = $this->CI->Supplier->assocLineToIncident($incidentId, $lineId);



                  if ($resultAL === false)
                  {
                    \RightNow\Connect\v1_3\ConnectAPI::rollback();

                    throw new \Exception("Error al esociar línea a incidente ".$this->CI->Supplier->getLastError());
                  }
                }

                //Asignación de mensajes
                if (!empty($message))
                  $this->CI->IncidentGeneral->insertPrivateNote($incidentId, $message);
                if (!empty($message_black))
                  $this->CI->IncidentGeneral->insertPrivateNote($incidentId, $message_black);
                if (!empty($message_color))
                  $this->CI->IncidentGeneral->insertPrivateNote($incidentId, $message_color);
              }
            }
          }
          else
          {
            
           
            \RightNow\Connect\v1_3\ConnectAPI::rollback();
            throw new \Exception("Error al obtener producto de HH: ".$this->CI->Supplier->getLastError());
          }
        }
        
        $this->CI->Supplier->processFatherIncident($fatherResponse->ID);

        $response                 = new \stdClass;
        $response->success        = true;
        $response->incident_id    = $fatherResponse->ID;
        $response->incident_refNo = $fatherResponse->ReferenceNumber;
        $response->incidents_son  = $incidents_id;
        $response->message        = "solicitud de insumos analizada sin errores.";

        // Exponiendo la respuesta
        echo json_encode($response);

      }
      catch (\Exception $e)
      {
        $response          = new \stdClass;
        $response->success = false;
        $response->message = $e->getMessage();

        echo json_encode($response);
      }
    }

}