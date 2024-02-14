<?php

namespace Custom\Controllers;
use RightNow\Connect\v1_2 as RNCPHP;
class Tickets extends \RightNow\Controllers\Base
{
    CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    CONST USER = "UserDimacofi";
    CONST ACCION = "getTicketsByTecnico";
    //CONST ACCION_2 = "getTicketsByTecnico";
    public $responseEncripted = false;
    protected $typeFormat = 'json';

    function __construct()
    {
        parent::__construct();
        $this->load->library('Blowfish', false); //carga Libreria de Blowfish
        $this->load->model('custom/ws/TecnicosModel'); //Modelo para acceder a tecnicos y modelo
        $this->load->model('custom/ws/DatosHH');
    }

    public function CopyTicket()
    {
        $incident_o='221122-000451';
        $incident_old= RNCPHP\Incident::fetch( $incident_o);
    /* CON HH BUSCAMOS TODOS LOS DATOS NECESARIOS */
    $info_HH                          = RNCPHP\Asset::first("SerialNumber ='" . 3265544  ."'" );
   
    //return $info_HH->CustomFields->DOS->Product;
    //return $info_HH->CustomFields->DOS->Direccion->d_id;

   
    try
    {
      $incident                                              = new RNCPHP\Incident();
      $incident->PrimaryContact                              = $incident_old->PrimaryContact;
      $incident->CustomFields->c->tipificacion_sugerida->ID  = 221;  /* Mantencion */
      //$incident->Subject                                     = "SOLICITUD ASISTENCIA TÉCNICA - ESPECIAL - ". strtoupper($incident->CustomFields->c->tipificacion_sugerida->LookupName);
      $incident->Subject                                     = $incident_old->Subject;
      $incident->Product                                     = $incident_old->Product;
      $incident->Category                                    = $incident_old->Category;
      $incident->Disposition                                 = RNCPHP\ServiceDisposition::fetch(25); // Soporte Especial
      $incident->StatusWithType                              = new RNCPHP\StatusWithType() ;
      $incident->StatusWithType->Status                      = new RNCPHP\NamedIDOptList() ;
      $incident->StatusWithType->Status->ID                  = 162 ;  // asignado a tecnico
      

      //$incident->CustomFields->c->diagnostico->ID            = $incident_old->CustomFields->c->diagnostico->ID;
      //$incident->CustomFields->c->motivo_solucion->ID        = $incident_old->CustomFields->c->motivo_solucion->ID;
      $incident->CustomFields->c->tipo->ID                   = $incident_old->CustomFields->c->tipo->ID;
      $incident->CustomFields->c->seguimiento_tecnico    = $incident_old->CustomFields->c->seguimiento_tecnico;
      $incident->CustomFields->c->seguimiento_tecnico->ID    = 15;
      $incident->CustomFields->c->soporte_telefonico         =false; // NO

      //Datos de SOLICITUD
      $incident->CustomFields->c->cont1_hh                   = $incident_old->CustomFields->c->cont1_hh;
      $incident->CustomFields->c->cont2_hh                   = $incident_old->CustomFields->c->cont2_hh;
      
      //Datos de HH
      $incident->CustomFields->c->id_hh                      = $incident_old->CustomFields->c->id_hh;
      $incident->AssignedTo->Account                         = $incident_old->AssignedTo->Account;
      
      $incident->CustomFields->c->marca_hh                = $incident_old->CustomFields->c->marca_hh;
      $incident->CustomFields->c->modelo_hh               = $incident_old->CustomFields->c->modelo_hh;
      $incident->CustomFields->c->convenio                = $incident_old->CustomFields->c->convenio;
      $incident->CustomFields->c->tipo_contrato           = $incident_old->CustomFields->c->tipo_contrato;
      $incident->CustomFields->c->sla_hh                  = $incident_old->CustomFields->c->sla_hh;
      $incident->CustomFields->c->sla_hh_rsn              = $incident_old->CustomFields->c->sla_hh_rsn;
      $incident->CustomFields->c->cliente_bloqueado       = $incident_old->CustomFields->c->cliente_bloqueado;
      $incident->CustomFields->c->serie_maq               = $incident_old->CustomFields->c->serie_maq;
      $incident->CustomFields->c->numero_delfos           = $incident_old->CustomFields->c->numero_delfos;
      $incident->CustomFields->c->order_number_om_ref     = $incident_old->CustomFields->c->order_number_om_ref;
      $incident->CustomFields->c->shipping_instructions   = $incident_old->CustomFields->c->shipping_instructions;
      
      $incident->CustomFields->c->direccion_incorrecta    = $incident_old->CustomFields->c->direccion_incorrecta;
      $incident->CustomFields->c->direccion_correcta      = $incident_old->CustomFields->c->direccion_correcta;
      $incident->CustomFields->c->codigo_error            = $incident_old->CustomFields->c->codigo_error;
      $incident->CustomFields->c->equipo_detenido_cliente = $incident_old->CustomFields->c->equipo_detenido_cliente;
      $incident->CustomFields->DOS->Direccion             = $incident_old->CustomFields->DOS->Direccion;
      $incident->Asset                                    = $incident_old->Asset ;
      $incident->CustomFields->c->ar_flow                 = $incident_old->CustomFields->c->ar_flow;
      $incident->CustomFields->OP->Incident               = $incident_old;
      $incident->Save();
      echo json_encode($incident->ReferenceNumber);
      return $incident;

    }
    catch (RNCPHP\ConnectAPIError $err )
    {
      RNCPHP\ConnectAPI::rollback();
      $this->error['message']  = "'createTechAssistanceTicket'. Código : " . $err->getCode() . " " . $err->getMessage() . " Línea: " . $err->getLine();
      $this->error['numberID'] = 1;
      echo $this->error['message'] ;
      return FALSE;
    }
}

public function createTicket()
{
  $this->load->model('custom/ws/DatosHH'); // Modelo
  $this->load->model('custom/TechAssistance');
  $a_temp['errors'] = array();
  $a_lines          = array();
  $this->load->model("custom/ws/DatosHH");
  $json_data = file_get_contents('php://input');
  /*
    $json_data='
    {
    "HH" :"3276579",
    "ID": 140112,
    "TYPE": 270,
    "STOPPPED":true
    }';

*/

  $data                       = json_decode($json_data);
  //echo json_encode($data). '<br>';
  $a_temp['errors']['status'] = 0;
  $contactId=$data->ID;
  //$id_ticket='200520-000000';
  //$incident=RNCPHP\Incident::fetch($id_ticket);

  //$user=RNCPHP\Contact::fetch(104535);
  //echo "->>". $data->STOPPPED;
  $obj_contact                                   = RNCPHP\Contact::fetch($contactId);

  //$this->sendResponse(json_encode($user->Organization));;
  //echo json_encode($user);
  $a_address = array();
  if (empty($obj_contact->Organization->ID)) {
  //throw new \Exception("Su contacto no tiene organización asociada");
   $a_temp['errors']['error']  = "Contacto  " . $contactId . " no existe o no tiene organización asociada HH " . $data->HH;
   $a_temp['errors']['status'] = 17;
  }



  

  $respuesta=$this->DatosHH->getDatosHHInsumos($data->HH);
  $contactId=$user->ID;
  //echo $respuesta;
  $datoshh= json_decode($respuesta);
  $infohh=$datoshh->respuesta;


  $incident                                      = new RNCPHP\Incident();
  $incident->PrimaryContact                      = $obj_contact;
  $incident->CustomFields->c->tipificacion_sugerida->ID  =$data->TYPE;
  $incident->Subject                                     = "SOLICITUD ASISTENCIA TÉCNICA";
  $incident->Product                                     = RNCPHP\ServiceProduct::fetch(68); // Solicitud
  $incident->Category                                    = RNCPHP\ServiceCategory::fetch(34); // Asistencia Técnica
  $incident->Disposition                                 = RNCPHP\ServiceDisposition::fetch(25); // Soporte Técnico
  $incident->StatusWithType                              = new RNCPHP\StatusWithType() ;
  $incident->StatusWithType->Status                      = new RNCPHP\NamedIDOptList() ;
  $incident->StatusWithType->Status->ID                  = 129 ;
  //Datos de HH
  $incident->CustomFields->c->id_hh                   = $infohh->ID_HH;
  $incident->CustomFields->c->marca_hh                = $infohh->Marca;
  $incident->CustomFields->c->modelo_hh               = $infohh->Modelo ;
  $incident->CustomFields->c->convenio                = $infohh->Convenio ;
  $incident->CustomFields->c->sla_hh                  = $infohh->SLA;
  $incident->CustomFields->c->sla_hh_rsn              = $infohh->RSN ;
  $incident->CustomFields->c->cliente_bloqueado       = $infohh->Direccion->Bloqueado;
  $incident->CustomFields->c->serie_maq               = $infohh->Serie;
  $incident->CustomFields->c->numero_delfos           = $infohh->delfos;
  $incident->CustomFields->c->shipping_instructions   = substr($a_contactInfo['name'] . " " . $a_contactInfo['phone'] . " " . $a_contactInfo['email'], 0, 254);
  
  $incident->CustomFields->c->direccion_incorrecta    = False;
  $incident->CustomFields->c->direccion_correcta      = '';
  
  $incident->CustomFields->c->solution_type =  $infohh->trx_id_erp;
  
  $incident->CustomFields->c->codigo_error            = $infohh->codigo_error;
  if($data->STOPPPED)
  {
    $incident->CustomFields->c->equipo_detenido_cliente = True;

  }
  else
  {
    $incident->CustomFields->c->equipo_detenido_cliente = False;

  }
  $dirId = $infohh->Direccion->ID_direccion;

  $array_Direccion_obj                           = RNCPHP\DOS\Direccion::find('d_id = '. $dirId);
  if (is_array($array_Direccion_obj) and is_object($array_Direccion_obj[0]))
      $incident->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
  $incident->save(RNCPHP\RNObject::SuppressAll);
  $incident->Subject                                     = "SOLICITUD ASISTENCIA TÉCNICA - WSP";
  // Crear Asociar Asset.
  if ($this->TechAssistance->updateAssistanceAsset($incident) === FALSE) // Creación del activo
  {
      RNCPHP\ConnectAPI::rollback();
      return FALSE;
  }
  $incident->Save();

  if (!empty($incident) and $a_temp['errors']['status'] == 0) {
    $respuesta = array('status' => "OK", 'ref_no' => $incident->ReferenceNumber, 'ID' => $incident->ID);
   } else {
 
    $respuesta = array('status' => "NOOK", 'ref_no' => $incident->ReferenceNumber, 'ID' => $incident->ID, 'Code' => $a_temp['errors']['status'], 'error' => $a_temp['errors']['error']);
   }
   //echo json_encode($respuesta) . "<br>";
   $this->sendResponse(json_encode($respuesta));


}

    public function CreateSpecialTicket()
    {
      $this->load->model('custom/TechAssistance');
      //$json_data = file_get_contents('php://input');
      $json_data= file_get_contents('php://input');
      

      if($json_data==null)
      {
        $data     ='{
            "usuario":"dtic",
            "accion":"CreateSpecialTicket",
            "datos":{ "hh":"2163527"}
            }';
        $array_post=json_decode($data);
      }
      else
      {    
        $array_post=json_decode($json_data);
      }
      /*
      );
      */

      //$incident= RNCPHP\Incident::fetch('200930-000002');
      //echo json_encode($incident->ReferenceNumber);
      //return;
      $a_hh=$array_post->datos->hh;
      $a_id_tecnico=$array_post->datos->id_tecnico;
     
      $incident=$this->TechAssistance->createSpecialTicket($a_hh,$a_id_tecnico);
      $obj_response                    = new \stdClass();
      $obj_response->HH=$a_hh;
  
      $obj_response->ReferenceNumber=$incident->ReferenceNumber;
      
      

      $this->sendResponse(json_encode($obj_response));
  
    }
    public function getByTecnico()
    {
        if (!empty($_POST))
        {
            $data_post = $this->getdataPOST();
            $json_data = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
            $array_data = json_decode(utf8_encode($json_data), true);


            if (is_array($array_data) and ($array_data != false))
            {
                $indiceDatos = 'datos';
                $indiceAccion = 'accion';
                $indiceUsuario = 'usuario';

                if (array_key_exists($indiceAccion, $array_data) and array_key_exists($indiceUsuario, $array_data) and array_key_exists( $indiceDatos, $array_data))
                {
                    $indiceTecnico = 'id_tecnico';

                    if (array_key_exists($indiceTecnico, $array_data[$indiceDatos]))
                    {
                        if ($array_data[$indiceUsuario] == self::USER)
                        {
                            if ($array_data[$indiceAccion] == self::ACCION)
                            {
                                if (is_numeric($array_data[$indiceDatos][$indiceTecnico]))
                                {
                                    $id_tecnico = (int) $array_data[$indiceDatos][$indiceTecnico];
                                    $array_incidents = $this->TecnicosModel->getTickets($id_tecnico);
                                    if ($array_incidents === false)
                                    {
                                        //echo "problema ".$this->TecnicosModel->getLastError();
                                        //die();
                                        $response = $this->responseError(4, $this->TecnicosModel->getLastError());
                                        $this->sendResponse($response);
                                    }
                                    else
                                    {
                                        $array_result = array('resultado' => true, 'respuesta' => array());
                                        $array_response = array("Tickets" => array());
                                        foreach ($array_incidents as $incident)
                                        {
                                            $array_incident = array('nro_referencia' => $incident->ReferenceNumber,
                                                                   'id_hh' => $incident->CustomFields->c->id_hh,
                                                                   'fecha_ingreso' => $incident->CreatedTime,
                                                                   'estado' => $incident->StatusWithType->Status->LookupName,
                                                                   'tipo_incidente' => $incident->Disposition->LookupName,
                                                                   'convenio' => $incident->CustomFields->c->convenio,
                                                                   //'disposicion' => $incident->Disposition->LookupName,
                                                                   'categoria' => $incident->Category->LookupName,
                                                                   'producto' =>  $incident->Product->LookupName,
                                                                   'dir_invalida' => $incident->CustomFields->c->direccion_incorrecta,
                                                                   'dir_correcta' => $incident->CustomFields->c->direccion_correcta,
																   'seguimiento_tecnico' => $incident->CustomFields->c->seguimiento_tecnico->LookupName
                                                                   );
                                            $array_response['Tickets'][] = $array_incident;
                                        }
                                        //INICIO - CON ENCRIPTACION

                                        $response = json_encode($array_response);
                                        $response = $this->blowfish->encrypt($response, self::KEY_BLOWFISH, 10, 22, NULL);
                                        $response = base64_encode($response);

                                        //FIN - CON ENCRIPTACION

                                        //INICIO -SIN ENCRIPTACION
                                        //$response = $array_response;
                                        //FIN:_ SIN ENCRIPTACION
                                        $array_result['respuesta'] = $response;
                                        $result = json_encode($array_result);
                                        $this->sendResponse($result);
                                    }

                                }
                                else
                                {
                                    $response = $this->responseError(7);
                                    $this->sendResponse($response);
                                }
                            }
                            else
                            {
                                $response = $this->responseError(6);
                                $this->sendResponse($response);
                            }
                        }
                        else
                        {
                            $response = $this->responseError(5);
                            $this->sendResponse($response);
                        }
                    }
                    else
                    {
                        $response = $this->responseError(3);
                        $this->sendResponse($response);
                    }
                }
                else
                {
                    $response = $this->responseError(3);
                    $this->sendResponse($response);
                }
            }
            else
            {
                $response = $this->responseError(2);
                $this->sendResponse($response);
            }
        }
        else
        {
            $response = $this->responseError(1);
            $this->sendResponse($response);
        }

    }

    private function getdataPOST()
    {
        $data = trim($_POST['data']);
        if (!empty($data)){
            $data_decode = base64_decode($data);
            //$data = utf8_encode($data);
            return $data_decode;
        }
        return false;
    }

    public function setCotizacion()
    {
        //$nameFunction = __FUNCTION__;
        $data_post  = $this->getdataPOST();
        $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
        $array_data = json_decode(utf8_encode($json_data), true);

        if (empty($_POST))
        {
            $response = $this->responseError(1);
            $this->sendResponse($response);
        }

        if (is_array($array_data) and ($array_data != false))
        {

            $indiceDatos = 'datos';
            $indiceAccion = 'accion';
            $indiceUsuario = 'usuario';

            if (!array_key_exists($indiceAccion, $array_data) and !array_key_exists($indiceUsuario, $array_data) and !array_key_exists( $indiceDatos, $array_data))
            {
                $response = $this->responseError(3);
                $this->sendResponse($response);
            }

            if ($array_data[$indiceUsuario] != self::USER)
            {
                $response = $this->responseError(5);
                $this->sendResponse($response);
            }

            if ($array_data[$indiceAccion] != __FUNCTION__ )
            {
                $response = $this->responseError(6);
                $this->sendResponse($response);
            }

            $indiceTicket = 'nro_referencia';
            $indiceCotizacion = 'id_cotizacion';
            $indiceTipo = 'tipo';

            if (is_numeric($array_data[$indiceDatos][$indiceCotizacion]))
            {
                //$params['nro_referencia'] = $array_data[$indiceDatos][$indiceTicket];
                $nro_referencia = $array_data[$indiceDatos][$indiceTicket];
                $id_cotizacion = $array_data[$indiceDatos][$indiceCotizacion];
                $tipo = $array_data[$indiceDatos][$indiceTipo];

                $this->load->model('custom/ws/TicketModel');   //libreria para tickets
                $obj_incident = $this->TicketModel->getObjectTicket($nro_referencia);
                if ($obj_incident == false)
                {
                    $response = $this->responseError(8);
                    $this->sendResponse($response);
                }

                $obj_cotiza = $this->TicketModel->setCotizacion($obj_incident, $id_cotizacion, $tipo);
                if ($obj_cotiza == false)
                {
                    $response = $this->responseError(4);
                    $this->sendResponse($response);
                }
                else
                {

                    $array_result = array('resultado' => true, 'respuesta' => array());
                    $array_response_true = array('nro_referencia' => $nro_referencia,
                                               'estado' => "cotizacion ingresada con exito",
                                               'id_cotizacion_rn' => $obj_cotiza->ID );

                    // Inicio - ALTERNATIVA ENCRIPTADA
                    $response = json_encode($array_response);
                    $response = $this->blowfish->encrypt($response, self::KEY_BLOWFISH, 10, 22, NULL);
                    $response = base64_encode($response);
                    $array_result['respuesta'] = $response;

                    // FIN - Alternativa Encritpada


                    /*
                    //Inicio -Alternativa SIN Encriptacion
                    $array_result['respuesta'] = $array_response_true;
                    //FIn -Alternativa SIN Encriptacion
                    */

                    $result = json_encode($array_result);
                    $this->sendResponse($result);

                }

            }
            else
            {
                $response = $this->responseError(7);
                $this->sendResponse($response);
            }
        }
        else
        {
            $response = $this->responseError(2);
            $this->sendResponse($response);
        }
    }

    private function responseError($type, $message = false)
    {

        $array_error = array ('resultado' => false, 'respuesta' => array(), 'POST' => $_POST['data']);
        $response = '';

        switch ($type) {
            case 1:
                $response =  array('Error' => 1, 'Glosa' => 'Solicitud Inesperada');
                break;
            case 2:
                $response =  array('Error' => 2, 'Glosa' => 'Cadena inesperada - Problemas en desencriptación');
                break;
            case 3:
                $response =  array('Error' => 3, 'Glosa' => 'Estructura no válida en la variable enviada');
                break;
            case 4:
                $response =  array('Error' => 4, 'Glosa' => (!empty($message)) ? $message :'Ha ocurrido un problema inesperado en la consulta'    );
                break;
            case 5:
                $response =  array('Error' => 5, 'Glosa' => 'Usuario Invalido');
                break;
            case 6:
                $response =  array('Error' => 6, 'Glosa' => 'Accion desconocida');
                break;
            case 7:
                $response =  array('Error' => 7, 'Glosa' => 'ID de tecnico no es de tipo numerico');
                break;
            case 8:
                $response =  array('Error' => 8, 'Glosa' => 'ID de ticket desconocido o no presente en Oracle RightNow');
                break;
            case 9:
                $response =  array('Error' => 9, 'Glosa' => 'ID de ticket no valido, no se encuentra en estado previo requerido');
                break;
            case 10:
                $response =  array('Error' => 7, 'Glosa' => 'Estado no es de tipo booleano');
                break;
            default:
                $response =  array('Error' => 1, 'Glosa' => 'Solicitud Inesperada');
                break;
        }



        if ($this->responseEncripted == true)
        {
            $response = $this->blowfish->encrypt(json_encode($response), self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
            $array_error['respuesta'] = base64_encode($response);
            $responseEncode = json_encode($array_error);
        }
        else
        {
            $array_error['respuesta'] = $response;
            $responseEncode = json_encode($array_error);
        }

        return $responseEncode;
    }

    private function sendResponse($response)
    {
        switch ($this->typeFormat) {
            case 'json':
                header('Content-Type: application/json');
                echo $response;
                break;
            default:
                header('Content-Type: application/json');
                echo $response;
                break;
        }
        die();
    }

    public function getTicketsCargoByDays()
    {
        $this->load->model('custom/ws/TicketsModel');

        $data_post  = $this->getdataPOST();
        $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
        $array_data = json_decode(utf8_encode($json_data), true);

        if (empty($_POST))
        {
            $response = $this->responseError(1);
            $this->sendResponse($response);
        }

        if (is_array($array_data) and ($array_data != false))
        {

            $indiceDatos = 'datos';
            $indiceAccion = 'accion';
            $indiceUsuario = 'usuario';

            if (!array_key_exists($indiceAccion, $array_data) and !array_key_exists($indiceUsuario, $array_data) and !array_key_exists( $indiceDatos, $array_data))
            {
                $response = $this->responseError(3);
                $this->sendResponse($response);
            }

            if ($array_data[$indiceUsuario] != self::USER)
            {
                $response = $this->responseError(5);
                $this->sendResponse($response);
            }

            if ($array_data[$indiceAccion] != __FUNCTION__ )
            {
                $response = $this->responseError(6);
                $this->sendResponse($response);
            }

            $indiceNroDias = 'nro_dias';



            if (is_numeric($array_data[$indiceDatos][$indiceNroDias]))
            {



                $days = $array_data[$indiceDatos][$indiceNroDias];
                //$date = time();

                $fecha = strtotime("-{$days} day"); // Se obtiene la fecha en UNIX, con los dias descontaoos
                $fecha = date("Y-m-d\TH:i:s.000\Z", $fecha ); // Se obtiene la fecha con el Formato que RightNow acepta para la connect y queries.

                $array_incidents = $this->TicketsModel->getTicketsCargobyDate($fecha);


                if ($array_incidents === false)
                {
                    $response = $this->responseError(4);
                    $this->sendResponse($response);
                }
                else
                {


                    $array_result = array('resultado' => true, 'respuesta' => array());
                    $array_response = array("Tickets" => array());
                    foreach ($array_incidents as $incident)
                    {
                        $array_incident = array("nro_Referencia" => $incident->ReferenceNumber,
                                                "id_hh" => $incident->CustomFields->c->id_hh,
                                                "fecha_ingreso" => $incident->CreatedTime,
                                                "id_tecnico"=> $incident->AssignedTo->Account->CustomFields->c->resource_id,
                                                "id_direccion" => $incident->CustomFields->DOS->Direccion->d_id,
                                                "estado" => $incident->StatusWithType->Status->LookupName,
                                                "subestado_tecnico" => $incident->CustomFields->c->seguimiento_tecnico->LookupName,
                                                "contacto" => array(
                                                    "nombre" => $incident->PrimaryContact->Name->First,
                                                    "apellido" => $incident->PrimaryContact->Name->Last,
                                                    "email" => $incident->PrimaryContact->Emails[0]->Address,
                                                    "telefono" => $incident->PrimaryContact->Phones[0]->Number
                                                )
                                               );
                        $array_response['Tickets'][] = $array_incident;
                    }

                    // Inicio - ALTERNATIVA ENCRIPTADA

                    $response = json_encode($array_response);
                    $response = $this->blowfish->encrypt($response, self::KEY_BLOWFISH, 10, 22, NULL);
                    $response = base64_encode($response);
                    $array_result['respuesta'] = $response;

                    // FIN - Alternativa Encritpada



                    //Inicio -Alternativa SIN Encriptacion
                    //$array_result['respuesta'] = $array_response;
                    //FIn -Alternativa SIN Encriptacion


                    $result = json_encode($array_result);
                    $this->sendResponse($result);

                }

            }
            else
            {
                $response = $this->responseError(7);
                $this->sendResponse($response);
            }
        }
        else
        {
            $response = $this->responseError(2);
            $this->sendResponse($response);
        }




        ///-----------------------------------------------------------------------------------

        /*
        $days = 6;
        //$date = time();

        $fecha = strtotime("-{$days} day"); // Se obtiene la fecha en UNIX, con los dias descontaoos
        $fecha = date("Y-m-d\TH:i:s.000\Z", $fecha ); // Se obtiene la fecha con el Formato que RightNow acepta para la connect y queries.

        $array_incidents = $this->TicketsModel->getTicketsCargobyDate($fecha);

        if ($array_incidents !== false) {
            echo "<pre";
            print_r($array_incidents);
            echo "</pre>";

        }
        else
            echo $this->TicketsModel->getLastError();
        */
    }

    public function setStateCotizacion()
    {
        $data_post  = $this->getdataPOST();
        $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
        $array_data = json_decode(utf8_encode($json_data), true);

        if (empty($_POST))
        {
            $response = $this->responseError(1);
            $this->sendResponse($response);
        }

        if (is_array($array_data) and ($array_data != false))
        {

            $indiceDatos = 'datos';
            $indiceAccion = 'accion';
            $indiceUsuario = 'usuario';

            if (!array_key_exists($indiceAccion, $array_data) and !array_key_exists($indiceUsuario, $array_data) and !array_key_exists( $indiceDatos, $array_data))
            {
                $response = $this->responseError(3);
                $this->sendResponse($response);
            }

            if ($array_data[$indiceUsuario] != self::USER)
            {
                $response = $this->responseError(5);
                $this->sendResponse($response);
            }

            if ($array_data[$indiceAccion] != __FUNCTION__ )
            {
                $response = $this->responseError(6);
                $this->sendResponse($response);
            }

            $indiceTicket = 'nro_referencia';
            $indiceEstado = 'estado';

            if (is_bool($array_data[$indiceDatos][$indiceEstado]))
            {

                $nro_referencia = $array_data[$indiceDatos][$indiceTicket];
                $bool_estado = $array_data[$indiceDatos][$indiceEstado];


                $this->load->model('custom/ws/TicketModel');   //libreria para tickets

                $obj_incident = $this->TicketModel->getObjectTicket($nro_referencia);
                if ($obj_incident == false)
                {
                    $response = $this->responseError(8);
                    $this->sendResponse($response);
                }

                if ($obj_incident->StatusWithType->Status->ID != 105) // Solicitud de Cotizacion
                {
                    $response = $this->responseError(9);
                    $this->sendResponse($response);
                }

                $obj_cotiza = $this->TicketModel->setStateCotizacion($obj_incident, $bool_estado);
                if ($obj_cotiza == false)
                {
                    //$response = $this->responseError(4, $this->TicketModel->getLastError());
                    $response = $this->responseError(4);
                    $this->sendResponse($response);
                }
                else
                {

                    $array_result = array('resultado' => true);
                    $result = json_encode($array_result);
                    $this->sendResponse($result);
                }

            }
            else
            {
                $response = $this->responseError(10); // Error estado no es de tipo booleano
                $this->sendResponse($response);
            }
        }
        else
        {
            $response = $this->responseError(2);
            $this->sendResponse($response);
        }

    }

    public function IncidentNota($nota,$obj_incident){
        if(strlen($nota)>0)
        {
    
          try
          {
              $obj_incident->Threads = new RNCPHP\ThreadArray();
              $obj_incident->Threads[0] = new RNCPHP\Thread();
              $obj_incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
              $obj_incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
              $obj_incident->Threads[0]->Text = $nota;
              $obj_incident->Save(RNCPHP\RNObject::SuppressAll);
          }
          catch ( RNCPHP\ConnectAPIError $err )
          {
              $obj_incident->Subject = "Error" . $err->getMessage();
              $obj_incident->Save(RNCPHP\RNObject::SuppressAll);
    
          }
        }
    }

    public function setOrderOMTicket_Conv()
    {
        
       $data = json_decode(file_get_contents('php://input'), true);

       $array_data = $data;
       //$this->sendResponse(json_encode($data));
       
       
       if (is_array($array_data) and ($array_data != false))
       {
         
         
         $array_data=$data;
       }
       else
       {
        $data=json_decode(' {"resultado":"OK",
            "status":"S",
            "mensaje":"- Order has been booked.",
            "NroPedido":"1685130",
            "Orig_sys_document_ref":"230822-000002"}',true);
            $array_data=$data;
            
       }
        $indiceTicket = 'Orig_sys_document_ref';
        $OM_Number = 'NroPedido';
        $OM_Message = 'mensaje';

        $nro_referencia = $array_data[$indiceTicket];
        $orderOmNumber = $array_data[$OM_Number];  
        $orderMessage = $array_data[$OM_Message]; 
        $incident = RNCPHP\Incident::fetch($nro_referencia);
        if (!empty($orderOmNumber)) 
        {
            $incident->CustomFields->c->order_number_om = $orderOmNumber;
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            $message = "Número de Orden ".$orderOmNumber. " registrada con exito";
            $bannerNumber = 1;
            $this->IncidentNota($message,$incident);
            $this->IncidentNota($orderMessage,$incident);
        }
        else
        {
            $message = "Número de Orden vacia";
            $bannerNumber = 3;
            $this->IncidentNota($message,$incident);
            $this->IncidentNota($orderMessage,$incident);
        }



        $array_result = array('resultado' => true);
        $result = json_encode($array_result);
        $this->sendResponse($result);
      
   
    }


    /**
     * Actualiza ticket de Reemplazo con el Numero de OA o el mensaje de respuesta de SAI. segun corresponda
     */
    public function setTicketReplacement()
    {
        
       $data = json_decode(file_get_contents('php://input'), true);

       $array_data = $data;
       //$this->sendResponse(json_encode($data));
       
       
       if (is_array($array_data) and ($array_data != false))
       {
         $array_data=$data;
       }
       else
       {

       
        /*$data=json_decode(' {
                "status":false,
                "oa":"41815",
                "estado":"PENDIENTE_FINANZAS",
                "habilitado":"false",
                "mensaje":"HH-3034555 ya pertenece a un contrato",
                "referencia_externa":"231018-000006",
                "accion":"setOAPTTO",
                "usuario":"UserDimacofi",
                "url_callback_rn":"http://localhost/es_una_prueba"
            }',true);

        */
                $array_result = array('resultado' => false,'error'=>'JSON enviado es invalido');
                $result = json_encode($array_result);
                $this->sendResponse($result);
            
       }
        

        $nro_referencia = $array_data['referencia_externa'];
        //echo $nro_referencia;
        $orderMessage = $array_data['mensaje']; 
        $oa = $array_data['oa']; 
        $status = $array_data['status']; 
        $incident = RNCPHP\Incident::fetch($nro_referencia);
    
        if ($status) 
        {
            
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            $message = "Número de oa ".$oa. " registrada con exito";
            $bannerNumber = 1;
            $incident->StatusWithType->Status->ID                  = 3 ; // Cierre el Ticke de reemplazo
            if( $incident->Disposition->ID==123)
            {
                // se actualiza con numero de OA de Reempazo de Equipo
                $incident->CustomFields->c->order_number_om_ref = $oa;    // Actualiza la OA 
            }
            else
            {
                // reemplazo prestamo
                $incident->CustomFields->c->orden_activacion = $oa;    // Actualiza la OA 
            }
            $incident->CustomFields->c->orden_activacion = $oa;    // Actualiza la OA 
            $incident->Save();
            $this->IncidentNota($message,$incident);
            $this->IncidentNota($orderMessage,$incident);
            $incident_soporte = RNCPHP\Incident::fetch($incident->CustomFields->OP->Incident->ID);
            $incident_soporte->StatusWithType->Status->ID=2;
            $incident_soporte->Save();

        }
        else
        {
            $message = "Número de oa vacia no se puedo generar la OA.[" . $orderMessage . "]" ;
            $bannerNumber = 3;
            $this->IncidentNota($message,$incident);
            $this->IncidentNota($orderMessage,$incident);
            $incident->StatusWithType->Status->ID                  = 1 ; // Vuelve a Ingresado ya qu eexiste un problema al crear la OA
            $incident->Save();
        }



        $array_result = array('resultado' => true);
        $result = json_encode($array_result);
        $this->sendResponse($result);
      
   
    }
    public function setOrderOMTicket()
    {
        //$data_post  = $_POST;
        $data = json_decode(file_get_contents('php://input'), true);
        
        //$json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
        $array_data = $data;
        //$this->sendResponse(json_encode($data));
        
        
        if (is_array($array_data) and ($array_data != false))
        {
           
            $indiceDatos = 'datos';
            $indiceAccion = 'accion';
            $indiceUsuario = 'usuario';
            
            if (!array_key_exists($indiceAccion, $array_data) and !array_key_exists($indiceUsuario, $array_data) )
            {
                $response = $this->responseError(3);
                $this->sendResponse($response);
            }

            if ($array_data[$indiceUsuario] != self::USER)
            {
                $response = $this->responseError(5);
                $this->sendResponse($response);
            }

            if ($array_data[$indiceAccion] != "setOrderOMTicket" )
            {
                $response = $this->responseError(6);
                $this->sendResponse($response);
            }

                    $indiceTicket = 'ref_number_order';
                    $OM_Number = 'order_number_OM';
                    $OM_Message = 'mensaje';

                    $nro_referencia = $array_data[$indiceTicket];
                    $orderOmNumber = $array_data[$OM_Number];  
                    $orderMessage = $array_data[$OM_Message];  
                    //$this->sendResponse($nro_referencia);
                    $incident = RNCPHP\Incident::fetch($nro_referencia);
                    //$this->IncidentNota($orderMessage,$incident);
                    //$this->sendResponse(json_encode($data) . " ID  " .  $nro_referencia . " OM_Number " . $orderOmNumber. " OM_Message " . $orderMessage );
                  
                    if (!empty($orderOmNumber)) 
                    {
                      $incident->CustomFields->c->order_number_om = $orderOmNumber;
                      $incident->Save(RNCPHP\RNObject::SuppressAll);
                      $message = "Número de Orden ".$orderOmNumber. " registrada con exito";
                      $bannerNumber = 1;
                      $this->IncidentNota($message,$incident);
                      $this->IncidentNota($orderMessage,$incident);
                    }
                    else
                    {
                      $message = "Número de Orden vacia";
                      $bannerNumber = 3;
                      $this->IncidentNota($message,$incident);
                      $this->IncidentNota($orderMessage,$incident);
                    }



                    $array_result = array('resultado' => true);
                    $result = json_encode($array_result);
                    $this->sendResponse($result);

        }
        else
        {
            $response = $this->responseError(2);
            $this->sendResponse($response);
        }

    }
}
