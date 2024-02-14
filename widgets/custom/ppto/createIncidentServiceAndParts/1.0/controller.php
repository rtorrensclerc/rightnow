<?php
namespace Custom\Widgets\ppto;
require_once(get_cfg_var('doc_root').'/include/ConnectPHP/Connect_init.phph');
use RightNow\Connect\v1_3 as RNCPHP;

class createIncidentServiceAndParts extends \RightNow\Libraries\Widget\Base {

    CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    // CONST URL_GET_HH   = "http://movil.dimacofi.cl/dts/rn_integracion/rntelejson.php";
    //CONST URL_GET_HH   = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";

    function __construct($attrs) {
        parent::__construct($attrs);
        $this->CI->load->model('custom/GeneralServices');
    }

    function getData()
    {
        $op_id = $_REQUEST['p_id'];
        if (empty($op_id))
        {
          $this->data['message'] = "Problemas en la recepción del ID de PPTO";
          return parent::getData();
        }

        if (!empty($_POST['enviar']))
        {
          if ($this->isRequiredCreateIncident($op_id) === true)
            $this->createIncidentService($op_id);
          else
            $this->data['message'] = "EL PPTO ya tiene asociado un incidente de visita!! <br>";
        }
        else {
          $this->data['message'] = "Haga click en Botón 'Crear Incidente' para generar y asociar el incidente de visita";
        }

        return parent::getData();
    }

    private function isRequiredCreateIncident($op_id)
    {
      $opportunity      = RNCPHP\Opportunity::fetch($op_id);
      if (!empty($opportunity->CustomFields->OP->IncidentReparation ) and !empty($opportunity->CustomFields->OP->IncidentService))
      {
        return false;
      }
      else {
        return true;
      }
    }

    private function createIncidentService($op_id)
    {
      try
      {
        RNCPHP\ConnectAPI::commit();
        $opportunity      = RNCPHP\Opportunity::fetch($op_id);
        $a_items          = RNCPHP\OP\OrderItems::find("Opportunity.ID = {$opportunity->ID} and Enabled = 1");
        $flag_parts_items = false;
        foreach ($a_items as $item) {
          if ($item->Product->CategoryItem->ID === 2)
          {
            $flag_parts_items = true;
            break;
          }
        }
        $asset                          = RNCPHP\Asset::first("SerialNumber ='" . $opportunity->CustomFields->c->id_hh  ."'" );

        //Crear Ticket de Repuestos y Servicio
        if ($flag_parts_items === true)
        {
          //Creación Incidente de Servicio
          $incident                         = new RNCPHP\Incident();
          $incident->PrimaryContact         = $opportunity->PrimaryContact->Contact;
          $incident->CustomFields->c->id_hh = $opportunity->CustomFields->c->id_hh;
          $incident->Category               = RNCPHP\ServiceCategory::fetch(11); //codigo de servicio
          $incident->Disposition            = RNCPHP\ServiceDisposition::fetch(25); //servicio Técnico
          $incident->Asset=$asset;
          $incident->Subject                = "Incidente de visita - Cotizador";
          $incident->Save();

          //Servicio HH
          $incident->StatusWithType->Status->ID = 134; // Solicitud de Información HH
          $incident->Save();

          //Avance Evaluación del Cliente
          $incident->StatusWithType->Status->ID         = 129; // Información Validada
          $incident->CustomFields->c->cliente_bloqueado = false; //saltarse la evaluación de credito
          $incident->Save();

          //Aprobado por Credito
          $incident->StatusWithType->Status->ID         = 103; // Aprobado por Credito
          $incident->Save();

          //Visita Aceptada
          $incident->StatusWithType->Status->ID         = 161; // Visita Aceptada
          $incident->Save();



          //Solicitud de Cotización para pedir Repuestos
          $incident->StatusWithType->Status->ID               = 105; // Solicitud de Cotización
          $incident->CustomFields->c->seguimiento_tecnico->ID = 24; //Visita Cargo PPTO
          $incident->Save();


          //Creación Ticket de Repuestos
          $fatherRefNo                                         = $incident->ReferenceNumber;
          $incidentR                                           = new RNCPHP\Incident();
          $incidentR->Subject                                  = "Solicitud de Reparación de ".$fatherRefNo;
          $incidentR->Disposition                              = RNCPHP\ServiceDisposition::fetch(47); //Repuestos Cargo
          $incidentR->CustomFields->OP->Incident               = RNCPHP\incident::fetch($fatherRefNo);
          $incidentR->PrimaryContact                           = RNCPHP\Contact::fetch(94586); //Torrens por ahora
          //$incidentR->AssignedTo->Account                      = RNCPHP\Account::fetch($idTecnico);
          $incidentR->CustomFields->c->request_date_om         = time();
          $incidentR->CustomFields->c->source_ticket_parts->ID = 70;
          $incidentR->Save(RNCPHP\RNObject::SuppressExternalEvents);

          //Asignar Técnico Y direccion
          $res = $this->getInfoHH($incident->CustomFields->c->id_hh);
          if($res === false)
          {
            RNCPHP\ConnectAPI::rollback();
            return;
          }
          else
          {
            $obj_tecnico   = RNCPHP\Account::first("CustomFields.c.resource_id = ". $res['id_tecnico']);
            $obj_dirección = RNCPHP\DOS\Direccion::first('d_id = '. $res['id_dir']);

            if (!is_object($obj_tecnico))
            {
              $this->data['message'] = "Error: ID de tecnico no encontrada en RN";
              RNCPHP\ConnectAPI::rollback();
              return;
            }

            if (!is_object($obj_dirección))
            {
              $this->data['message'] = "Error: ID de dirección no encontrada en RN";
              RNCPHP\ConnectAPI::rollback();
              return;
            }

            $idTecnico = $obj_tecnico->ID;;
          }
          //Asignando al Incidente Padre
          $incident->AssignedTo->Account                      = RNCPHP\Account::fetch($idTecnico);
          $incident->Save(RNCPHP\RNObject::SuppressAll);

          //Asignando al Ticket de Repuestos
          $Account = RNCPHP\Account::fetch($idTecnico);
          $incidentR->AssignedTo->Account                     = $Account;

          //Asignando contacto de cuenta
          $this->CI->load->model('custom/AccountLogin');
          $contact = $this->CI->AccountLogin->AssignContactToAccount($Account->Login);
          if ($contact === false)
          {
            $this->data['message'] = $this->CI->AccountLogin->getError();
            RNCPHP\ConnectAPI::rollback();
            return;
          }

         

          $incidentR->Asset=$asset;
          $incidentR->PrimaryContact = $contact;
          $incidentR->Save(RNCPHP\RNObject::SuppressAll);

          //Asociar lineas de producto
          $a_items = RNCPHP\OP\OrderItems::find("Opportunity.ID = {$opportunity->ID} and Enabled = 1");
          foreach ($a_items as $item) {
            $item->Incident = $incidentR;
            $item->Save();
          }

          //Estado Cotización
          $incidentR->StatusWithType->Status->ID               = 181; // Solicitud de Cotización
          $incidentR->Save();

          if (empty($incident->ID))
          {
            $this->data['message'] = "Error: ID de ticket de servicio no encontrada";
            RNCPHP\ConnectAPI::rollback();
            return;
          }

          if (empty($incidentR->ID))
          {
            $this->data['message'] = "Error: ID de solicitud de Repuestos no encontrada";
            RNCPHP\ConnectAPI::rollback();
            return;
          }

          //Se asocia el Incidente de Reparación a la Oportunidad
          $opportunity->CustomFields->OP->IncidentReparation = RNCPHP\Incident::fetch($incidentR->ID);
          $opportunity->CustomFields->OP->IncidentService    = RNCPHP\Incident::fetch($incident->ID);
          $opportunity->StatusWithType->Status->ID           = 180; // Estado en confección
          $opportunity->Save();

          $this->data['message'] = "Se crearon los tickets de Servicio y Repuestos exitosamente <br> Favor Refrescar el espacio de trabajo para visualizar los cambios";
        }
        else
        {
          $this->data['message'] = "EL PPTO no contiene Repuestos, por lo que no es necesario generar ticket de visita <br>";
        }


         /* Valida Bloqueo */


         $Blocked = $this->CI->GeneralServices->getOrganizationStatusbyRut($incident->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut);
         if($Blocked->Customer->CustomerData->Customer->tBLOQUEADO=='SI')
         {
          
           $incident->Subject                = "Incidente de visita - Cotizador 1";
             $incident->CustomFields->c->cliente_bloqueado=1;
         }
         else
         {
           $incident->Subject                = "Incidente de visita - Cotizador 0";
             $incident->CustomFields->c->cliente_bloqueado=0;
         }
         $incident->Save(RNCPHP\RNObject::SuppressAll);
         
      }
      catch (RNCPHP\ConnectAPIError $err){
          $this->data['message'] = $err->getMessage();
          RNCPHP\ConnectAPI::rollback();
      }
    }

    private function getInfoHH($hh)
    {
      $HH = $hh;
      $this->CI->load->library('Blowfish', false); //carga Libreria de Blowfish

      $array_post     = array('usuario' => 'appmind',
                              'accion' => 'info_hh',
                              'datos'=> array('id_hh'=> $HH)
                              );

      $json_data_post = json_encode($array_post);


      $json_data_post = $this->CI->blowfish->encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
      $json_data_post = base64_encode($json_data_post);

      $postArray = array ('data' => $json_data_post);
      $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL

    
      $result = $this->requestCURLByPost($cfg->Value, $postArray);
      

      if ($result === false)
      {
        return false;
      }
      else
      {
        $result = json_decode($result, true);
        if (!empty($result['respuesta']) and ($result['resultado'] == "true"))
        {
          $respuesta     = base64_decode($result['respuesta']);
          $json_hh       = $this->CI->blowfish->decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
          $array_hh_data = json_decode(utf8_encode($json_hh), true);



          if (!is_array($array_hh_data))
          {
              $this->data['message'] = "ERROR: Estructura JSON encriptado No valida ".PHP_EOL;
              $this->data['message'] .= "JSON: ".$json_hh;
              return false;
          }

          $array_hh_data      = $array_hh_data['respuesta'];
          $array_tecnico      = $array_hh_data['Tecnico'];
          $array_hh_direccion = $array_hh_data['Direccion'];
          $indiceIdTecnico    = 'ID_IBS';
          $indiceIdDireccion  = 'ID_direccion';

          if (array_key_exists($indiceIdTecnico, $array_tecnico) and $array_tecnico[$indiceIdTecnico] == "-1" and !empty($array_tecnico[$indiceIdTecnico]))
          {
              $this->data['message'] = "No se pudo ingresar el técnico, puesto que por WS viene vacio";
              return false;
          }

          if (array_key_exists($indiceIdDireccion, $array_hh_direccion) and $array_hh_direccion[$indiceIdDireccion ] == "-1" and !empty($array_hh_direccion[$indiceIdDireccion]))
          {
              $this->data['message'] = "No se pudo ingresar el técnico, puesto que por WS viene sin direccion asociada";
              return false;
          }



          $a_result['id_dir']     = $array_hh_direccion['ID_direccion'];
          $a_result['id_tecnico'] = $array_tecnico['ID_IBS'];

          return $a_result;
        }
        else
        {
          $this->data['message'] = "Error: Estructura del servicio no esperada";
          return false;
        }
      }
    }

    private function requestCURLByPost($url, $postArray)
    {
        # Form data string
        if (is_array($postArray))
            $postString = http_build_query($postArray, '', '&');

        load_curl();
        $ch = curl_init($url);

        # Setting our options

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 500);
        //curl_setopt($ch, 156, 500);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        # Get the response
        $response = curl_exec($ch);

        if(curl_errno($ch))
        {
            $info = curl_getinfo($ch);
            $this->data['message']= curl_error($ch);
            //$this->data['message'].='<br>Tiempo ' . $info['total_time'] . ' segundos en recibir la respuesta de la siguiente URL: ' . $info['url'];
            curl_close($ch);
            return false;
        }

        if ($response != false)
        {
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($statusCode != '200')
            {
                $this->data['message']= 'No se pudo resolver la petición a la URL, codigo de Error: '. $statusCode;
                return false;
            }
            else
                return $response;
        }
        else
        {
            curl_close($ch);
            $this->data['message']= 'No se pudo resolver la petición a la URL';
            return false;
        }
    }

}
