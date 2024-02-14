<?php



namespace Custom\Controllers;
use RightNow\Connect\v1_2 as RNCPHP;
require_once( get_cfg_var("doc_root")."/ConnectPHP/Connect_init.php" );

class Services extends \RightNow\Controllers\Base
{
    CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    CONST USER = "UserDimacofi";
    CONST ACCION = "getTicketsByTecnico";
 //CONST URL_GET_HH   = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
 //CONST URL_GET_HH   = "http://190.14.56.27/dts/rn_integracion/rntelejson.php";
 CONST __FUNCTION__CIS__ = "CrearIncidenteServicio";
    public $responseEncripted = false;
    protected $typeFormat = 'json';

    public $URL_GET_HH ="";
 
    static $msgError="";
    function __construct()
    {
        parent::__construct();

        $this->load->library('Blowfish', false); //carga Libreria de Blowfish
        $this->load->model('custom/ws/TecnicosModel'); //Modelo para acceder a tecnicos y modelo
        $this->load->model('custom/ws/TicketModel'); //Modelo para acceder a tecnicos y modelo
        $this->load->model('custom/ws/EnviromentConditions'); //Modelo para acceder a tecnicos y modelo
        $this->load->model('custom/ws/OpportunityModel');
    


        $this->load->library('fpdf2');
        $cfg2 = RNCPHP\Configuration::fetch( CUSTOM_CFG_WS_URL );
        

        $this->URL_GET_HH = $cfg2->Value;
        $this->companyInfo1    = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_COMPANY_INFO_1);
        $this->companyInfo2    = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_COMPANY_INFO_2);
        $this->companyInfo3    = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_COMPANY_INFO_3);
        $this->companyInfo4    = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_COMPANY_INFO_4);
        $this->companyInfo5    = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_COMPANY_INFO_5);
        $this->companyInfo6    = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_COMPANY_INFO_6);
        $this->conditionsInfo1 = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_CONDITIONS_INFO_1);
        $this->conditionsInfo2 = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_CONDITIONS_INFO_2);
        $this->conditionsInfo3 = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_CONDITIONS_INFO_3);
        $this->conditionsInfo4 = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_CONDITIONS_INFO_4);
        $this->conditionsInfo5 = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_CONDITIONS_INFO_5);
        $this->mail_to_ptto    = RNCPHP\MessageBase::fetch(CUSTOM_MSG_MAIL_TO_PTTO);
		load_curl();
    }

    public function apitoken()
    {
          $CI =& get_instance();
          $CI->load->model('custom/ConnectUrl');
          $cfg2 = RNCPHP\Configuration::fetch( 1000019 );
          echo $cfg2->Value;
          if (strstr($cfg2->Value,"8290"))
          {
              $consumerKey    = "gaIIMLvsZM6tMv7G6WgDeAdDb7Ma"; // TEST 
              $consumerSecret = "5cTKPPLY2mCsiR23jvwB63j446ka"; // TEST
          }
          else
          {
              $consumerKey    = "yh8wgLIb4RLIHwQ868CIifi2EYca"; // Prod 
              $consumerSecret = "bfaZkjfdIWoEtiXoDbo4E_EPpAka"; // Prod
          } 
          $url_token=$cfg2->Value . '/token';
          echo $consumerKey;
          $data           = array("grant_type" => "client_credentials");
          //$consumerKey    = "gaIIMLvsZM6tMv7G6WgDeAdDb7Ma"; // TEST 
          //$consumerSecret = "5cTKPPLY2mCsiR23jvwB63j446ka"; // TEST
          //$consumerKey    = "5_127oyLQSwQ_yA7HpRXAAvEcBoa";
          //$consumerSecret = "HzHL7WmxxY7Y4nqWVg0uzqSKDmga";
        
          $tokenA = $CI->ConnectUrl->requestCURLByPost($url_token, $data, $consumerKey . ":" . $consumerSecret);
          $a_jsonToken = json_decode($tokenA, TRUE);
          $token = $a_jsonToken["access_token"];
         
          echo $token;

          $url = $cfg2->Value .  "/apiCloudMD/GetListMRutHH";

         
          //$consumerKey    = "5_127oyLQSwQ_yA7HpRXAAvEcBoa";
          //$consumerSecret = "HzHL7WmxxY7Y4nqWVg0uzqSKDmga";
          $response=$CI->ConnectUrl->requestCURLJsonRaw($url, '{"RUT":"76307553-2"}', $token); 
          
          echo "-->" .$response;
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
                                                                   "id_direccion" => $incident->CustomFields->DOS->Direccion->d_id,
                                                                   "dir_envio" => $incident->CustomFields->DOS->Direccion->dir_envio,
                                                                   'seguimiento_tecnico' => $incident->CustomFields->c->seguimiento_tecnico->LookupName,
                                                                   'Contador_BN' => $incident->CustomFields->c->cont1_hh,
																   'Contador_Color' => $incident->CustomFields->c->cont2_hh

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
            case 11:
				$response =  array('Error' => 11, 'Glosa' => 'HH Invalida');
				break;
			case 12:
				$response =  array('Error' => 12, 'Glosa' => 'Orden Activacion Invalida');
				break;
			case 13:
				$response =  array('Error' => 13, 'Glosa' => 'Orden Activacion o HH no puede ir Vacia');
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

    public function setPrioriozation()
    {  
      if (!empty($_POST))
      {
        $data_post  = $this->getdataPOST();
        $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
        $array_data = json_decode(utf8_encode($json_data), true);

      }
  /*
      else
        {
          $array_post    = array("usuario" => "UserDimacofi",
          "accion"  => "setPrioriozation",
          "datos" => array(
            "ref_num"         => '220809-000220',
            "Priorization"             => "78"
            
          ));
          $data_post=json_encode($array_post);
          
          $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
          $array_data = json_decode(utf8_encode($json_data), true);
        }
*/
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
            
            $indiceTicket = 'ref_num';
            $indicePriorization = 'Priorization';

            if ($array_data[$indiceDatos][$indicePriorization])
            {

                $nro_referencia = $array_data[$indiceDatos][$indiceTicket];
                $Priorization = $array_data[$indiceDatos][$indicePriorization];


                $this->load->model('custom/ws/TicketModel');   //libreria para tickets

                $obj_incident = $this->TicketModel->getObjectTicket($nro_referencia);
               
                if ($obj_incident == false)
                {
                    $response = $this->responseError(8);
                    $this->sendResponse($response);
                }
                $respuesta=true;
                if($obj_incident->CustomFields->c->priorization<1000)
                {
                  $respuesta = $this->TicketModel->setPriorization($obj_incident, $Priorization);
                }

                if ($respuesta == false)
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

public function setIncidentState()
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


			$nro_referencia = $array_data[$indiceDatos][$indiceTicket];
			$estado = $array_data[$indiceDatos][$indiceEstado];


			$this->load->model('custom/ws/TicketModel');   //libreria para tickets

			$obj_incident = $this->TicketModel->getObjectTicket($nro_referencia);
			if ($obj_incident == false)
			{
				$response = $this->responseError(8);
				$this->sendResponse($response);
			}
			// A futuro controlar estado actual antes del cambio
			//if ($obj_incident->StatusWithType->Status->ID != 111) // Por Despachar
			//{
			//	$response = $this->responseError(9);
			//	$this->sendResponse($response);
			//}

			$obj_cotiza = $this->TicketModel->setIncidentState($obj_incident, $estado);
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
            $response = $this->responseError(2);
            $this->sendResponse($response);
        }
    }
    public function leefile()
    {
      $obj_incident = RNCPHP\Incident::fetch( '180110-000421');
      $this->sendResponse(json_encode($obj_incident->StatusWithType->Status->ID));

      $env=$this->EnviromentConditions->getObjectEnviromentConditions($obj_incident->ID);
      //Obtiene Correos Alternativos  del Incidente
      $email = $env->AlternativeEmails;
      $i=0;

      if(strlen($email)>0 )
      {

        $todos=explode(",",$email );
        foreach ($todos as $key => $value) {
            $pos[$i]['e']=$value;
            $pos[$i]['a']=strpos ($value, '@');
            if($pos[$i]['a'])
            {
               $pos[$i]['p']=strpos ($value, '.', $pos['a'][$i]);
            }
            else {
              $pos[$i]['p']=false;
            }
            $i++;

        }

        $i=0;
        foreach ($pos as $key => $value) {
          if($value['a'] && $value['p'])
          {
                $emailGroup[$i]=$value['e'];
                $i++;
          }
        }
      }

    if(strlen($obj_incident->PrimaryContact->Emails[0]->Address)>0)
    {
        $emailGroup[$i]=$obj_incident->PrimaryContact->Emails[0]->Address;
        $i++;
    }

    $emailGroup[$i]=$obj_incident->AssignedTo->Account->Emails[0]->Address;


    $mm = new RNCPHP\MailMessage();
    $mm->To->EmailAddresses = $emailGroup;
    $mm->Subject = 'Comprobante de Atención ' . '170809-000383';
    $mm->Body->Text = 'Pdf Para Contacto Orden de Trabajo Firmada  para Ticket ' . '170809-000383';
    $filescount=count($obj_incident->FileAttachments);
    foreach ($obj_incident->FileAttachments as $key => $value) {
      if($value->FileName == 'OT-' . '170809-000383' . '-' . $env->VisitNumber .'.pdf')
      {
        //$this->sendResponse(json_encode($value->FileName . ' - ' . $value->ContentType   . ' - ' .   $env->VisitNumber ) );
        $mm->FileAttachments[] = $value ;
      }
      if($value->FileName == 'Contadores-' . '170809-000383' . '-' . $env->VisitNumber .'.jpg')
      {
        //$this->sendResponse(json_encode($value->FileName . ' - ' . $value->ContentType   . ' - ' .   $env->VisitNumber ) );
        $mm->FileAttachments[] = $value ;
      }
    }
    $mm->send();










        $acc=RNCPHP\Account::fetch(67873);

        $this->sendResponse(json_encode($acc));
        $record = explode('-',"kENIA DIAZ-kdiaz@redmagister.cl-552537623");
        $n=strpos ($record[0], '@');
        $a=strpos ($record[1], '@');
        $p=strpos ($record[1], '.',$a);
        $e=strpos ($record[1], ' ');
        $cc=strpos ($record[1], '@.');

        if($a>0 && $p>0 && $n=='' && $e=='' && $cc=='')
        {
          //$this->sendResponse(json_encode($record) . ' aca '  . $a . ' ' . $p . ' n{' . $n .  '}' . 'e{' . $e . '}'. 'cc{' . $cc . '}');
          $contact = new RNCPHP\Contact();
          $contact->Login = $record[1];
          $person = explode(" ",$record[0] );
          $contact->Name = new RNCPHP\PersonName();

          if(strlen($person[0])>80)
          {
            $first=substr($person[0], 0, 80);
          }
          else {
            $first=$person[0];
          }
          $contact->Name->First =$first;

          if(strlen($person[1])>80)
          {
            $last=substr($person[1], 0, 80);
          }
          else {
            $last=$person[1];
          }
          $contact->Name->Last = $last;

                //add email addresses
          $contact->Emails = new RNCPHP\EmailArray();
          $contact->Emails[0] = new RNCPHP\Email();
          $contact->Emails[0]->AddressType=new RNCPHP\NamedIDOptList();
          $contact->Emails[0]->AddressType->LookupName = "Correo electrónico - Principal";
          if(strlen($record[1])>80)
          {
            $email=substr($record[1], 0, 80);
          }
          else {
            $email=$record[1];
          }
          $contact->Emails[0]->Address = $email;


          $i = 0;
          if($record[2])
          {
            $contact->Phones = new RNCPHP\PhoneArray();
            $contact->Phones[$i] = new RNCPHP\Phone();
            $contact->Phones[$i]->PhoneType = new RNCPHP\NamedIDOptList();
            $contact->Phones[$i]->PhoneType->LookupName = 'Teléfono de oficina';

            if(strlen($record[2])>40)
            {
              $phone=substr($record[2], 0, 40);
            }
            else {
              $phone=$record[2];
            }
            $contact->Phones[$i]->Number =  $phone;
          }

        $i++;
          $contact->save();
        }

          if($a>0 && $p>0 && $n=='' && $e=='' && $cc=='')
          {
            $this->sendResponse(json_encode($person[1]));
          }
else {
              $this->sendResponse(json_encode($record) . ' '  . $a . ' ' . $p . ' n{' . $n .  '}' . 'e{' . $e . '}'. 'cc{' . $cc . '}');
}
       $this->sendResponse(json_encode($person));
       $aIncident = RNCPHP\Incident::fetch( '170629-000336');

      // $this->sendResponse(json_encode($aIncident));
    }

    public function send_image($buff,$ref_no,$incident)
    {

      $dir_cliente='';
      $dir_cliente= $dir_cliente . $incident->CustomFields->DOS->Direccion->LookupName;
      $dir_cliente= $dir_cliente . ',';
      $dir_cliente= $dir_cliente . $incident->CustomFields->DOS->Direccion->ebs_comuna;
      $dir_cliente= $dir_cliente . ',';
      $dir_cliente= $dir_cliente . $incident->CustomFields->DOS->Direccion->ebs_region;

      $databuff=base64_encode($buff);
      $env=$this->EnviromentConditions->getObjectEnviromentConditions($incident->ID);

      $array_post     = array("usuario" => "Integer",
                              "accion"  => "CreatePdf",
                              "datospdf" => array(
                                "imagen"      => $databuff,
                                "ref_no" => $ref_no,
                                "rut_cliente" => $incident->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut,
                                "nombre_cliente" => $incident->CustomFields->DOS->Direccion->Organization->LookupName,
                                "dir_cliente" => $dir_cliente,
                                "contacto" =>  $incident->PrimaryContact->LookupName,
                                "email_contacto" =>$incident->PrimaryContact->Emails[0]->Address,
                                "telefono_contacto" =>$incident->PrimaryContact->Phones[0]->Number,
                                "tipo_contrato" => $incident->CustomFields->c->tipo_contrato,
                                "disposition_id" => $incident->Disposition->ID,
                                "disposition_name" => $incident->Disposition->LookupName,
                                "Observacion" =>$incident->CustomFields->c->shipping_instructions,
                                "motivo" =>$incident->CustomFields->c->motivo_solucion->LookupName,
                                "diagnostico" =>$incident->CustomFields->c->diagnostico->LookupName,
                                "equipo_detenido" =>  $incident->CustomFields->c->equipo_detenido,
                                "id_hh" => $incident->CustomFields->c->id_hh,
                                "cont1" => $incident->CustomFields->c->cont1_hh,
                                "cont2" => $incident->CustomFields->c->cont2_hh,
                                "marca" => $incident->CustomFields->c->marca_hh,
                                "modelo" => $incident->CustomFields->c->modelo_hh,
                                "serie" => $incident->CustomFields->c->serie_maq,
                                "support_type" => $incident->CustomFields->c->support_type->LookupName,
                                "Description" => $env->Description,
                                "Solution" => $env->Solution,
                                "IpNumber" => $env->IpNumber,
                                "Copy" => $env->Copy,
                                "Scan" => $env->Scan,
                                "Printer" => $env->Printer,
                                "Fax" => $env->Fax,
                                "Temperture" => $env->Temperture,
                                "IssueCausa" => $env->IssueCausa,
                                "ElectricalCondition" => $env->ElectricalCondition,
                                "EnviromentCondit" => $env->EnviromentCondit,
                                "PrintFlow" => $env->PrintFlow,
                                "OperatingSystem" => $env->OperatingSystem,
                                "nombretecnico" => $incident->AssignedTo->Account->LookupName,
                                "Area" => $env->Area,
                                "CostCenter" => $env->CostCenter,
                                "Reception_Name" => $env->Reception_Name,
                                "AlternativeEmails" => $env->AlternativeEmails,
                                "NoDataMobile" => $env->NoDataMobile,
                                "VisitNumber" => $env->VisitNumber
                              ));
      $json_data_post = json_encode($array_post);
      $json_data_post = $this->blowfish->encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
      $json_data_post = base64_encode($json_data_post);
      $postArray = array ('data' => $json_data_post);
      $result = $this::requestPost($this->URL_GET_HH, $postArray);
      return $buff;
    }

    public function upsend()
    {
        /*logMessage("upsend FILES " . json_encode($_POST)  );
        logMessage("upsend FILES " . json_encode($_POST["file2"])  );
        logMessage("upsend FILES " . json_encode($_POST["ref_no"])  );
        logMessage("upsend ->>>>>>> " . json_encode($_FILES)  );
        */
        $buffer=$_POST['hidden_data'];
        $obj_incident = RNCPHP\incident::fetch( $_POST["ref_no"]);
        $this->send_image($buffer,$_POST["ref_no"],$obj_incident);
        $array_response['response']= array ('status' => true, 'ref_no' => $_POST["ref_no"] ,'cont1_hh' => $cont1_hh ,'cont2_hh' => $cont2_hh , 'error' => 0,"id_BN" => $id_BN,"id_Color" => $id_Color);

        $responseEncode = json_encode($array_response);

        $this->sendResponse($responseEncode);

    }
    public function upload()
    {

        //logMessage("Upload3  FILES" . json_encode($_FILES)  . " -->" . json_encode($_POST) );
        $dir_subida = '/tmp/';
        $fichero_subido = $dir_subida . basename($_FILES['file']['name']);

        //move_uploaded_file($_FILES['tmp_name'], $fichero_subido);
        $ref_no=$_POST;
        //logMessage("Incidente " . $ref_no['ref_no'] );
        $obj_incident             = RNCPHP\incident::fetch( $ref_no['ref_no']);

        $fattach = new RNCPHP\FileAttachmentIncident();
        //logMessage("New FileAttachmentIncident");
        //logMessage("ContentType " . $_FILES['file']['type']);
        $fattach->ContentType = $_FILES['file']['type'];
        $fattach->Private=false;
        //logMessage("ContentType " . $_FILES['file']['type']);
        $fattach->setFile($_FILES['file']["tmp_name"]);
        $fattach->FileName =  $ref_no['type'] . '-' . $ref_no['ref_no'] .'-' . $ref_no['VisitNumber'] .'.jpg';

        if($ref_no['type']=='OT')
        {
            $env=$this->EnviromentConditions->getObjectEnviromentConditions($obj_incident->ID);
            $env->NoDataMobile=true;
            $env->Save();
        }
        //logMessage("/tmp/" . $_FILES['file']["tmp_name"]);
        $obj_incident->FileAttachments =new RNCPHP\FileAttachmentIncidentArray();
        //logMessage("FileAttachmentIncidentArray");
        $obj_incident->FileAttachments[] = $fattach;
        //logMessage("FileAttachments");

        try{
                  $obj_incident->Save();
                  //logMessage("save");
        }
        catch (Exception $err ){

          //logMessage( $err->getMessage());
          $array_response['response'] = array ('status' => false, 'ref_no' => $refNo ,'cont1_hh' => $cont1_hh ,'cont2_hh' => $cont2_hh , 'error' => 0,"id_BN" => $id_BN,"id_Color" => $id_Color);
          $responseEncode = json_encode($array_response);
          $this->sendResponse($responseEncode);

        }

        $array_response['response'] = array ('status' => true, 'ref_no' => $refNo ,'cont1_hh' => $cont1_hh ,'cont2_hh' => $cont2_hh , 'error' => 0,"id_BN" => $id_BN,"id_Color" => $id_Color);
        $responseEncode = json_encode($array_response);
        $this->sendResponse($responseEncode);
    }

    public function genpdf()
    {
      //logMessage("Incidente llegando "  );
      try{
          $emailGroup=array();
          $dir_subida = '/tmp/';
          $fichero_subido = $dir_subida . basename($_FILES['file']['name']);
          $i=0;
          //move_uploaded_file($_FILES['tmp_name'], $fichero_subido);
          $ref_no=$_POST;
          //logMessage("Incidente " . $ref_no['ref_no'] );
           //$obj_incident             = RNCPHP\incident::fetch( 123605);
          $obj_incident             = RNCPHP\incident::fetch( $ref_no['ref_no']);
          $fattach = new RNCPHP\FileAttachmentIncident();
          $fattach->ContentType = $_FILES['file']['type'];
          $fattach->Private=false;
          $fattach->setFile($_FILES['file']["tmp_name"]);
          $fattach->FileName = $_FILES['file']['name'];
          $obj_incident->FileAttachments =new RNCPHP\FileAttachmentIncidentArray();
          $obj_incident->FileAttachments[] = $fattach;
          //logMessage("Incidente " . json_encode($_POST) );

          $obj_incident->Save();
          $env=$this->EnviromentConditions->getObjectEnviromentConditions($obj_incident->ID);
          //Obtiene Correos Alternativos  del Incidente
          $email = $env->AlternativeEmails;
          $i=0;

          if(strlen($email)>0)
          {

            $todos=explode(",",$email );
            foreach ($todos as $key => $value) {
                $pos[$i]['e']=$value;
                $pos[$i]['a']=strpos ($value, '@');
                if($pos[$i]['a'])
                {
                   $pos[$i]['p']=strpos ($value, '.', $pos['a'][$i]);
                }
                else {
                  $pos[$i]['p']=false;
                }
                $i++;

            }

            $i=0;
            foreach ($pos as $key => $value) {
              if($value['a'] && $value['p'])
              {
                    $emailGroup[$i]=$value['e'];
                    $i++;
              }
            }
          }
          if(strlen($obj_incident->PrimaryContact->Emails[0]->Address)>0)
          {
              $emailGroup[$i]=$obj_incident->PrimaryContact->Emails[0]->Address;
              $i++;
          }

          $emailGroup[$i]=$obj_incident->AssignedTo->Account->Emails[0]->Address;
          //$emailGroup[0]='
      $mm = new RNCPHP\MailMessage();
      $mm->To->EmailAddresses = $emailGroup;
      $mm->Subject = 'Comprobante de Atención ' . $ref_no['ref_no'];
      $mm->Body->Text = 'Pdf Para Contacto Orden de Trabajo Firmada  para Ticket ' . $ref_no['ref_no'];
      $filescount=count($obj_incident->FileAttachments);
      foreach ($obj_incident->FileAttachments as $key => $value) {
        if($value->FileName == 'OT-' . $ref_no['ref_no'] . '-' . $env->VisitNumber .'.pdf')
        {
          //$this->sendResponse(json_encode($value->FileName . ' - ' . $value->ContentType   . ' - ' .   $env->VisitNumber ) );
          $mm->FileAttachments[] = $value ;
        }
        if($value->FileName == 'Contadores-' . $ref_no['ref_no'] . '-' . $env->VisitNumber .'.jpg')
        {
          //$this->sendResponse(json_encode($value->FileName . ' - ' . $value->ContentType   . ' - ' .   $env->VisitNumber ) );
          $mm->FileAttachments[] = $value ;
        }
      }
      $mm->send();

      $array_response['response'] = array (
                                           'status' => true,
                                           'ref_no' => $refNo ,
                                           'cont1_hh' => $cont1_hh ,
                                           'cont2_hh' => $cont2_hh ,
                                           'error' => 0,
                                           "id_BN" => $id_BN,
                                           "id_Color" => $id_Color,
                                           'email_contact'=> $obj_incident->PrimaryContact->Emails[0]->Address,
                                           'email_account'=> $obj_incident->AssignedTo->Account->Emails[0]->Address
                                         );
      $responseEncode = json_encode($array_response);
      $this->sendResponse($responseEncode);
    }
    catch (Exception $err ){
      $this->sendResponse( $err->getMessage());

    }

    }
    public function updateIncidentLogistica ()
    {


      $mensajes = array();
      if (empty($_POST))
       {
         $response = $this->responseError(1);

         $this->sendResponse($response);
       }

       if(empty($_POST['data']))
       {

         $response = $this->responseError(2);
         $this->sendResponse($response);
       }
       $data = trim($_POST['data']);

       $array_data = json_decode($data, true);
       if (  !is_array($array_data) or !array_key_exists('order_detail', $array_data))
       {

         $response = $this->responseError(3, print_r($data, true));
         $this->sendResponse($response);
       }

       $orderDetail = $array_data['order_detail'];
       $refNo                = $orderDetail['ref_no'];
       $status               = $orderDetail['select_status'];
       $nota                 = $orderDetail['nota'];

       $CI = get_instance();
       $username = $CI->session->getSessionData("username");
       $password = $CI->session->getSessionData("password");

       initConnectAPI($username,$password);
       $obj_incident             = RNCPHP\incident::fetch($refNo);
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
       $obj_incident->StatusWithType->Status->ID=$status;
       $obj_incident->Save();
       $mensaje[0]='Datos Modificados';
       $msg=$mensaje[0];
       $array_response['response'] = array ('status' => true, 'ref_no' => $refNo , 'error' => 0,'msg' => $msg, 'select_status'=> $status);
       $responseEncode = json_encode($array_response);
       $this->sendResponse($responseEncode);

    }

    public function updateIncidentInforme ()
       {


         $mensajes = array();
         if (empty($_POST))
          {
            $response = $this->responseError(1);

            $this->sendResponse($response);
          }

          if(empty($_POST['data']))
          {

            $response = $this->responseError(2);
            $this->sendResponse($response);
          }
          $data = trim($_POST['data']);

          $array_data = json_decode($data, true);
          if (  !is_array($array_data) or !array_key_exists('order_detail', $array_data))
          {

            $response = $this->responseError(3, print_r($data, true));
            $this->sendResponse($response);
          }

          $orderDetail = $array_data['order_detail'];
          $refNo                = $orderDetail['ref_no'];
          $status               = $orderDetail['select_status'];

          $CI = get_instance();
          $username = $CI->session->getSessionData("username");
          $password = $CI->session->getSessionData("password");

          initConnectAPI($username,$password);
          $obj_incident             = RNCPHP\incident::fetch($refNo);

          $obj_incident->StatusWithType->Status->ID=$status;

          //  debemos validar si existe objeto para no generar error

          $incidentR = RNCPHP\Incident::find('CustomFields.OP.Incident.ID=' . $obj_incident->ID  . '  and StatusWithType.status.ID not in(2,149,146)  ' );
          if(is_array($incidentR))
          {
            $incidentR[0]->StatusWithType->Status->ID = $status;
            $incidentR[0]->save();
          }

          $obj_incident->Save();
          $mensaje[0]='Datos Modificados';
          $msg=$mensaje[0];
          $array_response['response'] = array ('status' => true, 'ref_no' => $refNo , 'error' => 0,'msg' => $msg, 'select_status'=> $status);
          $responseEncode = json_encode($array_response);
          $this->sendResponse($responseEncode);

       }



        public function updateIncidentState()
        {
            $EnviromentConditions = (object) array('Notes'=>'');
            $mensajes = array();


            $CI = get_instance();
            $username = $CI->session->getSessionData("username");
            $password = $CI->session->getSessionData("password");
            initConnectAPI($username,$password);
          //logMessage("updateIncidentState " . json_encode($_POST));
         if (empty($_POST))
          {
            $response = $this->responseError(1);

            $this->sendResponse($response);
          }

          if(empty($_POST['data']))
          {

            $response = $this->responseError(2);
            $this->sendResponse($response);
          }

          $data = trim($_POST['data']);

          $array_data = json_decode($data, true);
          if (  !is_array($array_data) or !array_key_exists('order_detail', $array_data))
          {

            $response = $this->responseError(3, print_r($data, true));
            $this->sendResponse($response);
          }

          $orderDetail = $array_data['order_detail'];


          $action               = $orderDetail['action'];
          $refNo                = $orderDetail['ref_no'];
          $status               = $orderDetail['select_status'];
          $cont1_hh             = $orderDetail['cont1_hh'];
          $cont2_hh             = $orderDetail['cont2_hh'];
          $seguimiento_tecnico  = $orderDetail['seguimiento_tecnico'];
          $motivo_solucion      = $orderDetail['motivo_solucion'];
          $equipo_detenido      = $orderDetail['equipo_detenido'];
          $diagnostico          = $orderDetail['diagnostico'];
          $gasto                = $orderDetail['gasto'];
          $expend_type          = $orderDetail['expend_type'];
          $gsto_detail          = $orderDetail['gsto_detail'];
          $nota                 = $orderDetail['nota'];
          $tipo_contrato        = $orderDetail['tipo_contrato'];
          $disposition          = $orderDetail['disposition'];
          //$file                 = $orderDetail['file'];

          //$filedata                 = $orderDetail['contents'];

          $obj_incident             = RNCPHP\incident::fetch($refNo);
          if(!$gasto)
          {
            $obj_incident->CustomFields->c->gasto=0;
         }
          else {
            $obj_incident->CustomFields->c->gasto=$gasto;
          }


          if($obj_incident->CustomFields->c->gasto>=0 and $expend_type!=90 )
          {
            $gastos=new  RNCPHP\OP\Expenses();
            $gastos->Incident=$obj_incident->ID;
            $gastos->ExpenseType=$expend_type;
            $gastos->Description=$gsto_detail;
            $gastos->Expenses=$gasto;
            $gastos->save();

          }
          //logMessage("updateIncidentState " . json_encode($array_data));
    //$this->sendResponse($obj_incident->ID);
          $validar=0;
          if($seguimiento_tecnico==18)
          {
            $EnviromentConditions->Incident= $obj_incident->ID;
            $EnviromentConditions->Description=  $orderDetail['Description'];
            if($orderDetail['Description']=='' || $orderDetail['Description']=='-descripcion-')
            {
                 $validar=7;
            }
            $EnviromentConditions->Solution= $orderDetail['Solution'];
            if($orderDetail['Solution']=='' || $orderDetail['Solution']=='-solucion-')
            {
                 $validar=8;
            }
            $EnviromentConditions->IpNumber= $orderDetail['IpNumber'];
            if(($orderDetail['IpNumber']=='' || $orderDetail['IpNumber']=='-Numero IP-') && ($orderDetail['disposition'] ==27 || $orderDetail['disposition'] ==28) )
            {
                 $validar=9;
            }
            $EnviromentConditions->Copy=$orderDetail['Copy'];
            if(($orderDetail['Copy']=='' ) && ($orderDetail['disposition'] ==27 || $orderDetail['disposition'] ==28) )
            {
                 $validar=10;
            }
            $EnviromentConditions->Scan=$orderDetail['Scan'];
            if(($orderDetail['Scan']=='') && ($orderDetail['disposition'] ==27 || $orderDetail['disposition'] ==28) )
            {
                 $validar=11;
            }
            $EnviromentConditions->Printer= $orderDetail['Printer'];
            if(($orderDetail['Printer']=='') && ($orderDetail['disposition'] ==27 || $orderDetail['disposition'] ==28) )
            {
                 $validar=12;
            }

            $EnviromentConditions->Fax= $orderDetail['Fax'];
            if(($orderDetail['Fax']=='') && ($orderDetail['disposition'] ==27 || $orderDetail['disposition'] ==28) )
            {
                 $validar=13;
            }

            $EnviromentConditions->Temperture= $orderDetail['Temperture'];
            if($orderDetail['Temperture']=='SV')
            {
                 $validar=14;
            }
            $EnviromentConditions->IssueCausa= $orderDetail['IssueCausa'];
            if($orderDetail['IssueCausa']=='SV')
            {
                 $validar=15;
            }
            $EnviromentConditions->ElectricalCondition= $orderDetail['ElectricalCondition'];
            if($orderDetail['ElectricalCondition']=='SV')
            {
                 $validar=16;
            }
            $EnviromentConditions->EnviromentCondit= $orderDetail['EnviromentCondit'];
            if($orderDetail['EnviromentCondit']=='SV' )
            {
                 $validar=17;
            }
            $EnviromentConditions->PrintFlow= $orderDetail['PrintFlow'];
            if(($orderDetail['PrintFlow']=='SV') && ($orderDetail['disposition'] ==27 || $orderDetail['disposition'] ==28) )
            {
                 $validar=18;
            }
            $EnviromentConditions->OperatingSystem= $orderDetail['OperatingSystem'];
            if(($orderDetail['OperatingSystem']=='SV') && ($orderDetail['disposition'] ==27 || $orderDetail['disposition'] ==28) )
            {
                 $validar=19;
            }

            $EnviromentConditions->AlternativeEmails=$orderDetail['AlternativeEmails'];
            $EnviromentConditions->Area= $orderDetail['Area'];
            $EnviromentConditions->CostCenter= $orderDetail['CostCenter'];
            $EnviromentConditions->Reception_Name= $orderDetail['Reception_Name'];

            $id=$this->EnviromentConditions->updateEnviromentConditions($EnviromentConditions,$nota);

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


          //logMessage("Action " . json_encode($username));
          //$contadores = RNCPHP\DOS\Contador::find("Asset.SerialNumber = "  . $obj_incident->CustomFields->c->id_hh   );
          $hh = RNCPHP\Asset::first("SerialNumber = " . $obj_incident->CustomFields->c->id_hh  );
          $contadores = RNCPHP\DOS\Contador::find("Asset.ID = {$hh->ID}");

          $id_BN=0;
          $id_Color=0;

          if($seguimiento_tecnico==18)
          {
            $obj_incident->CustomFields->c->motivo_solucion->ID =$motivo_solucion;
            if($motivo_solucion==106 )
            {
                 $validar=20;
            }

        //logMessage("Action " . json_encode($diagnostico));
            $obj_incident->CustomFields->c->diagnostico->ID=$diagnostico;
            if($diagnostico==107 )
            {
                 $validar=22;
            }


            foreach($contadores as $key => $value)
            {
              //EnviromentConditionslogMessage("updateIncidentState " . json_encode($value->TipoContador->ID) . " - " .  $value->ContadorID . " - " . $value->Valor);
              if( ($value->TipoContador->ID==1 or $value->TipoContador->ID==13 or $value->TipoContador->ID==16 ) and $value->Valor )
              {
                $copia_BN=$value;
                $id_BN=$value->ContadorID;
              }

              if( ($value->TipoContador->ID==2 or $value->TipoContador->ID==14 ) and $value->Valor )
              {
                $copia_Color=$value;
                $id_Color=$value->ContadorID;
              }
            }

          }


            $action=1;
          $array_obj = RNCPHP\Incident::find(" AssignedTo.Account.ID=" . $obj_incident->AssignedTo->Account->ID .    " and StatusWithType.status.ID in(163,165) and Incident.ID  not in ("  .  $obj_incident->ID . ")"   );

          if($array_obj && $status<>98)
          {
            $action=4;
          }
          else {
            if($seguimiento_tecnico==18)
            {
              if($copia_BN->Valor>$cont1_hh)
              {
                $action=2;
              }
              if($copia_Color->Valor>$cont2_hh)
              {

                $action=3;
              }
            }

/* Restricion para archivos adjuntos*/
/*
            if ($status==19 || $status==17)
            {
              $adjuntos=false;
              if(count($obj_incident->FileAttachments))
              {
                foreach ($obj_incident->FileAttachments as $key => $value) {
                  $adjuntos=true;
                }
              }
              if(!$adjuntos)
              {
                $action=6;
              }

            }
*/

          }
          if($cont1_hh && $action<>2)
          {
            $obj_incident->CustomFields->c->cont1_hh=$cont1_hh;
          }

          if($cont2_hh && $action<>3)
          {

            $obj_incident->CustomFields->c->cont2_hh=$cont2_hh;
          }
        $obj_incident->CustomFields->c->equipo_detenido=$equipo_detenido;
        $obj_incident->Save();

          $mensaje[0]='';
          $mensaje[1]='';
          $mensaje[2]='Contador B/N Menor al Actual. ' . $copia_BN->Valor ;
          $mensaje[3]='Contador Color  Menor al Actual . '  . $copia_Color->Valor ;
          $mensaje[4]="Ya existe Ticket en estado Trabajando o En Ruta " .  $array_obj[0]->ReferenceNumber;
          $mensaje[5]='';
          $mensaje[6]="Debe Solicitar Firma de Cliente ";
          $mensaje[7]="descripcion  no puede ser vacio.";
          $mensaje[8]="Solucion no puede ser vacio.";
          $mensaje[9]="Numero IP no puede ser vacio.";
          $mensaje[10]="copia  no puede ser vacio.";
          $mensaje[11]="Scanner  no puede ser vacio.";
          $mensaje[12]="Impresora  no puede ser vacio.";
          $mensaje[13]="Fax  sin Valor.";
          $mensaje[14]="Temperatura  sin Valor.";
          $mensaje[15]="Motivo de la Falla sin valor.";
          $mensaje[16]="Condiciones Eléctricas sin valor.";
          $mensaje[17]="Condiciones Ambientales  sin valor.";
          $mensaje[18]="Flujo de Impresion  sin valor.";
          $mensaje[19]="Sistema Operativo  sin valor.";
          $mensaje[20]="Motivo Solución  sin valor.";
          $mensaje[21]="Motivo Solución  sin valor.";
          $mensaje[22]="Diagnostico sin valor.";

          //logMessage("Action " . json_encode($action));
          switch ($action) {
            case 1: //Crear

                  //Validar que todos los campos esten ingresados


                  if($validar==0)
                  {
                  $obj_incident->CustomFields->c->seguimiento_tecnico->ID=$status;

                  switch($status)
                  {
                    case 15:
                      $obj_incident->StatusWithType->Status->ID =162;
                    break;
                    case 16:
                      $obj_incident->StatusWithType->Status->ID =163;
                    break;
                    case 18:
                      $obj_incident->StatusWithType->Status->ID =165;
                    break;
                    case 19:
                      $obj_incident->StatusWithType->Status->ID =166;
                    break;
                    case 43:
                      $obj_incident->StatusWithType->Status->ID =167;
                    break;
                    case 24:
                      $obj_incident->StatusWithType->Status->ID =171;
                      break;
                    case  98:  // Despacho Entregado
                      $obj_incident->StatusWithType->Status->ID =112;
                    break;

                  }

                  if($seguimiento_tecnico==18)
                  {
                  if($cont1_hh)
                  {
                    $obj_incident->CustomFields->c->cont1_hh=$cont1_hh;
                  }

                  if($cont2_hh)
                  {

                    $obj_incident->CustomFields->c->cont2_hh=$cont2_hh;
                  }
                  }
                  }
                  $obj_incident->Save();
                  $msg=$mensaje[$validar];
                  $array_response['response'] = array ('status' => true, 'ref_no' => $refNo , 'error' => 0,'cont1_hh' => $cont1_hh ,'cont2_hh' => $cont2_hh,'copia_BN'=>$copia_BN->Valor,'copia_Color'=>$copia_Color->Valor,"id_BN" => $id_BN,"id_Color" => $id_Color, 'msg' => $msg, 'select_status'=> $status, 'validar' => $validar,'tipo_contrato'=>$tipo_contrato);
                  //$array_response['response'] = array ('status' => true, 'ref_no' => $refNo ,'cont1_hh' => $cont1_hh ,'cont2_hh' => $cont2_hh , 'error' => 0,"id_BN" => $id_BN,"id_Color" => $id_Color, 'msg' => $msg);
                  //logMessage("updateIncidentState " . json_encode($array_response));
                  $responseEncode = json_encode($array_response);
                  $this->sendResponse($responseEncode);

              break;
             case 2:
             $msg = $mensaje[2];
             $array_response['response'] = array ('status' => false, 'ref_no' => $refNo, 'error' => 2,'cont1_hh' => $cont1_hh ,'cont2_hh' => $cont2_hh,'copia_BN'=>$copia_BN->Valor,'copia_Color'=>$copia_Color->Valor,"id_BN" => $id_BN,"id_Color" => $id_Color,"msg" => $msg, 'select_status'=> $status, 'validar' => $valida,'tipo_contrato'=>$tipo_contrato);
             $responseEncode = json_encode($array_response);
             $this->sendResponse($responseEncode);
             break;

             case 3:
             $msg = $mensaje[3];
             $array_response['response'] = array ('status' => false, 'ref_no' => $refNo , 'error' => 3,'cont1_hh' => $cont1_hh ,'cont2_hh' => $cont2_hh,'copia_BN'=>$copia_BN->Valor,'copia_Color'=>$copia_Color->Valor,"id_BN" => $id_BN,"id_Color" => $id_Color,"msg" => $msg, 'select_status'=> $status, 'validar' => $validar,'tipo_contrato'=>$tipo_contrato);
             $responseEncode = json_encode($array_response);
             $this->sendResponse($responseEncode);
             break;
             case 4:

             $msg=$mensaje[4];

             $array_response['response'] = array ('status' => false, 'ref_no' => $refNo , 'error' => 4,'cont1_hh' => $cont1_hh ,'cont2_hh' => $cont2_hh,'copia_BN'=>$copia_BN->Valor,'copia_Color'=>$copia_Color->Valor,"id_BN" => $id_BN,"id_Color" => $id_Color, "msg" => $msg, 'select_status'=> $status, 'validar' => $validar,'tipo_contrato'=>$tipo_contrato);
             $responseEncode = json_encode($array_response);
             $this->sendResponse($responseEncode);
             break;
             case 5:

               $msg=$mensaje[5];
               $array_response['response'] = array ('status' => false, 'ref_no' => $refNo , 'error' => 5,'cont1_hh' => $cont1_hh ,'cont2_hh' => $cont2_hh,'copia_BN'=>$copia_BN,'copia_Color'=>$copia_Color,"id_BN" => $id_BN,"id_Color" => $id_Color, 'msg' => $msg, 'select_status'=> $status, 'validar' => $validar,'tipo_contrato'=>$tipo_contrato);
               $responseEncode = json_encode($array_response);
               $this->sendResponse($responseEncode);
             break;
             case 6:

             $msg=$mensaje[6];

             $array_response['response'] = array ('status' => false, 'ref_no' => $refNo , 'error' => 6,'cont1_hh' => $cont1_hh ,'cont2_hh' => $cont2_hh,'copia_BN'=>$copia_BN->Valor,'copia_Color'=>$copia_Color->Valor,"id_BN" => $id_BN,"id_Color" => $id_Color, "msg" => $msg, 'select_status'=> $status, 'validar' => $validar,'tipo_contrato'=>$tipo_contrato);
             $responseEncode = json_encode($array_response);
             $this->sendResponse($responseEncode);
             break;

            default:

              $array_response['response'] = array ('status' => false, 'ref_no' => $refNo, 'error' => 1 );
              $responseEncode = json_encode($array_response);
              $this->sendResponse($responseEncode);
              break;
          }



      }


public function getreporttest()
{
        $filters               = new RNCPHP\AnalyticsReportSearchFilterArray;
        $report_id             = 102309;
       
        
        $filter_value          = "'69070600-8'";
        //logMessage("  filter_value " .     $filter_value);
        $status_filter         = new RNCPHP\AnalyticsReportSearchFilter;
        $status_filter->Name   = 'rut';
        $status_filter->Values = array($filter_value);
        $filters[]             = $status_filter;


        $status_filter         = new RNCPHP\AnalyticsReportSearchFilter;
        $status_filter->Name   = 'disp';
        $status_filter->Values = array(24);
        $status_filter->Operator->ID = 10;
        $filters[]             = $status_filter;
    
       
        
        $ar                    = RNCPHP\AnalyticsReport::fetch($report_id);
        $arr                   = $ar->run( 0, $filters );
  
        // Inicio - ALTERNATIVA ENCRIPTADA
        echo $arr->count();
        $os = array("Insumos", "NT", "Irix", "Linux");
        for ( $i = $arr->count(); $i--; )
        {
          $row = $arr->next();
          echo $row['disp_id'] .'<br>';
          if(in_array($row['disp_id'], $os))
          {
            $array_response['Tickets'][] = $row;
            
          }
        }
  
        $response = json_encode($array_response);

        $array_result['respuesta'] = $response;
  
        // FIN - Alternativa Encritpada
  
        $result = json_encode($array_result);
        $this->sendResponse($result);
}
public function getReportv()
{
  try 
  {
    //logMessage("  getReportv IN" );
    $data_post  = $this->getdataPOST();
    $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
    $array_data = json_decode(utf8_encode($json_data), TRUE);
    
    if (empty($_POST))
    {
      /*00005218071
            $response = $this->responseError(1);
            $this->sendResponse($response);
      */
      $json_data = json_encode( array ('usuario' => 'UserDimacofi', 'accion' => 'getReportv', 'datos' => array('report_id'=> 102451,'filter'=> '231221-000515') ));
      $array_data = json_decode(utf8_encode($json_data), TRUE);
    }
  
    
    if (is_array($array_data) and ($array_data != FALSE))
    {
      $indiceDatos   = 'datos';
      $indiceAccion  = 'accion';
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
  
      $indice_report = 'report_id';
      $indice_filter = 'filter';
      
      if (is_numeric($array_data[$indiceDatos][$indice_report])  )
      {
   
        $report_id             = $array_data[$indiceDatos][$indice_report];
        $filter_value          = $array_data[$indiceDatos][$indice_filter];

        //logMessage("  filter_value " .     $filter_value);
        $status_filter         = new RNCPHP\AnalyticsReportSearchFilter;
        $status_filter->Name   = 'resource_id';

        $status_filter->Values = array($filter_value);
        $filters               = new RNCPHP\AnalyticsReportSearchFilterArray;
        $filters[]             = $status_filter;
        $ar                    = RNCPHP\AnalyticsReport::fetch($report_id);
        $arr                   = $ar->run( 0, $filters );
  
        // Inicio - ALTERNATIVA ENCRIPTADA
  
  
        for ( $i = $arr->count(); $i--; )
        {
          $row = $arr->next();
          $array_response['Tickets'][] = $row;
        }
  
        $response = json_encode($array_response);
        $response = $this->blowfish->encrypt($response, self::KEY_BLOWFISH, 10, 22, NULL);
        $response = base64_encode($response);
        $array_result['respuesta'] = $response;
  
        // FIN - Alternativa Encritpada
  
        $result = json_encode($array_result);
        $this->sendResponse($result);
      }
      else
      {
        $response = $this->responseError(4);
        $this->sendResponse($response);
      }
    }
  } 
  catch (\Exception $e) 
  {
    $response = $this->responseError(4, $e->getMessage());
    $this->sendResponse($response);
  }
}


public function getReporto()
{
  try 
  {
    $id=$_GET["id"];
    $resource_id=$_GET["request_id"];

    $json_data = json_encode( array ('usuario' => 'UserDimacofi', 'accion' => 'getReporto', 'datos' => array('report_id'=> $id,'filter'=> $resource_id) ));
    $array_data = json_decode(utf8_encode($json_data), TRUE);
    
    if (is_array($array_data) and ($array_data != FALSE))
    {
      $indiceDatos   = 'datos';
      $indiceAccion  = 'accion';
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
  
      $indice_report = 'report_id';
      $indice_filter = 'filter';

    
      if (is_numeric($array_data[$indiceDatos][$indice_report])  )
      {
        $report_id             = $array_data[$indiceDatos][$indice_report];
        $filter_value          = $array_data[$indiceDatos][$indice_filter];
        //logMessage("  filter_value " .     $filter_value);
        $status_filter         = new RNCPHP\AnalyticsReportSearchFilter;
        $status_filter->Name   = 'resource_id';
        $status_filter->Values = array($filter_value);
        $filters               = new RNCPHP\AnalyticsReportSearchFilterArray;
        $filters[]             = $status_filter;
        $ar                    = RNCPHP\AnalyticsReport::fetch($report_id);
        $arr                   = $ar->run( 0, $filters );
  
        // Inicio - ALTERNATIVA ENCRIPTADA
  
  
        for ( $i = $arr->count(); $i--; )
        {
          $row = $arr->next();
          $array_response['Tickets'][] = $row;
        }
  
  
        $array_result['respuesta'] = $array_response;
  
        // FIN - Alternativa Encritpada
  
        $result = json_encode($array_result);
        $this->sendResponse($result);
      }
      else
      {
        $response = $this->responseError(4);
        $this->sendResponse($response);
      }
    }
  } 
  catch (\Exception $e) 
  {
    $response = $this->responseError(4, $e->getMessage());
    $this->sendResponse($response);
  }
}

	public function getReport()
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


public function getReportv2()
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

                $indice_report = 'report_id';
                $indice_filter = 'filter';
                if (is_numeric($array_data[$indiceDatos][$indice_report]) )
                {
                        $report_id = $array_data[$indiceDatos][$indice_report];
                        $filter_value= $array_data[$indiceDatos][$indice_filter];

                        $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
                        $status_filter->Name = 'resource_id';
                        $status_filter->Values = array( $filter_value  );
                        $filters = new RNCPHP\AnalyticsReportSearchFilterArray;
                        $filters[] = $status_filter;
                        $ar= RNCPHP\AnalyticsReport::fetch( $report_id);
                        $arr= $ar->run( 0, $filters );

		                // Inicio - ALTERNATIVA ENCRIPTADA


						for ( $i = $arr->count(); $i--; )
						{

							$row = $arr->next();
							$array_response['Tickets'][] = $row;
						}
                        $response = json_encode($array_response);
                        $response = $this->blowfish->encrypt($response, self::KEY_BLOWFISH, 10, 22, NULL);
                        $response = base64_encode($response);
                        $array_result['respuesta'] = $response;

                        // FIN - Alternativa Encritpada


                        $result = json_encode($array_result);
                        $this->sendResponse($result);

                }
                else
                {
                        $response = $this->responseError(4);
                        $this->sendResponse($response);

                }

		}
}



public function setOrderOMNumber()
    {

		$this->load->library('Blowfish', false);
        $this->load->library('ConnectUrl');
		$data_post  = $this->getdataPOST();

		$json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
		$array_data = json_decode(utf8_encode($json_data), true);
		if (empty($_POST))
		{
				$response = $this->responseError(1);
				$this->sendResponse($response);
		}

		$indiceTicket = 'nro_referencia';
		$indiceDatos = 'datos';

		$nro_referencia = $array_data[$indiceDatos][$indiceTicket];

		$this->load->model('custom/ws/TicketModel');   //libreria para tickets

		$obj_incident = $this->TicketModel->getObjectTicket($nro_referencia);


		if ($obj_incident == false)
		{
			$response = $this->responseError(8);
			$this->sendResponse($response);
		}

        $incident = RNCPHP\Incident::fetch($obj_incident->ID);

        try
        {
            $HH                    = $incident->CustomFields->c->id_hh;
            $rutClient             = '';
            $fatherIncident        = '';
            $type_order            = '';
            $refNumber             = $incident->ReferenceNumber;
            $shipping_instructions = $incident->CustomFields->c->shipping_instructions;
            $type_contract         = $incident->CustomFields->c->tipo_contrato;
            if (!empty($incident->CustomFields->DOS->Direccion))
              $rutClient      = $incident->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut;
            if (!empty($incident->CustomFields->OP->Incident))
              $fatherIncident = $incident->CustomFields->OP->Incident->ReferenceNumber;
            if (!empty($incident->Disposition))
            {
              $idDisposition   = $incident->Disposition->ID;
              switch ($idDisposition) {
                case 41:
                  $type_order = 'servicio';
                  break;
                case 40:
                  $type_order = 'taller';
                  break;
                default:
                  $type_order = '';
                  break;
              }
            }

            $shipToCustomerID      = $incident->CustomFields->DOS->Direccion->d_id;
            $a_orderItems          = RNCPHP\OP\OrderItems::find("Incident.ID ='{$incident->ID}'");
            $a_list_items          = array();
            foreach ($a_orderItems as $item)
            {
              if ($item->Enabled === false )
                continue;
              $a_tmp_result['line_id']           = $item->ID;
              $a_tmp_result['Inventory_item_id'] = $item->Product->InventoryItemId;
              $a_tmp_result['ordered_quantity']  = $item->QuantitySelected;
              $a_tmp_result['line_type_id']      = $item->Product->CategoryItem->LookupName;
              $a_list_items[] = $a_tmp_result;
            }

            $array_post     = array("usuario" => "Integer",
                                    "accion"  => "setOrderOM",
                                    "order_detail" => array(
                                      "ref_number_order"      => $refNumber,
                                      "ref_number_ticket"     => $fatherIncident,
                                      "client_rut"            => $rutClient,
                                      "type_order"            => $type_order,
                                      "type_contract"         => $type_contract,
                                      "hh"                    => $HH,
                                      "shipping_instructions" => $shipping_instructions,
                                      "ship_to_customer_id"   => $shipToCustomerID,
                                      "request_name"          => $incident->AssignedTo->Account->DisplayName,
                                      "list_products"         => $a_list_items
                                    ));


            $json_data_post = json_encode($array_post);




            $json_data_post = $this->blowfish->encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
            $json_data_post = base64_encode($json_data_post);


			$postArray = array ('data' => $json_data_post);

      $cfg2 = RNCPHP\Configuration::fetch( CUSTOM_CFG_WS_URL );

      
            //$result = $this->connecturl->requestPost("http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php", $postArray);
            $result = $this->connecturl->requestPost($cfg2->Value, $postArray);
            


            if ($result != false)
            {
                $arr_json = json_decode($result, true);

                if ($arr_json != false)
                {
                    if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json)) ))
                    {

                        $respuesta  = base64_decode($arr_json['respuesta']);

                        //$json = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                        //self::insertPrivateNote($incident, "json respuesta 1:".$arr_json['resultado']);
                        //return;

                        switch ($arr_json['resultado'])
                        {
                            case true:
                                $json = $this->blowfish->decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);


                                $array_data = json_decode(utf8_encode($json), true); //Transformar a array


                                if (!is_array($array_data))
                                {
                                  $message = "ERROR: Estructura JSON encriptado No valida ".PHP_EOL;
                                  $message .= "JSON: ".$json;
                                  $bannerNumber = 3;
                                  break;
                                }

                                if (!array_key_exists('order_number_OM', $array_data))
                                {
                                  $message = "Número de Orden no encontrado ". $json;
                                  $bannerNumber = 3;
                                  break;
                                }

                                $orderOmNumber = $array_data['order_number_OM'];

                                if (!empty($orderOmNumber))
                                {
                                  $incident->CustomFields->c->order_number_om = $orderOmNumber;
                                  $incident->Save(RNCPHP\RNObject::SuppressAll);
                                  $message = "Número de Orden ".$orderOmNumber. " registrada con exito";
                                  $bannerNumber = 1;
                                  break;
                                }
                                else
                                {
                                  $message = "Número de Orden vacia";
                                  $bannerNumber = 3;
                                  break;
                                }

                                break;
                            case false:
                                $message = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                                $bannerNumber = 3;
                                break;
                            default:
                                $message = "ERROR: Estructura JSON No valida R".PHP_EOL;
                                $message .= "JSON: ". $result;
                                $bannerNumber = 3;
                                break;
                          }

                    }
                    else
                    {
                        $message = "ERROR: Estructura JSON No valida ". PHP_EOL;
                        $message .= "JSON: ". $result;

                        $bannerNumber = 3;
                    }
                }
                else
                {
                    $message = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;

                    $bannerNumber = 3;
                }

            }
            else {
                $message = "ERROR: ".ConnectUrl::getResponseError();
                $bannerNumber = 3;
            }


            self::insertPrivateNote($incident, $message);
            self::insertBanner($incident, $bannerNumber);
        }
        catch (RNCPHP\ConnectAPIError $err )
        {
             $message = "Error ".$e->getMessage();

             self::insertPrivateNote($incident, "Error Query: ".$message);
             self::insertBanner($incident, $bannerNumber);
        }


    }

 static function insertPrivateNote($incident, $textoNP)
    {
        try
        {
            $incident->Threads = new RNCPHP\ThreadArray();
            $incident->Threads[0] = new RNCPHP\Thread();
            $incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
            $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
            $incident->Threads[0]->Text = $textoNP;
            $incident->Save(RNCPHP\RNObject::SuppressAll);
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $incident->Subject = "Error" . $err->getMessage();
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            return false;
        }
    }

    static function insertBanner($incident, $typeBanner, $texto = '')
    {
        if (!is_numeric($typeBanner) and $typeBanner > 3 and $typeBanner < 0)
            $typeBanner = 1;

        $texto = '';
        if ($typeBanner == 3)
            $texto = "Error respuesta OM";

        $incident->Banner->Text = $texto;
        $incident->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
        $incident->Save(RNCPHP\RNObject::SuppressAll);

    }


    public function actualizaestado()
    {
     $data= '{"data":[{"ticket":"200605-000063"},{"ticket":"200605-000059"},{"ticket":"200605-000047"},{"ticket":"200612-000333"},{"ticket":"200610-000106"},{"ticket":"181119-000232"},{"ticket":"200617-000336"},{"ticket":"200619-000159"},{"ticket":"200618-000020"},{"ticket":"200619-000080"},{"ticket":"181114-000680"},{"ticket":"200618-000006"},{"ticket":"200618-000047"},{"ticket":"181113-000305"},{"ticket":"200617-000182"},{"ticket":"200617-000181"},{"ticket":"200618-000354"},{"ticket":"181120-000093"},{"ticket":"200701-000072"},{"ticket":"181122-000284"},{"ticket":"200630-000037"},{"ticket":"200622-000085"},{"ticket":"181122-000520"},{"ticket":"181122-000470"},{"ticket":"200618-000210"},{"ticket":"200701-000236"},{"ticket":"200701-000035"},{"ticket":"200703-000015"},{"ticket":"200630-000089"},{"ticket":"200623-000165"},{"ticket":"200624-000301"},{"ticket":"200701-000082"},{"ticket":"200624-000173"},{"ticket":"181122-000471"},{"ticket":"200623-000148"},{"ticket":"200623-000211"},{"ticket":"200624-000144"},{"ticket":"200623-000131"},{"ticket":"200626-000006"},{"ticket":"200701-000031"},{"ticket":"200703-000284"},{"ticket":"200706-000034"},{"ticket":"200706-000228"},{"ticket":"200630-000320"},{"ticket":"200706-000081"},{"ticket":"200706-000111"},{"ticket":"200618-000210"},{"ticket":"200703-000179"},{"ticket":"200626-000088"},{"ticket":"200705-000001"},{"ticket":"181128-000024"},{"ticket":"181130-000105"},{"ticket":"200706-000080"},{"ticket":"181130-000125"},{"ticket":"200706-000060"},{"ticket":"200622-000345"},{"ticket":"200630-000323"},{"ticket":"181129-000602"},{"ticket":"181128-000019"},{"ticket":"200623-000345"},{"ticket":"200630-000152"},{"ticket":"200630-000223"},{"ticket":"200703-000159"},{"ticket":"200630-000331"},{"ticket":"200630-000207"},{"ticket":"200630-000338"},{"ticket":"200701-000148"},{"ticket":"200701-000306"},{"ticket":"200702-000237"},{"ticket":"200701-000369"},{"ticket":"200703-000167"},{"ticket":"200703-000163"},{"ticket":"200706-000288"},{"ticket":"200701-000347"},{"ticket":"200701-000076"},{"ticket":"200630-000359"},{"ticket":"200630-000340"},{"ticket":"200622-000180"},{"ticket":"200630-000228"},{"ticket":"200702-000194"},{"ticket":"200702-000244"},{"ticket":"200630-000357"},{"ticket":"200702-000223"},{"ticket":"200701-000363"},{"ticket":"200706-000297"},{"ticket":"200630-000185"},{"ticket":"200703-000154"},{"ticket":"200701-000371"},{"ticket":"200630-000047"},{"ticket":"200701-000367"},{"ticket":"200701-000155"},{"ticket":"200622-000149"},{"ticket":"200624-000158"},{"ticket":"200625-000123"},{"ticket":"200623-000419"},{"ticket":"200626-000221"}]}';
      $data2=json_decode($data);
         
      foreach ($data2->data as $ticket)
      {
        
        $obj_incident = RNCPHP\Incident::fetch($ticket->ticket);
        $obj_incident->StatusWithType->Status->ID =140;
        $obj_incident->Save(RNCPHP\RNObject::SuppressAll);
        echo $ticket->ticket . '-' . $obj_incident->StatusWithType->Status->ID . '<br>';
      }

//$this->sendResponse($data);
    }
    /* Cierra Envios despuesde X horas */
    public function CloseTicketDespachado()
    {
      $listaTicket= array();
      
      $this->load->model('custom/ws/TicketModel');   //libreria para tickets
      $Close_Santiago = RNCPHP\Configuration::fetch( CUSTOM_CFG_CLOSE_INSUMOS_SANTIAGO );
      $Close_Regiones = RNCPHP\Configuration::fetch( CUSTOM_CFG_CLOSE_INSUMOS_REGIONES );
   
      echo "METROPOLITANA <br>";

        $report_id = 101977 ; // Solicitudes de Despacho Insumos RM
        $filter_value= 1;
        $nro_referencia="";
        $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
				$status_filter->Name = 'resource_id';
				$status_filter->Values = array( $filter_value  );
				$filters = new RNCPHP\AnalyticsReportSearchFilterArray;
				$filters[] = $status_filter;
				$ar= RNCPHP\AnalyticsReport::fetch( $report_id);
        $arr= $ar->run( 0, $filters );
        $k=0;
        $i=0;

       
        while($row = $arr->next())
        {
          $borrando=$borrando ."-Cerrando " .$i;

          $nro_referencia = $row["ref_no"];
          $DIFF = $row["DIFF"];
          //$this->sendResponse("-->" .$row["ref_no"]);
          //$this->sendResponse("-->" .$nro_referencia);

          //$obj_incident = $this->TicketModel->getObjectTicket($nro_referencia);
          $listaTicket[$k]= RNCPHP\Incident::fetch($nro_referencia);
					if ($listaTicket[$k] == false)
					{
						$response = $this->responseError(8);
            //echo $response ."<br>";
          }

          echo $arr->count() .'-' . $i.'-' . $nro_referencia. "-"  .$DIFF  . "-" .json_encode($listaTicket[$k]->ID) . "-" . json_encode($listaTicket[$k]->ReferenceNumber) . "-" . $listaTicket[$k]->StatusWithType->Status->ID . '<br>';
          echo $k . '-' .floatval($DIFF) . '-' .floatval($Close_Santiago->Value) . '<br>';

          

          if(floatval($DIFF)>=floatval($Close_Santiago->Value))
          {
            echo "DIFF "  . $DIFF . "- " . $nro_referencia . "-". $listaTicket[$k]->StatusWithType->Status->ID." <br>"; 
         
                if($listaTicket[$k]->StatusWithType->Status->ID==111 || $listaTicket[$k]->StatusWithType->Status->ID==140 || $listaTicket[$k]->StatusWithType->Status->ID==112)
                { 
                    echo $listaTicket[$k]->ReferenceNumber . "<br>";
                    $listaTicket[$k]->StatusWithType->Status->ID    = 2; // Cambio de estado
                    $listaTicket[$k]->Save(RNCPHP\RNObject::SuppressAll);
                    $i++;
                    echo $arr->count() .'-' . $i.'-' . $nro_referencia. "-"  .$DIFF  . "-" .json_encode($listaTicket[$k]->ID) . "-" . json_encode($listaTicket[$k]->ReferenceNumber) . "-" . $listaTicket[$k]->StatusWithType->Status->ID . '<br>';
                }
          }
          $k++;
        }
        //$this->sendResponse($i . "-" . $j);
        $j=$i;
        echo "REGIONES <br>";
        $report_id = 101978 ; // Solicitudes de Despacho Insumos RG
        $filter_value= 1;
        $nro_referencia="";
        $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
				$status_filter->Name = 'resource_id';
				$status_filter->Values = array( $filter_value  );
				$filters = new RNCPHP\AnalyticsReportSearchFilterArray;
				$filters[] = $status_filter;
				$ar= RNCPHP\AnalyticsReport::fetch( $report_id);
        $arr= $ar->run( 0, $filters );
        $i=0;
        while($row = $arr->next())
        {


          $nro_referencia = $row["ref_no"];
          $DIFF = $row["DIFF"];
          
          //$this->sendResponse("-->" .$row["ref_no"]);
          //$this->sendResponse("-->" .$nro_referencia);

          $listaTicket[$k] = RNCPHP\Incident::fetch($nro_referencia);
          echo $arr->count() .  '...' . floatval(floatval($DIFF)-floatval(175500))    . '...' . $i.'-' . $nro_referencia. "-"  .$DIFF  . "-" .json_encode($listaTicket[$k]->ID) . "-" . json_encode($listaTicket[$k]->ReferenceNumber) . "-" . $listaTicket[$k]->StatusWithType->Status->ID . '<br>';
          echo $k . '-' .floatval($DIFF) . '-' .floatval($Close_Regiones->Value) . '<br>';;
          if(floatval($DIFF)>=floatval($Close_Regiones->Value))
          {
            echo "DIFF "  . $DIFF . "- " . $nro_referencia . "-". $listaTicket[$k]->StatusWithType->Status->ID." <br>"; 
            try{
                if($listaTicket[$k]->StatusWithType->Status->ID==111 || $listaTicket[$k]->StatusWithType->Status->ID==140 || $listaTicket[$k]->StatusWithType->Status->ID==112)
                { 
                    echo $listaTicket[$k]->ReferenceNumber . "<br>";
                    $listaTicket[$k]->StatusWithType->Status->ID    = 2; // Cambio de estado
                    $listaTicket[$k]->Save(RNCPHP\RNObject::SuppressAll);
                    $i++;
                    echo $arr->count() .'-' . $i.'-' . $nro_referencia. "-"  .$DIFF  . "-" .json_encode($listaTicket[$k]->ID) . "-" . json_encode($listaTicket[$k]->ReferenceNumber) . "-" . $listaTicket[$k]->StatusWithType->Status->ID . '<br>';
                }
              }
             catch ( RNCPHP\ConnectAPIError $err )
             {
               $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
               $respuestaApi['resultado']='false';
               $respuestaApi['respuesta']['glosa']=$this->error;
               //RNCPHP\ConnectAPI::rollback();
               $this->sendResponse(json_encode($respuestaApi));
               return;
             }
             //$this->sendResponse($arr->count());

            
          }
          $k++;
        /* if ($i==100)
         {
           return;
         }*/
        }
        //$this->sendResponse($i . "-" . $j);
        return;

      
       
    }

    /* Cierra Tickets  Mi Dimacofi en estado Ingresado  despues de 2 horas*/ 
    public function CloseTicketIngresado()
    {
 
      $data_post  = $this->getdataPOST();
      $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
      $array_data = json_decode(utf8_encode($json_data), true);

      $this->load->model('custom/ws/TicketModel');   //libreria para tickets

      //  Looop Start
        $report_id = 101964 ; // Solicitudes de Repuestos Sin Abastecer
        $filter_value= 1;
        $nro_referencia="";
        $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
				$status_filter->Name = 'resource_id';
				$status_filter->Values = array( $filter_value  );
				$filters = new RNCPHP\AnalyticsReportSearchFilterArray;
				$filters[] = $status_filter;
				$ar= RNCPHP\AnalyticsReport::fetch( $report_id);
        $arr= $ar->run( 0, $filters );
        
        for ( $i = $arr->count(); $i--; )
				{
          $row = $arr->next();
          $borrando=$borrando ."-Borrando " .$i;

          $nro_referencia = $row["ref_no"];
          //$this->sendResponse("-->" .$row["ref_no"]);
          //$this->sendResponse("-->" .$nro_referencia);

          $obj_incident = $this->TicketModel->getObjectTicket($nro_referencia);
         
					if ($obj_incident == false)
					{
						$response = $this->responseError(8);
						$this->sendResponse($response);
					}
					$obj_status = $this->TicketModel->setIncidentState($obj_incident, 149);
					if ($obj_status == false)
					{

						//$response = $this->responseError(4, $this->TicketModel->getLastError());
						$response = $this->responseError(4);
						$this->sendResponse($response);
					}
        }

        
        $this->sendResponse($borrando);
    }


    public function setMultiIncidentState()
	    {

	        $data_post  = $this->getdataPOST();
	        $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
	        $array_data = json_decode(utf8_encode($json_data), true);
	/*
	        if (empty($_POST))
	        {
	            $response = $this->responseError(1);
	            $this->sendResponse($response);
	        }
	*/
	//        if (is_array($array_data) and ($array_data != false))
	        if (1)
	        {
	/*
	            $indiceAccion = 'accion';
	            $indiceUsuario = 'usuario';

	            if (!array_key_exists($indiceAccion, $array_data) and !array_key_exists($indiceUsuario, $array_data))
	            {
	                $response = $this->responseError(3);
	                $this->sendResponse($response);
	            }

	            if ($array_data[$indiceUsuario] != self::USER)
	            {
	                $response = $this->responseError(5);
	                $this->sendResponse($response);
	            }
	*/
	            $indiceTicket = 'Solicitud';
	            $indiceTicketPadre = 'Padre';


				$this->load->model('custom/ws/TicketModel');   //libreria para tickets

	// Looop Start

				$report_id = 100749 ; // Solicitudes de Repuestos Sin Abastecer
	 			$filter_value= 1;
	 			$nro_referencias="";

				$status_filter= new RNCPHP\AnalyticsReportSearchFilter;
				$status_filter->Name = 'resource_id';
				$status_filter->Values = array( $filter_value  );
				$filters = new RNCPHP\AnalyticsReportSearchFilterArray;
				$filters[] = $status_filter;
				$ar= RNCPHP\AnalyticsReport::fetch( $report_id);
				$arr= $ar->run( 0, $filters );

				// Inicio - ALTERNATIVA ENCRIPTADA


				for ( $i = $arr->count(); $i--; )
				{

					$row = $arr->next();
					$array_response['Tickets'][] = $row;

					$nro_referencia = $row[$indiceTicket];
					$nro_referencia_Padre = $row[$indiceTicketPadre];
					$estado = $array_data[$indiceDatos][$indiceEstado];

					$obj_incident = $this->TicketModel->getObjectTicket($nro_referencia);
					if ($obj_incident == false)
					{
						$response = $this->responseError(8);
						$this->sendResponse($response);
					}
					$obj_cotiza = $this->TicketModel->setIncidentState($obj_incident, 157);
					if ($obj_cotiza == false)
					{

						//$response = $this->responseError(4, $this->TicketModel->getLastError());
						$response = $this->responseError(4);
						$this->sendResponse($response);
					}
          if(strcmp($row["Disposición"],"Insumo"))
          {

  					$obj_incident = $this->TicketModel->getObjectTicket($nro_referencia_Padre);
  					if ($obj_incident == false)
  					{
  						$response = $this->responseError(8);
  						$this->sendResponse($response);
  					}

  					$obj_cotiza = $this->TicketModel->setIncidentState($obj_incident, 157);
  					if ($obj_cotiza == false)
  					{
  						//$response = $this->responseError(4, $this->TicketModel->getLastError());
  						$response = $this->responseError(4);
  						$this->sendResponse($response);
  					}
          }
					$nro_referencias= $nro_referencias .  $nro_referencia ;

				}
	// Looop Stop


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
      


      public function setCloseMultiInsumos()
	    {

	        $data_post  = $this->getdataPOST();
	        $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
	        $array_data = json_decode(utf8_encode($json_data), true);
	/*
	        if (empty($_POST))
	        {
	            $response = $this->responseError(1);
	            $this->sendResponse($response);
	        }
	*/
	//        if (is_array($array_data) and ($array_data != false))
	        if (1)
	        {
	/*
	            $indiceAccion = 'accion';
	            $indiceUsuario = 'usuario';

	            if (!array_key_exists($indiceAccion, $array_data) and !array_key_exists($indiceUsuario, $array_data))
	            {
	                $response = $this->responseError(3);
	                $this->sendResponse($response);
	            }

	            if ($array_data[$indiceUsuario] != self::USER)
	            {
	                $response = $this->responseError(5);
	                $this->sendResponse($response);
	            }
	*/
	            $indiceTicket = 'Solicitud';
	            $indiceTicketPadre = 'Padre';


				$this->load->model('custom/ws/TicketModel');   //libreria para tickets

	// Looop Start

				$report_id = 101744 ; // Solicitudes de Repuestos Sin Abastecer
	 			$filter_value= 1;
	 			$nro_referencias="";

				$status_filter= new RNCPHP\AnalyticsReportSearchFilter;
				$status_filter->Name = 'resource_id';
				$status_filter->Values = array( $filter_value  );
				$filters = new RNCPHP\AnalyticsReportSearchFilterArray;
				$filters[] = $status_filter;
				$ar= RNCPHP\AnalyticsReport::fetch( $report_id);
				$arr= $ar->run( 0, $filters );

				// Inicio - ALTERNATIVA ENCRIPTADA


				for ( $i = $arr->count(); $i--; )
				{

					$row = $arr->next();
					$array_response['Tickets'][] = $row;

					$nro_referencia = $row[$indiceTicket];
					$nro_referencia_Padre = $row[$indiceTicketPadre];
					$estado = $array_data[$indiceDatos][$indiceEstado];

					$obj_incident = $this->TicketModel->getObjectTicket($nro_referencia);
					if ($obj_incident == false)
					{
						$response = $this->responseError(8);
						$this->sendResponse($response);
					}
					$obj_cotiza = $this->TicketModel->setIncidentState($obj_incident, 157);
					if ($obj_cotiza == false)
					{

						//$response = $this->responseError(4, $this->TicketModel->getLastError());
						$response = $this->responseError(4);
						$this->sendResponse($response);
					}
          if(strcmp($row["Disposición"],"Insumo"))
          {

  					$obj_incident = $this->TicketModel->getObjectTicket($nro_referencia_Padre);
  					if ($obj_incident == false)
  					{
  						$response = $this->responseError(8);
  						$this->sendResponse($response);
  					}

  					$obj_cotiza = $this->TicketModel->setIncidentState($obj_incident, 157);
  					if ($obj_cotiza == false)
  					{
  						//$response = $this->responseError(4, $this->TicketModel->getLastError());
  						$response = $this->responseError(4);
  						$this->sendResponse($response);
  					}
          }
					$nro_referencias= $nro_referencias .  $nro_referencia ;

				}
	// Looop Stop


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
	public function getReportFilter()
	{
		$indiceDatos = 'datos';
		$indiceAccion = 'accion';
		$indiceUsuario = 'usuario';
		$indice_report = 'report_id';
		$indice_filter = 'filter';
        $array_data = array ('usuario' => 'UserDimacofi', 'accion' => 'getReportv', 'datos' => array('report_id'=> 100752,'filter'=> '160908-000141') );


                        $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
                        $status_filter->Name = 'resource_id';

						$status_filter->Values       = array();
						$val=$array_data[$indiceDatos][$indice_filter];
						$status_filter->Values[] = $val;

						$status_filter->Operator     = new RNCPHP\NamedIDOptList();
      					$status_filter->Operator->ID = 1; //4 es igual a menor, 5 es igual a mayor.

                        $filters = new RNCPHP\AnalyticsReportSearchFilterArray;
                        $filters[] = $status_filter;
                        $ar= RNCPHP\AnalyticsReport::fetch( 100752);
                        $arr= $ar->run( 0, $filters );

		                // Inicio - ALTERNATIVA ENCRIPTADA


						for ( $i = $arr->count(); $i--; )
						{

							$row = $arr->next();
							$array_response['Tickets'][] = $row;
						}
                        $response = json_encode($array_response);
                        $response = $this->blowfish->encrypt($response, self::KEY_BLOWFISH, 10, 22, NULL);
                        $response = base64_encode($response);
                        $array_result['respuesta'] = $array_response;

                        // FIN - Alternativa Encritpada


                        $result = json_encode($array_result);
                        $this->sendResponse($result);

	}

  public function GetBlockedStatus()
  {


    ?>
<!DOCTYPE html>
<html>
<head>
<style>
  body {background-color: white;}
  h1   {color: black;}
  p    {color: red;}


  table {
          border:1px solid black;
          width:"100%";
        }
        
  th    {
          text-align: left;
          border:1px solid black;
          }
  tr      {text-align:right}
  td      {
            text-align:right;
            border:1px solid black;
          }
</style>
</head>
<body>
    <?
    $this->load->model('custom/GeneralServices');

    $parametros=$_GET;

   
    $incident = RNCPHP\incident::fetch($parametros["p_iid"]);
    //echo "[" .json_encode($parametros) . "]";
    //echo  json_encode($incident->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut);

    $OrgData = $this->GeneralServices->getOrganizationStatusbyRut($incident->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut);

    $Organizacion = RNCPHP\Organization::first( "CustomFields.c.rut = '".$OrgData->Customer->CustomerData->Customer->tRUT."'");

    setlocale(LC_MONETARY, 'es_CL');

    ?>




    <h3>
        <p>
        <h1>Nuestros Sistemas indican que existen restricciones para generar solicitudes</h1>
        </p>
    </h3>

    <table >
        <tr>
            <th>RUT</th>
            <th ><?=$OrgData->Customer->CustomerData->Customer->tRUT?>
            
            </th>
        </tr>
        <tr>
            <th >Cliente</th>
            <th ><?=$Organizacion->LookupName?></th>
        </tr>
        <tr>
            <th >Bloqueado por Deuda Morosa</th>
            <th >
                <?=$OrgData->Customer->CustomerData->Customer->tbloqued?></th>
                <th > <button type="button">Enviar Mensaje Por Deuda Morosa</button></th >
                <rn:widget path="custom/reports/IntegerGrid" report_id="102037"
                json_filters="#rn:php:json_encode($a_Filters2)#" per_page="200" show_paginator="false"
                url_per_col="/app/reparacion/special/special_detail/ref_no/" col_id_url="1" />
        </tr>
        <tr>
            <th >Bloqueado por Rechazo de Facturas</th>
            <th >
                <?=$OrgData->Customer->CustomerData->Customer->tBLOQUEO_FACTURACION?></th>
                <th > <button type="button">Click Me!</button></th >
        </tr>
        <tr>
            <th >Bloqueado por Informacion Financiera incompleta</th>
            <th >
                <?=$OrgData->Customer->CustomerData->Customer->tBLOQUEO_INFORMACION?></th>
                <th > <button type="button">Click Me!</button></th >
        </tr>
        <tr>
            <th >Bloqueado por Situacion de Riesgo</th>
            <th >
                <?=$OrgData->Customer->CustomerData->Customer->tBLOQUEO_RIESGO?></th>
                <th > <button type="button">Click Me!</button></th >
        </tr>
        <tr>
            <th >Bloqueado por Castigo de Deudas Antiguas</th>
            <th >
                <?=$OrgData->Customer->CustomerData->Customer->tBLOQUEO_DEUDAS?></th>
                <th > <button type="button">Click Me!</button></th >
        </tr>
    </table>


    <br>

    <table >

        <tr >
            <th>Nro Factura</th>
            <th>Contrato</th>
            <th>Monto</th>
            <th>Fecha</th>
        </tr>
        <?
    //facturas inpagas
setlocale(LC_MONETARY, 'es_CL');
    foreach ( $OrgData->Invoice->InvoiceData->Invoices as $Invoice)
    {
      ?>
        <tr >
            <td><?=$Invoice->TRX_NUMBER?></th>
            <td><?=($Invoice->CT_REFERENCE)?$Invoice->CT_REFERENCE:'(Sin Valor)' ?></th>
            <td><?= money_format('%.0n', $Invoice->AMOUNT) ?></th>
            <td><?=$Invoice->DUE_DATE?></th>
        </tr>

        <?
    }

    ?>
    </table>
</body>

</html>

<?

  }


  public function getpdftest()
  {

    $jsonArray = json_decode( $_POST['json'], true );
$tmpName = tempnam(sys_get_temp_dir(), 'data');
$file = fopen($tmpName, 'w');

fputcsv($file, $jsonArray);
fclose($file);

header('Content-Description: File Transfer');
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=data.csv');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($tmpName));

ob_clean();
flush();
readfile($tmpName);

unlink($tmpName);

/*
    $data_post  = $this->getdataPOST();
    $array_data = json_decode(utf8_encode($json_data), true);

    $file = '/tmp/invoice.pdf';
    file_put_contents($file, $array_data->data);
    
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
    
*/
  }

  public function testX()
  {

    //$hh = RNCPHP\Asset::fetch(41685);
    $value='1008481';
    $obj_incident_temp = RNCPHP\Incident::first("CustomFields.c.guide_dispatch='" . $value  . "'" );

    if($obj_incident_temp->CustomFields->OP->Incident->ReferenceNumber)
          {
            echo "REPUESTO";
            $obj_incident=$obj_incident_temp->CustomFields->OP->Incident;
          }
          else
          {
            echo "INSUMO";
            $obj_incident=$obj_incident_temp;
          }
    echo $obj_incident->ReferenceNumber;
    return;


    date_default_timezone_set('UTC');
    $delta=time()-100*60*60*24;
    $sql="Asset.ID = 50500 "   ;
    echo $sql . '<br>';
    $contadores = RNCPHP\DOS\Contador::find($sql);
    echo count($contadores) . '<br>';

    
    foreach ($contadores as $z)
    {
      if($z->UpdatedTime-$delta>0)
      {
      echo  $z->ID  .'-' . $z->UpdatedTime . '- '. $z->Valor .'-'.  $delta .'-' . time()  . '-'. $z->TipoContador->ID .'<br>';
      }
    }


    $BN=0;
    $Color=0;
    $id_BN=0;
    $id_Color=0;
    $indice_color=0;
    $indice_bn=0;

    foreach($contadores as $key => $value)
         {
          if($value->UpdatedTime-$delta>0)
          {
          
             if( ($value->TipoContador->ID==1 or $value->TipoContador->ID==13 or $value->TipoContador->ID==16 ) and $value->ID>=$indice_bn )
              {
              
                $copia_BN=$value;
                $BN=$value->Valor;
                $id_BN=$value->ContadorID;
                $id_BN_type=$value->TipoContador->ID;
                $indice_bn=$value->ID;
            
              }

              if( ($value->TipoContador->ID==2 or $value->TipoContador->ID==14 ) and $value->ID>=$indice_color )
              {
                $copia_Color=$value;
                $Color=$value->Valor;
                $id_Color=$value->ContadorID;
                $id_Color_type=$value->TipoContador->ID;
                $indice_color=$value->ID;
              }
          }
         }
    //echo json_encode($contadores);

    echo $BN . '-' .  $Color .'<br>';
    echo $indice_color . '-' .  $indice_bn .'<br>';
    return;
    //$this->load->library('ConnectUrl');
    // MIDDLE WARE ANTIGUO IIA
    //$result =  $this::requestPost("http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php",null);
    // MIDDLEWARE OCI 
    $result =  $this::requestPost("http://129.151.120.248:8080/dts/rn_integracion/rntelejson.php",null);
    // MIDDLEWARE OCI  Acceso de contingencia creado por Renato
    //$result =  $this::requestPost("http://129.151.104.201:8080/dts/rn_integracion/rntelejson_test.php",null);
 
    echo $result;
    return;
  }


  public function testX2()
  {



    $incident = RNCPHP\Incident::fetch('190130-000255');
    echo json_encode($incident) .'<br>';
    echo ($incident->CustomFields->OP->Incident->ReferenceNumber);
    
    $IncidentR  = RNCPHP\Incident::find(" CustomFields.OP.Incident.ID = " . $incident->ID ); 

    echo '<br>';

    echo json_encode($IncidentR) .'<br>';

    foreach($IncidentR as $i)
    {
        echo json_encode($i->StatusWithType->Status->ID);
        echo '<br>';
        echo json_encode($i->StatusWithType->Status->LookupName);
        echo '<br>';
        $i->StatusWithType->Status->ID=2;
        $i->Save(RNCPHP\RNObject::SuppressAll);
    }
    return;
    $this->conditionsInfo = array();
    $this->conditionsInfo[] = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_CONDITIONS_INFO_6);

    echo json_encode($this->conditionsInfo[0]->Value);
    return;
    $array_Account_obj = RNCPHP\Account::find("CustomFields.c.resource_id = ". 100239757);
    echo json_encode($array_Account_obj);
    return;


    $res = RNCPHP\ROQL::queryObject( "SELECT  Contact FROM Contact C 
    WHERE login='rtorrenscler@gmail.com'" )->next();

  if($contact = $res->next()) {
printf("Here is the contact ID: %d <br />", json_encode($contact->ID));
}
else 
{
  printf("Sin Datos");
}
    //$res = RNCPHP\ROQL::queryObject("SELECT ID FROM Contact     ORDER BY ID DESC LIMIT 10 "  )->next();
    echo json_encode($res);
    return;
    $incident = RNCPHP\Incident::fetch('221122-000451');
    echo json_encode($incident->CustomFields->OP->Incident);
    return;

    //221018-000157 No se puede
    $incident = RNCPHP\Incident::fetch('1093259');
    $Organization = RNCPHP\Organization::fetch(56919);
    $incident->Organization=$Organization;
    $incident->Save(RNCPHP\RNObject::SuppressAll);
    echo json_encode($incident->Organization->ID);
    echo "<br>";
    echo json_encode($incident->PrimaryContact->Organization->ID);
    echo "<br>";
    echo json_encode($incident->CustomFields->DOS->Direccion->Organization->ID);
    echo "<br>";
    echo "<br>";

    //221110-000098 No se puede
    $incident = RNCPHP\Incident::fetch(1100984);
    echo json_encode($incident->Organization->ID);
    echo "<br>";
    echo json_encode($incident->PrimaryContact->Organization->ID);
    echo "<br>";
    echo json_encode($incident->CustomFields->DOS->Direccion->Organization->ID);
    echo "<br>";
    echo "<br>";
    //221109-000371 ok
    $incident = RNCPHP\Incident::fetch(1100665);
    echo json_encode($incident->Organization->ID);
    echo "<br>";
    echo json_encode($incident->PrimaryContact->Organization->ID);
    echo "<br>";
    echo json_encode($incident->CustomFields->DOS->Direccion->Organization->ID);
    echo "<br>";
    echo "<br>";
    return;

    $incident = RNCPHP\Incident::fetch(1090521);
    $f_count = count($incident->FileAttachments);
    foreach ($incident->FileAttachments as $i)
    {
      if(substr($i->FileName,0,3)=='OT-')
      {
       $link='<a href="https://soportedimacoficl.custhelp.com/ci/fattach/get/'. $i->ID .'/'. $i->CreatedTime .'/filename/'. $i->FileName .'">' . $i->FileName .'</a><br>';
       echo $link;
      }
    }
    return;
  }

  public function testXX()
  {
/*
    
    $this->load->model('custom/ws/OpportunityModel');
    //$obj_line=RNCPHP\OP\OrderItems::find("Opportunity.ID = 23445    and Enabled = 1");
    //$obj_line=RNCPHP\OP\OrderItems::find("Opportunity.ID = {$op_id} and Enabled = 1");
    //$opportunity = RNCPHP\Opportunity::fetch(23445);
    $obj_line = $this->OpportunityModel->getItems(23445);
    foreach($obj_line as $key => $line)
    {
      echo  json_encode($line) .'<br>';
      $tempValue                     = $line->UnitTempSellPrice  * $line->QuantitySelected;
      $discountPrice                 = ($tempValue * $line->Discount)/100;
      echo  $tempValue .'<br>';
      echo  $discountPrice .'<br>';
      echo  $line->Discount .'<br>';
      echo  $line->DiscountSellPrice .'<br>';

    }

    return;


    $json_data='{"dolar":{"values":{"CODIGO_PRODUCTO":"38521","DESCRIPCION_PRODUCTO":"DEVELOPER TYPE F CYAN RICOH","VALOR_US":"39.27694064"}}}';

    $data=json_decode($json_data);
    echo  json_encode($data->dolar->values->VALOR_US);
    if($data->dolar->values->VALOR_US>=0)
    {
        $opportunity = RNCPHP\Opportunity::fetch(23041);
       

        $obj_product                   = RNCPHP\OP\Product::first("CodeItem =  '{$data->dolar->values->CODIGO_PRODUCTO}' ");

        echo "-->". $data->dolar->values->CODIGO_PRODUCTO .'-'. json_encode($obj_product->ID);
       
        $idOP                          = $opportunity->ID;
        $obj_line                      = RNCPHP\OP\OrderItems::first("Product.ID =  '{$obj_product->ID}' and Opportunity.ID = {$idOP} ");


        echo  json_encode($obj_line);
        echo 'ConfirmedCost     :' . $obj_line->ConfirmedCost .' <br>';
        echo 'Enabled           :' . $obj_line->Enabled .' <br>';
        //echo 'State             :' . json_encode($obj_line->State) .' <br>';
        echo 'Alternative       :' . $obj_line->Alternative .' <br>';
        //echo 'RefLineOM         :' . json_encode($obj_line->RefLineOM) .' <br>';
        //echo 'Opportunity       :' . json_encode($obj_line->Opportunity) .' <br>';
        echo 'DiscountSellPrice :' . $obj_line->DiscountSellPrice .' <br>';
        echo 'UnitTempSellPrice :' . $obj_line->UnitTempSellPrice .' <br>';
        
        


        //$obj_line->UnitTempSellPrice   = 'TEST';
        //$obj_line->Save();
        
       
        
        


        $a_orderItems1          = RNCPHP\OP\OrderItems::find('Opportunity.ID =' . $idOP);
        echo json_encode($a_orderItems1);
        $a_orderItems1[0]->UnitTempSellPrice   = 'TEST';
        $a_orderItems1[0]->Save();

        echo 'ConfirmedCost     :' . $a_orderItems1[0]->ConfirmedCost .' <br>';
        echo 'Enabled           :' . $a_orderItems1[0]->Enabled .' <br>';
        //echo 'State             :' . json_encode($obj_line->State) .' <br>';
        echo 'Alternative       :' . $a_orderItems1[0]->Alternative .' <br>';
        //echo 'RefLineOM         :' . json_encode($obj_line->RefLineOM) .' <br>';
        //echo 'Opportunity       :' . json_encode($obj_line->Opportunity) .' <br>';
        
        echo 'DiscountSellPrice :' . $a_orderItems1[0]->DiscountSellPrice .' <br>';
        echo 'UnitTempSellPrice :' . $a_orderItems1[0]->UnitTempSellPrice .' <br>';

    }
    return;



  //  $opportunity = RNCPHP\Opportunity::fetch(23006);
   //echo  json_encode($opportunity->CustomFields->c);
    //return;

    $CI =& get_instance();
    $CI->load->model('custom/ConnectUrl');
    $url = "https://api.dimacofi.cl/token";

    $data           = array("grant_type" => "client_credentials");
    $consumerKey    = "yh8wgLIb4RLIHwQ868CIifi2EYca"; // Prod 
            $consumerSecret = "bfaZkjfdIWoEtiXoDbo4E_EPpAka"; // Prod
    //$consumerKey    = "5_127oyLQSwQ_yA7HpRXAAvEcBoa";
    //$consumerSecret = "HzHL7WmxxY7Y4nqWVg0uzqSKDmga";
  
    $tokenA = $CI->ConnectUrl->requestCURLByPost($url, $data, $consumerKey . ":" . $consumerSecret);
    $a_jsonToken = json_decode($tokenA, TRUE);
    $token = $a_jsonToken["access_token"];
    echo $token;
    
    $jsonData = new \stdClass();
     $a_request = array(
                  "RUT" => '61102022-8',
                  "HH" => '2110589'
              );
   
  
    //$jsonDataEncoded ='{"id_ticket":"221111-0002188","tipo_ticket":28,"estado":134}';
    $jsonDataEncoded ='{}';
    $service=$CI->ConnectUrl->requestCURLJsonRaw("https://api.dimacofi.cl/CustomerDataInfo/getUSDValue", $jsonDataEncoded, $token);
    //$service = $CI->ConnectUrl->requestCURLJsonRaw('https://api.dimacofi.cl/sucursalVirtual/consulta/consumos/resumen', $jsonDataEncoded);
    $data=json_decode($service);
    echo "[" . $data->dolar->values->CONVERSION_RATE *10 ."]";
    $this->sendResponse($service);
    
    return;

    $incident = RNCPHP\Incident::fetch(1090521);
    echo json_encode($incident->StatusWithType->Status->ID);
    return;
    */
    $report_id = 102309 ;
    $filter_value= '69070600-8';
    //$filter_hh='4034750';
    $nro_referencias="";

    $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
    $status_filter->Name = 'rut_org';
    $status_filter->Values = array( "'','76215893-0','76186954-K'");
    $status_filter->Operator->ID=10;
    $filters = new RNCPHP\AnalyticsReportSearchFilterArray; 
    $filters[] = $status_filter;

    $disp=8;
    $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
    $status_filter->Name = 'disp';
    $status_filter->Values = array( $disp );
    //$status_filter->Operator->ID=1;
    $filters[] = $status_filter;

    if($filter_hh)
    {
    $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
    $status_filter->Name = 'hh';
    $status_filter->Values = array( $filter_hh );
    $status_filter->Operator->ID=1;
    //$filters = new RNCPHP\AnalyticsReportSearchFilterArray; 
    $filters[] = $status_filter;
    }
    $ar= RNCPHP\AnalyticsReport::fetch( 102309);
    $arr= $ar->run(0,$filters);

    

    
    for ( $i = $arr->count(); $i--; )
    {
      $row = $arr->next();
     
     
        $array_response['Tickets'][] = $row;
        
    }
    $this->sendResponse(json_encode($array_response));
 




    return json_decode($response);
    
    
   
    $a_request = new \stdClass();
    $a_request->id_reporte=102274;
    $a_request->status_values=array('');
    $a_request->ruts = "'69070600-8'";
    $a_request->tipo_soporte=array("Insumo");
    $Trx=$this->GeneralServices->BuscaReporte($a_request);
    echo json_encode($Trx->Tickets);

    $this->sendResponse(json_encode($incident));
   return;
    if($incident->CustomFields->OP->Incident->CustomFields->c->ar_flow->ID==282)
    {
      $incident->CustomFields->OP->Incident->AssignedTo->Account=$incident->AssignedTo->Account;
      $incident->CustomFields->OP->Incident->Save(RNCPHP\RNObject::SuppressAll);
      
    }
    $result = $this->sendResponse(json_encode($incident->CustomFields->OP->Incident->CustomFields->c->ar_flow->ID));

     
     //$result = $this->connecturl->requestPost("http://190.14.56.27:8080/dts/rn_integracion/test2.php");
     $cfg2 = RNCPHP\Configuration::fetch( CUSTOM_CFG_WS_URL );
     echo "conectando a ..-> "  . $cfg2->Value . " <br>";
     $result = $this->connecturl->requestPost($cfg2->Value);
     
     //$result = $this->connecturl->requestPost("http://129.151.101.81:8080/dts/rn_integracion/rntelejson.php");
     echo $result;
     exit;
     //$this->sendResponse(json_encode($result));
    $a_suppliers       = RNCPHP\OP\SuppliersRelated::find("Product.ID =31626 and (EnabledSupplierRequest = 1 or EnabledSupplierRequest is null)");
    $a_colorItems  =  $a_suppliers;
    
    foreach ($a_colorItems as $supplier_tmp)
    { 
        $supplier=$supplier_tmp->Supplier;
          //Sugerido consumo
          
          
          if($supplier->InputCartridgeType->TonerType=='Black')
          {
            $consumption    = $counterBN - $lastSuppliersIncident->CustomFields->c->cont1_hh;
            
            //$sugerido  = self::calculo_percent($i,$consumption,$rendimientoBNReal,$supplier,$counterBN,$lastSuppliersIncident->CustomFields->c->cont1_hh);
          }
          else
          {

            $consumption    = $counterColor + $counterBN - $lastSuppliersIncident->CustomFields->c->cont2_hh - $lastSuppliersIncident->CustomFields->c->cont1_hh;
            //self::insertPrivateNote($i, 'CONSUMO COLOR  ->'.json_encode($consumption) . '- '. $counterColor .'-' .$lastSuppliersIncident->CustomFields->c->cont2_hh, 3);
           // $sugerido  = self::calculo_percent($i,$consumption,$rendimientoColorReal,$supplier,$counterColor,$lastSuppliersIncident->CustomFields->c->cont2_hh);
          }

          
          $a_response['message_color'] =$a_response['message_color']  . '-<br>' . $sugerido['message_color'];

          

          
          if($supplier->Enabled)
          { 
            switch($supplier->InputCartridgeType->ID)
            {
              case 1:
                $a_TempResponse['supplier_id']        = $sugerido['supplier_id'] ;
          $a_TempResponse['quantity_suggested'] = $sugerido['quantity_suggested'];
                $a_TempResponse['quantity']           = $quantityCyan;
                $a_TempResponse['toner_type']         = $sugerido['toner_type'];
            $a_TempResponse['Consumption']        = $sugerido['Consumption'];
            $a_response['supplier'][]             = $a_TempResponse;
                break;
              case 2:
                $a_TempResponse['supplier_id']        = $sugerido['supplier_id'] ;
          $a_TempResponse['quantity_suggested'] = $sugerido['quantity_suggested'];
                $a_TempResponse['quantity']           = $quantityYellow;
                $a_TempResponse['toner_type']         = $sugerido['toner_type'];
            $a_TempResponse['Consumption']        = $sugerido['Consumption'];
            $a_response['supplier'][]             = $a_TempResponse;
                break;
              case 3:
                $a_TempResponse['supplier_id']        = $sugerido['supplier_id'] ;
          $a_TempResponse['quantity_suggested'] = $sugerido['quantity_suggested'];
                $a_TempResponse['quantity']           = $quantityMagenta;
                $a_TempResponse['toner_type']         = $sugerido['toner_type'];
            $a_TempResponse['Consumption']        = $sugerido['Consumption'];
            $a_response['supplier'][]             = $a_TempResponse;
                break;
              case 4:
                $a_TempResponse['supplier_id']        = $sugerido['supplier_id'] ;
          $a_TempResponse['quantity_suggested'] = $sugerido['quantity_suggested'];
                $a_TempResponse['quantity']           = $quantityBlack;
                $a_TempResponse['toner_type']         = $sugerido['toner_type'];
            $a_TempResponse['Consumption']        = $sugerido['Consumption'];
            $a_response['supplier'][]             = $a_TempResponse;
                break;
              case 5:
                $a_TempResponse['supplier_id']        = $sugerido['supplier_id'] ;
           $a_TempResponse['quantity_suggested'] = $sugerido['quantity_suggested'];
                $a_TempResponse['quantity']           = $quantityBlack;
                $a_TempResponse['toner_type']         = $sugerido['toner_type'];
            $a_TempResponse['Consumption']        = $sugerido['Consumption'];
            $a_response['supplier'][]             = $a_TempResponse;
                break;
              default:
              $a           = 0;
            }
          }
    
          echo '<br>' .json_encode($a_TempResponse) .'<br>';
        }
    $this->sendResponse(json_encode($a_response));

    

//$this->load->library('Blowfish', false);
$incident = RNCPHP\Incident::fetch(1070528);
$incident->Threads                   = new RNCPHP\ThreadArray();
$incident->Threads[0]                = new RNCPHP\Thread();
$incident->Threads[0]->EntryType     = new RNCPHP\NamedIDOptList();
$incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
$incident->Threads[0]->Text          = "PRUEBA";
$incident->Save(RNCPHP\RNObject::SuppressAll);

$oportunidad = RNCPHP\Opportunity::find("CustomFields.OP.IncidentService.ID = 1045104");
foreach ($oportunidad as $key => $op)
{
  if(!$op->CustomFields->c->id_venus)
  {
    echo "1-" . $op->ID .'[' .$op->CustomFields->c->id_venus . "]<br>";
  }
 
}

$this->sendResponse(json_encode($oportunidad));
$this->sendResponse('TEST');

if($oportunidad)
{
$oportunidad[0]->CustomFields->c->id_venus=$incident->ReferenceNumber;
self::insertPrivateNote($incident, "Se Actualiza  PPTO . Ahora puede Facturarse ");
$oportunidad[0]->save();
}

$this->sendResponse(jsin_encode($oportunidad));

    $array_post     = array('data' => array('usuario' => 'appmind',
                                'accion' => 'info_hh',
                                'datos'=> array('id_hh'=> '168665')
                                ));
        $json_data_post = json_encode($array_post);
        $json_data_post = base64_encode($json_data_post);
        $json_data_post = $this->requestPost('http://190.14.56.27/dts/rn_integracion/rntelejson.php', $json_data_post);
     echo "Hola ["  . $json_data_post ."]";
     echo 'User IP Address - '.$_SERVER['REMOTE_ADDR']; 
     echo 'User IP Address1 - '.$_SERVER['HTTP_CLIENT_IP'];
     echo  getHostByName(getHostName());
    $this->sendResponse($json_data_post);
    exit;
    //$incidents = RNCPHP\Incident::find("Disposition.ID = 24 and StatusWithType.StatusType.ID != 2 and StatusWithType.StatusType.ID !=104  and StatusWithType.StatusType.ID != 148 and StatusWithType.StatusType.ID !=149 and StatusWithType.StatusType.ID !=196 and Asset.ID = {$assetId}");
    //$incidents = RNCPHP\Incident::find("Disposition.ID = 24 and StatusWithType.StatusType.ID != 2  and Asset.ID = {$assetId}");
    $incidents = RNCPHP\Incident::find("Disposition.ID = 24 and StatusWithType.Status.ID not in(104,148,149,2,196)  and Asset.ID = {$assetId}");
    foreach ($incidents as $key => $incident)
    {

      echo $incident->ReferenceNumber .'-'.$incident->Disposition->LookupName .'-' .  json_encode($incident->StatusWithType->Status->LookupName).  '-' .$incident->StatusWithType->Status->ID.'<br>';
    }
    
    $this->sendResponse(json_encode($incidents));
    //$a_products = RNCPHP\OP\Product::find("PartNumber like '%{$q_partCode}%' and CodeItem like '%49700%' and CategoryItem like '%{$q_type}%' and (Atribute25 is null or Atribute25 =2 or Atribute25 =0) limit 30");
    //$Incident = RNCPHP\Incident::fetch( '220502-000375');
   
    //$this->sendResponse(json_encode($a_products));
    $trans = RNCPHP\TRA\Transaction2::fetch(938);
    $this->sendResponse(json_encode($trans->TransactionDate));

    
  }


  public function test()
  {


  

   // Looop Start

         $report_id = 102309 ;
         $filter_value= '69070600-8';
         $filter_hh=4164036;
         $nro_referencias="";

         $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
         $status_filter->Name = 'rut';
         $status_filter->Values = array( $filter_value  );
         $filters = new RNCPHP\AnalyticsReportSearchFilterArray;
         $filters[] = $status_filter;
         $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
         $status_filter->Name = 'hh';
         $status_filter->Values = array( $filter_hh );
         $filters = new RNCPHP\AnalyticsReportSearchFilterArray;
         $filters[] = $status_filter;
         $ar= RNCPHP\AnalyticsReport::fetch( $report_id);
         $arr= $ar->run();
         $id_Contacto=112972;
         // Inicio - ALTERNATIVA ENCRIPTADA
         //$this->sendResponse(json_encode($arr->count()));
        
         for ( $i = $arr->count(); $i--; )
         {
            $row = $arr->next();
            $id_Contacto=$row['id'];
           // echo "--->" . $row['id'] . '<br>';
           break;
/*$oportunidad->CustomFields->c->id_venus=1;
$oportunidad->save(RNCPHP\RNObject::SuppressAll);
//$this->sendResponse(json_encode($row['op_id']));*/
//$this->sendResponse(json_encode($oportunidad->CustomFields->OP->IncidentService->ReferenceNumber));
//$this->sendResponse(json_encode($oportunidad->CustomFields->c->id_venus));
//$oportunidad->CustomFields->c->id_venus=$oportunidad->CustomFields->OP->IncidentService->ReferenceNumber;
//$oportunidad->save(RNCPHP\RNObject::SuppressAll);
//$this->sendResponse(json_encode($oportunidad->CustomFields->c->id_venus . '-' . $oportunidad->CustomFields->OP->IncidentService->ReferenceNumber));
         }

         $this->sendResponse(json_encode($id_Contacto)); 
       


    $objAccount = RNCPHP\Account::first("Account.Login = 'malcota'");


  /*  
    $objAccount = RNCPHP\Account::first("Account.Login = 'malcota'");
    $contact                  = RNCPHP\Contact::fetch(123111);
    //$this->sendResponse(json_encode( $contact ));
    $contact->Login           = "malcota";
    $objAccount->CustomFields->OP->Contact=$contact;
    $objAccount->save();
    $contact->save();
    $contact                  = RNCPHP\Contact::fetch(90830);
    $this->sendResponse(json_encode( $objAccount->CustomFields->OP->Contact));
    $this->sendResponse(json_encode( $incident->AssignedTo->StaffGroup->LookupName));
*/
    $this->load->model('custom/GeneralServices');

    //$Contracts = $this->GeneralServices->GetContracts('81137900-K');
    $Contracts = $this->GeneralServices->getOrganizationStatus(94586);
    

    $this->sendResponse(json_encode( $Contracts));

    $incident = RNCPHP\Incident::fetch('220111-000531');
    $this->sendResponse(json_encode( $incident->AssignedTo->StaffGroup->LookupName));
  
   


    $CI =& get_instance();
    $CI->load->model('custom/ConnectUrl');
    $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); 
    $json_data='{
      "hh": ' . 1843633 .',
      "comments": "RIGHTNOW-210301-000600-Soporte Técnico-Creación",
      "Purchase_order": "",
      "user_id": "",
      "counters": {
          "counter_bn": ' . 70000 .',
          "counter_color": ' . 0 .',
          "counter_a3_bn": ' . 0 .',
          "counter_a3_color": '. 0 .',
          "counter_b4_bn": ' . 0 .',
          "counter_b4_color": ' . 0 .',
          "counter_dupl": ' . 0 .',
          "counter_metro": ' . 0 .'
      }
  }';
  
 
  

 $url=$cfg2->Value ."/mb/insertCounterRN";
 

  $response=$CI->ConnectUrl->requestCURLJsonRaw($url, $json_data); 
  $this->sendResponse(json_encode($response));
  
  



    $lastSuppliersIncident      = RNCPHP\Incident::first("CustomFields.c.id_hh = '588103' and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont1_hh != 0 order by ClosedTime DESC");
    $this->sendResponse(json_encode($lastSuppliersIncident->LookupName)) ;

    $a_suppliers       = RNCPHP\OP\SuppliersRelated::find("Product.ID = 31252 and (EnabledSupplierRequest = 1 or EnabledSupplierRequest is null)");
    $this->sendResponse(json_encode($a_suppliers[0]->Product->Name) . '-' . json_encode($a_suppliers[0]->Supplier->Name));

    $opportunity      = RNCPHP\Opportunity::fetch(17393);
    $this->sendResponse(json_encode($opportunity->CustomFields->OP->Direccion));
  /*
  $Conditions = (object) array('Notes'=>'');
*/
    //$Organization  = RNCPHP\Organization::find("CustomFields.c.rut='" . '77094440-6'. "'");
    

    //$direcciones=RNCPHP\DOS\Direccion::find('organization.id = '. $Organization[0]->ID);
    
//    $lastSuppliersIncident      = RNCPHP\Incident::first("CustomFields.c.id_hh = {$idHH} and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont1_hh != 0 order by ClosedTime DESC");
    $lastSuppliersColorIncident = RNCPHP\Incident::first("CustomFields.c.id_hh = 2288562 and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont2_hh != 0 order by ClosedTime DESC");
    $this->sendResponse(json_encode($lastSuppliersColorIncident->ReferenceNumber));

    $incidents = RNCPHP\Incident::find(" CustomFields.DOS.Direccion.Organization.CustomFields.c.rut='" . '77094440-6'. "' and StatusWithType.status.ID not in(2,149)"); //Original .ID in(140)"

    foreach ($incidents as $key => $incident)
         {
           echo $incident->ReferenceNumber .'-'.$incident->Disposition->LookupName .'-' .  json_encode($incident->StatusWithType->Status->LookupName).'<br>';
         }
    return;       
    $this->sendResponse(json_encode($incidents));


    $this->sendResponse(json_encode($incidents));
    $obj_hh = RNCPHP\Asset::first("SerialNumber = '" . $incident->CustomFields->c->id_hh . "'");
    $a_items = RNCPHP\OP\SuppliersRelated::find("Product.ID = {$incident->Asset->CustomFields->DOS->Product->ID} and (EnabledSupplierRequest = 1 or EnabledSupplierRequest is null)");

    //$contadores = RNCPHP\DOS\Contador::find('Asset.ID ='. $incident->Asset->ID);
    $this->sendResponse(json_encode($a_items));
   // $token=ConnectUrl::geToken();


   
   

  

    $data           = array("grant_type" => "client_credentials");
    $consumerKey    = "Lew2akNsSYkM9j92eQvU50_BfFEa"; // Prod 
    $consumerSecret = "uP1Q_Coeio8w_nytC_MuTBfENhga"; // Prod
  
    $tokenA = $CI->ConnectUrl->requestCURLByPost($url, $data, $consumerKey . ":" . $consumerSecret);
    $a_jsonToken = json_decode($tokenA, TRUE);
    $token = $a_jsonToken["access_token"];


    //$this->sendResponse($token);

            $org_rut = '61601000-K';
            $a_request = array(
                "RUT" => $org_rut
            );
            $json_request=json_encode($json_request);
            $response=$CI->ConnectUrl->requestCURLJsonRaw('https://api.dimacofi.cl/apiCloudMD/getRutStatusSAI', '{"RUT":"61601000-K"}', $token); 
            $this->sendResponse($response);
            // Obtiene valor de HH
            // $id_hh      = $incident->CustomFields->c->id_hh;
    /*$EnviromentConditions = $this->EnviromentConditions->getObjectEnviromentConditions($Incident->ID);


    if(!($EnviromentConditions))
    {

        $Conditions->Incident=$Incident->ID;
         $EnviromentConditions=$this->EnviromentConditions->createEnviromentConditions($Conditions,"Hola");
    }    
    $EnviromentConditions->IpNumber='1';
    $EnviromentConditions->save();
    $this->sendResponse("--->".json_encode($EnviromentConditions->ID));
    /*require_once(get_cfg_var("doc_root") . "/ConnectPHP/Connect_init.php");
    //error_reporting(E_ALL);
*/

    initConnectAPI("rtorrens","Rtorrens123");
      $incident = RNCPHP\Incident::fetch('201026-000091');
      $FILE=$incident->FileAttachments[1];
      $this->sendResponse(json_encode($FILE->getAdminURL()));
  


  $array_post     = array('usuario' => 'appmind',
  'accion' => 'info_hh',
  'datos'=> array('id_hh'=> '3048555')
  );
$json_data_post = json_encode($array_post);
//$json_data_post = $this->Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
$json_data_post = $this->blowfish->encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
$json_data_post = base64_encode($json_data_post);
$postArray = array ('data' => $json_data_post);

$result = $this::requestPost($this->URL_GET_HH, $postArray);

$this->sendResponse("--->".$result);

  //producción
  $url = "https://api.dimacofi.cl/sucursalVirtual/SearchRutInvoice";
  $CI =& get_instance();
  $CI->load->model('custom/ConnectUrl');
  $a_request = array(
      "rut" => "60506000-5",
      "invoice_number"=>  "" ,
      "invoice_rut"=> "",
      "invoice_contrato"=> "" ,
      "invoice_from"=> "" ,
      "invoice_to"=> "" 
  );

  $jsonDataEncoded = json_encode($a_request);
  
  $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded);
  $this->sendResponse("--->".$service);


  $IncidentR  = RNCPHP\Incident::find(" CustomFields.OP.Incident.ID = " . '743481'  ." and StatusWithType.status.ID in(140,111)"); //Original .ID in(140)"
  $this->sendResponse(json_encode($IncidentR[0]->StatusWithType->Status->ID));


  $Incident = RNCPHP\Incident::find( '200706-000034');
  $this->sendResponse(json_encode($Incident));
  $data = '{"items":[{"TK":"200728-000450"},
  {"TK":"200728-000451"},
  {"TK":"200729-000356"},
  {"TK":"200729-000357"},
  {"TK":"200729-000358"},
  {"TK":"200728-000450"},
  {"TK":"200728-000451"},
  {"TK":"200724-000169"},
  {"TK":"200717-000313"},
  {"TK":"200717-000314"},
  {"TK":"200717-000315"},
  {"TK":"200715-000286"},
  {"TK":"200715-000288"},
  {"TK":"200715-000289"},
  {"TK":"200715-000290"}
       
    ]}';
$array_data=json_decode($data,true);
foreach ($array_data['items']  as $key => $value)
         {
           
             $Incident = RNCPHP\Incident::fetch( $value['TK']);
        
            echo $value['TK'] .'-' .json_encode($Incident->CustomFields->c->supply_reason->LookupName) .'-' .json_encode($Incident->StatusWithType->Status->LookupName) . '<br>';
        
         }    
  exit; 
  $CI =& get_instance();
  $CI->load->model('custom/ConnectUrl');


  $url = "https://api.dimacofi.cl/token";

  $data           = array("grant_type" => "client_credentials");
  $consumerKey    = "Lew2akNsSYkM9j92eQvU50_BfFEa"; // Prod 
  $consumerSecret = "uP1Q_Coeio8w_nytC_MuTBfENhga"; // Prod

  $tokenA = $CI->ConnectUrl->requestCURLByPost($url, $data, $consumerKey . ":" . $consumerSecret);
  $a_jsonToken = json_decode($tokenA, TRUE);
  $token = $a_jsonToken["access_token"];
  $jsonData = new \stdClass();
   $a_request = array(
                "RUT" => '61102022-8',
                "HH" => '2110589'
            );
 

  $jsonDataEncoded = json_encode($a_request);
  $service=$CI->ConnectUrl->requestCURLJsonRaw("https://api.dimacofi.cl/apiCloudMD/getHoldingListHH", $jsonDataEncoded, $token);
  //$service = $CI->ConnectUrl->requestCURLJsonRaw('https://api.dimacofi.cl/sucursalVirtual/consulta/consumos/resumen', $jsonDataEncoded);
  $this->sendResponse($service);
  
  }


public function test2()
{
/*
  $IncidentR  = RNCPHP\Incident::find(" CustomFields.OP.Incident.ID = " . '1131144  and StatusWithType.status.ID not in(2,149,146)' ); 

  echo json_encode($IncidentR);
  echo "<br>";
  foreach($IncidentR as $i)
  {
      self::insertPrivateNote($i, "Cerrando Ticket de Reparación " . $i->ReferenceNumber);
      $i->StatusWithType->Status->ID=2;
      $i->Save(RNCPHP\RNObject::SuppressAll);
      echo $i->StatusWithType->Status->ID;
      echo "<br>";
  }
  
  return;


  echo "TEST2";
  $CI =& get_instance();
  $CI->load->model('custom/ConnectUrl');
  $data           = array("grant_type" => "client_credentials");
  $consumerKey    = "Lew2akNsSYkM9j92eQvU50_BfFEa"; // Prod 
  $consumerSecret = "uP1Q_Coeio8w_nytC_MuTBfENhga"; // Prod
  $url = "https://api.dimacofi.cl/token";

  $tokenA = $CI->ConnectUrl->requestCURLByPost($url, $data, $consumerKey . ":" . $consumerSecret);
  $a_jsonToken = json_decode($tokenA, TRUE);
  $token = $a_jsonToken["access_token"];
  echo "[" . $token . "]";

  //$this->sendResponse($token);

  $json_text = '{
    "hh": "1046336",
    "motivo": "Termino Prestamo",
    "instrucciones": "Instrucciones de Prueba",
    "comentarios": "Es una prueba",
    "contacto": "Juan Perez",
    "email": "email@email.com",
    "telefono": "123",
    "aprobacion": true
}';
  $a_request = array(
      "RUT" => $org_rut
  );
  $json_request=json_encode($json_request);
  echo $token;
  $response=$CI->ConnectUrl->requestCURLJsonRaw('https://api.dimacofi.cl/sucursalVirtual/crearRetiroHH', $json_text, $token); 
  $this->sendResponse($response);




 
$json_data_post = ' {"usuario":"Integer","accion":"setInvoiceAR","order_detail":{"ref_id_ppto_rn":"21100-1\/1","client_rut":"71499900-1","deliver_to_customer_id":53074,"Bill_to":26112,"hh":820350,"Batch_source_id":null,"Cust_trx_type_id":null,"Terms_id":1226,"Customer_id":null,"salesreps_id":100077142,"Purchase_order_ref":"0","list_products":[{"Inventory_item_id":17429,"line_id":1053418,"ordered_quantity":1,"line_selling_price":"2841","unit_selling_price":2841},{"Inventory_item_id":7941,"line_id":1053661,"ordered_quantity":1,"line_selling_price":"35000","unit_selling_price":35000}]}}';

//self::insertPrivateNote($opportunity, "json enviado: ". $json_data_post);


$json_data_post = base64_encode($json_data_post);
$postArray      = array ('data' => $json_data_post);
echo $this->URL_GET_HH;
$result         = $this->requestPost($this->URL_GET_HH, $postArray);
echo $result;
echo "--" . self::$msgError . "--"; 
$this->sendResponse($result);

}

public function getHH()
    {
*/
$this->load->library('ConnectUrl');
$opportunity = RNCPHP\Opportunity::fetch(22793);
        $a_orderItems          = RNCPHP\OP\OrderItems::find("Opportunity.ID ='22793'");
        
        foreach ($a_orderItems as $item)
        {
          if ($item->Enabled === false )
            continue;
          $a_tmp_result['Inventory_item_id'] = $item->Product->InventoryItemId;
          $a_list_items[] = $a_tmp_result;
        }

        $array_post     = array("usuario" => "Integer",
                                "accion"  => "updateItemsPrice",
                                "order_detail" => array(
                                  "list_products"         => $a_list_items
                                ));

        $json_data_post = json_encode($array_post);

        //self::insertPrivateNote($opportunity, "json enviado: ". $json_data_post);


        $json_data_post = base64_encode($json_data_post);
        $postArray      = array ('data' => $json_data_post);
        $result         = $this->requestPost($this->URL_GET_HH, $postArray);

        if ($result != false)
        {
          $arr_json  = json_decode($result, true);
          
          //$respuesta = base64_decode($arr_json['respuesta']);
          //$json      = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
          //self::insertPrivateNote($opportunity, "json Repuesta: ". $json);

          //No fallo el JSON Decode
          if ($arr_json != false)
          {
            if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json)) ))
            {
              $respuesta  = base64_decode($arr_json['respuesta']);

              switch ($arr_json['resultado'])
              {
                case true:

                    //$json_resp       = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                    $array_data_resp = json_decode(utf8_encode($respuesta), true); //Transformar a array

                    if (!is_array($array_data_resp))
                    {
                      $message = "ERROR, problema al decofificar Respuesta ";
                      break;
                    }


                    //Codigo para exponer los arreglos
                    $a_products = $array_data_resp['order_detail']['list_products'];
                    $var = print_r($a_products, true);

                    $this->insertPrivateNote($opportunity, "OBJ LINE: ". $var);

           
                    //Codigo de actualización de precios

                    $exist_lines = false;
                    foreach ($a_products as $key => $product) {

                      $exist_lines = true;
                      $itemID                        = $product['Inventory_item_id'];

                      //Actualizando precio producto

                      if($product['unit_selling_price']>=0)
                      {
                      $obj_product                   = RNCPHP\OP\Product::first("InventoryItemId =  '{$itemID}' ");
                      $obj_product->UnitSellingPrice = $product['unit_selling_price'];
                      $obj_product->Save();

                      //Actualizando precio linea
                      $idOP                          = $opportunity->ID;
                      $obj_line                      = RNCPHP\OP\OrderItems::first("Product.ID =  '{$obj_product->ID}' and Opportunity.ID = {$idOP} ");
                      $obj_line->UnitTempSellPrice   = $product['unit_selling_price'];
                      echo $idOP .'-'. json_encode($obj_line->ID).'-' . $obj_product->ID .'<br>';

                      //$obj_line->Save();
                      }
                    }

                    if ($exist_lines === false)
                      $message =  "Los precios de los productos y las lineas han sido actualizados con exito " . $json_resp;
                    else
                      $message =  "No se encontraron lineas para actualizar";

                    break;
                case false:
                    $message = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                    break;
                default:
                    $message = "ERROR: Estructura JSON No valida R".PHP_EOL;
                    $message .= "JSON: ". $result;
                    break;
              }
              self::insertPrivateNote($opportunity, $message);
            }
            else
            {
              $message = "ERROR: Estructura JSON No valida, no se encontro 'resultado' ni 'respuesta' ". PHP_EOL;
              //$message .= "JSON: ". $result;
              self::insertPrivateNote($opportunity, $message);
            }
          }
          else
          {
            $message = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
            self::insertPrivateNote($opportunity, $message);
          }
        }
        else
        {
            $message = "ERROR: ".ConnectUrl::getResponseError();
            self::insertPrivateNote($opportunity, $message);
        }

        //self::insertPrivateNote($opportunity, "json devuelto: ". $result);
        $arr_json  = json_decode($result, true);
        $array_data_resp = json_decode(utf8_encode($json_resp), true);
        $respuesta  = base64_decode($arr_json['respuesta']);

        $array_post     = array('data' => array('usuario' => 'appmind',
                                'accion' => 'info_hh',
                                'datos'=> array('id_hh'=> '1348551')
                                ));
        $json_data_post = json_encode($array_post);
        $json_data_post = base64_encode($json_data_post);
        $json_data_post = $this->connecturl->requestPost($this->URL_GET_HH, $json_data_post);

        if ($result != false)
            $this->sendResponse($result);
        else
             $this->sendResponse($this->connecturl->getResponseError());



    }

    private function ValidaArreglo($indiceAccion,$array_data)
    {

      if (!array_key_exists($indiceAccion, $array_data) and !array_key_exists($indiceUsuario, $array_data) and !array_key_exists( $indiceDatos, $array_data))
         {
           $response = $this->responseError(3);
           $this->sendResponse($response);
         }

         if ($array_data['usuario'] != self::USER)
         {
           $response = $this->responseError(5);
           $this->sendResponse($response);
         }

         if ($array_data['accion'] != self::__FUNCTION__CIS__ )
         {
           $response = $this->responseError(6);
           $this->sendResponse($response );
         }

         if (!is_array($array_data['datos']) )
         {
           $response = $this->responseError(3);
           $this->sendResponse($response );
         }


    }

static function requestCURLByPost($url, $postArray)
	{
		# Form data string
		if (is_array($postArray))
			$postString = http_build_query($postArray, '', '&');


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
			self::$msgError = curl_error($ch);
      $response = self::$msgError;
			//self::$msgError .='<br>Tiempo ' . $info['total_time'] . ' segundos en recibir la respuesta de la siguiente URL: ' . $info['url'];
			curl_close($ch);

			return false;
		}

		if ($response != false)
		{
			$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if ($statusCode != '200')
			{
				self::$msgError = 'No se pudo resolver la petici?n a la URL 1, codigo de Error: '. $statusCode;
				return false;
			}
			else
				return $response;
		}
		else
		{
			curl_close($ch);
			self::$msgError = 'No se pudo resolver la petici?n a la URL 2';
			return false;
		}
	}
  static function requestFileGetContentByPost($url, $postArray)
    {

        $headers = @get_headers($url);
        $statusCode = substr($headers[0], 9, 3);
        if($statusCode != '200'){
            self::$msgError = 'No se pudo resolver la petici?n a la URL 3, codigo de Error: '. $statusCode;
            return false;
        }

        if (is_array($postArray))
            $postString =  http_build_query($postArray);

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' =>  $postString
            )
        );

        $context  = stream_context_create($opts);
        $result   = file_get_contents($url, false, $context);



        return $result;
    }

    static function requestPost($url, $postArray, $typeRequest ='CURL')
	    {
	        switch ($typeRequest) {
	            case 'CURL':
	                return self::requestCURLByPost($url, $postArray);
	                break;
	            case 'FileGetContent':
	                return self::requestFileGetContentByPost($url, $postArray);
	                break;
	            default:
	                return self::requestCURLByPost($url, $postArray);
	                break;
	        }
	    }

    public function AsociarIncidente()
    {
      $TEXTO='';
      $data_post='{
          "datos":
          [
            {"ID": "23852" }

          ]
        }';

    // 84860

        $array_data=json_decode($data_post,true);
      foreach ($array_data["datos"]  as $key => $value)
      {


          $incident = RNCPHP\Incident::fetch($value['ID'] );
          $incident->AssignedTo->Account= RNCPHP\Account::fetch(84860);
          $incident->Save(RNCPHP\RNObject::SuppressAll);
          $TEXTO=$TEXTO . '-' . $incident->ReferenceNumber;
      }

      $this->sendResponse($TEXTO);
    }

    public function CrearIncidenteServicio()
    {
      $bannerNumber = 0;
      $indiceDatos = 'datos';
      $indiceAccion = 'accion';
      $indiceUsuario = 'usuario';
      $indiceHH='HH';
      $indiceEstado = 'estado';
      $indiceTipoSolicitud = 'tiposolicitud';
      $indiceSubject = 'asunto';
      $indiceOrden_Activacion = 'orden_activacion';
      $indiceLinea_Orden_Activacion = 'linea_orden_activacion';
      $indiceAssignedTo = "AssignedTo";
      $indiceshipping_instructions ="instrucciones";

      $indiceArreglo=0;
      /*
      $data_post  = $this->getdataPOST();
      $array_data = json_decode(utf8_encode($data_post), true);
    */
      $respuesta= array();
      $listaTicket= array();
      $incidents=array();


      
      if (empty($_POST))
      {
          //$response = $this->responseError(1);
          //$this->sendResponse($response);

        $data_post='{"usuario": "UserDimacofi","accion": "CrearIncidenteServicio","datos":[{"HH": "4496098","estado": 1,"tiposolicitud": 28,"asunto": "Instalacion Maquina Nueva HH 4496098","orden_activacion": "81029","linea_orden_activacion": "OE_ORDER_LINES_ALL23294639|4496098|19667552","AssignedTo": "209","instrucciones": "Importadora Hevia SPA-ventas@kaiken.info-56959387814-Porfavor en la factura agregar el siguiente ID 1233607-32-LE23","Category_Type": "","contract_number": "81029","solution_type": "","sub_type": ""}]}';
        //$data_post='{"usuario": "UserDimacofi","accion": "CrearIncidenteServicio","datos":[{"HH": "1552529","estado": 1,"tiposolicitud": 28,"asunto": "Instalacion Maquina Nueva HH 1552529","orden_activacion": "951","linea_orden_activacion": "951-3769","AssignedTo": "209","instrucciones": "Camila Erdlandsen-227291006"}]}';
        //$data_post='{"usuario": "UserDimacofi","accion": "CrearIncidenteServicio","datos":[{"HH": "2993540","estado": 1,"tiposolicitud": 28,"asunto": "Instalacion Maquina Nueva HH 2993540","orden_activacion": "OE_ORDER_HEADERS_ALL20891915","linea_orden_activacion": "OE_ORDER_LINES_ALL20265583|2993540|16212464","AssignedTo": "209","instrucciones": ""}]}';

        //'{"usuario": "UserDimacofi","accion": "CrearIncidenteServicio","datos":[
          //{"HH": "638030","estado": 1,"tiposolicitud": 28,"asunto": "Instalacion Maquina Nueva HH 638030","orden_activacion": "4018","linea_orden_activacion": "6.1","AssignedTo": "209","instrucciones": "Luis Molina-lmolinab@copec.cl-65878952-Entrega a: Luis Molina Fono:"}
    //  ] }';

        $array_data=json_decode($data_post,true);
      }
      else {

        $data_post  = $this->getdataPOST();
        $array_data=json_decode($data_post,true);
      }

      if (empty($data_post))
      {
          $response = $this->responseError(1);
          $this->sendResponse("Error ->" . $data_post);
      }

      if (is_array($array_data) and ($array_data != false))
      {
             $this->ValidaArreglo($indiceAccion,$array_data);


      try
      {

      
          foreach ($array_data[$indiceDatos]  as $key => $value)
            {

              $orden_activacion[$indiceArreglo] =  $value[$indiceOrden_Activacion];
              $linea_orden_activacion[$indiceArreglo] =  $value[$indiceLinea_Orden_Activacion];
              $hh[$indiceArreglo] =  $value[$indiceHH];
              

              if(!array_key_exists($indiceDatos, $array_data))
              {
                $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
                $this->sendResponse($this->error);
                return;
              }

              if(!empty($value['instrucciones']))
              {
                $record = explode("-",$value['instrucciones'] );
                
                
                //$res = RNCPHP\ROQL::query("SELECT ID FROM Contact where  login='" . $value['contacto']['email'] . "'  ORDER BY ID DESC LIMIT 1 "  )->next();
                  $res = RNCPHP\ROQL::query("SELECT ID FROM Contact where  login='" . $record[1] . "'  ORDER BY ID DESC LIMIT 1 "  )->next();

                  
                  $contactObj = $res->next();
                 
                  if($contactObj['ID'])
                  {
                   
                     $contact =  RNCPHP\Contact::fetch($contactObj['ID']);


                       $person = explode(" ",$record[0] );


                       if(strlen($person[0])>80)
                       {
                         $first=substr($person[0], 0, 80);
                       }
                       else {
                         $first=$person[0];
                       }
                       $contact->Name->First =$first;

                       if(strlen($person[1])>80)
                       {
                         $last=substr($person[1], 0, 80);
                       }
                       else {
                         $last=$person[1];
                       }
                       $contact->Name->Last = $last;
                       $contact->save();

               }
                    else {

                      $n=strpos ($record[0], '@');
                      $a=strpos ($record[1], '@');
                      $p=strpos ($record[1], '.',$a);
                      $e=strpos ($record[1], ' ');
                      $cc=strpos ($record[1], '@.');

                     
                        if($a>0 && $p>0 && $n=='' && $e=='' && $cc=='')
                        {
                          $contact = new RNCPHP\Contact();
                          $contact->Login = $record[1];
                          $person = explode(" ",$record[0] );
                          $contact->Name = new RNCPHP\PersonName();

                          if(strlen($person[0])>80)
                          {
                            $first=substr($person[0], 0, 80);
                          }
                          else {
                            $first=$person[0];
                          }
                          $contact->Name->First =$first;

                          if(strlen($person[1])>80)
                          {
                            $last=substr($person[1], 0, 80);
                          }
                          else {
                            $last=$person[1];
                          }
                          $contact->Name->Last = $last;

                                //add email addresses
                          $contact->Emails = new RNCPHP\EmailArray();
                          $contact->Emails[0] = new RNCPHP\Email();
                          $contact->Emails[0]->AddressType=new RNCPHP\NamedIDOptList();
                          $contact->Emails[0]->AddressType->LookupName = "Correo electrónico - Principal";
                          if(strlen($record[1])>80)
                          {
                            $email=substr($record[1], 0, 80);
                          }
                          else {
                            $email=$record[1];
                          }
                          $contact->Emails[0]->Address = $email;


                          $i = 0;
                          if($record[2])
                          {
                            $contact->Phones = new RNCPHP\PhoneArray();
                            $contact->Phones[$i] = new RNCPHP\Phone();
                            $contact->Phones[$i]->PhoneType = new RNCPHP\NamedIDOptList();
                            $contact->Phones[$i]->PhoneType->LookupName = 'Teléfono de oficina';

                            if(strlen($record[2])>40)
                            {
                              $phone=substr($record[2], 0, 40);
                            }
                            else {
                              $phone=$record[2];
                            }
                            $contact->Phones[$i]->Number =  $phone;
                          }

                        $i++;
                          $contact->save();
                        }
                        else {
                          $contact =  RNCPHP\Contact::fetch(61293);
                        }
                     }

             }
          else {
            $contact =  RNCPHP\Contact::fetch(61293);
          }

          
              //$orden_activacion=$array_data[$indiceDatos][$indiceOrden_Activacion];
              // Busca la existencia de ticket asociado a una Orden de activacion
           
              
              $array_obj = RNCPHP\Incident::find(" CustomFields.c.orden_activacion = '" . $linea_orden_activacion[$indiceArreglo]    .  "' and  CustomFields.c.nota_pedido =  "    .  $orden_activacion[$indiceArreglo]  .  " and CustomFields.c.id_hh ='"  .  $hh[$indiceArreglo] . "'"   );

              
              if($array_obj[0]->ReferenceNumber<>'')
              {
                // si existe crea un elemento con los datps devueltos
                $listaTicket[$indiceArreglo]['ref_num']=$array_obj[0]->ReferenceNumber;
                $listaTicket[$indiceArreglo]['orden_activacion']=$orden_activacion[$indiceArreglo];
                $listaTicket[$indiceArreglo]['linea_orden_activacion']=$linea_orden_activacion[$indiceArreglo] ;
                $listaTicket[$indiceArreglo]['HH']=$hh[$indiceArreglo];
                $listaTicket[$indiceArreglo]['message']='ok';
                $listaTicket[$indiceArreglo]['estado']='true';

                //$asset = RNCPHP\Asset::find( "asset_id = ". $array_obj[0]->asset_id );
                $res='';
/*
                $contadores = RNCPHP\DOS\Contador::find("incident = "  . $array_obj[0]->ID  );
                $indicecontador=0;
                $cont=array();
                foreach ($contadores  as $key => $value2)
                {
                  $cont[$indicecontador]['ID']   =  $value2->ID;
                  $cont[$indicecontador]['Valor']   = $value2->Valor;
                  $cont[$indicecontador]['Tipo']=$value2->TipoContador->LookupName;
                  $indicecontador++;
                }


                $listaTicket[$indiceArreglo]['Contadores'] =$cont;
*/
                $array_obj[0]->PrimaryContact                         = RNCPHP\Contact::fetch($contact->ID);
                $array_obj[0]->Save(RNCPHP\RNObject::SuppressExternalEvents);

              }
              else
              {
                // si el  el ticket no existe se crea
                
                $listaTicket[$indiceArreglo]['ref_num']='';
                $listaTicket[$indiceArreglo]['orden_activacion']='';
                $listaTicket[$indiceArreglo]['message']='ERROR:Desconocido';
                $listaTicket[$indiceArreglo]['estado']='false';
                $listaTicket[$indiceArreglo]['HH']='';



               
                //RNCPHP\ConnectAPI::commit();
                
                
                $incidents[$indiceArreglo]                                         = new RNCPHP\Incident();
                $incidents[$indiceArreglo]->CustomFields->c->json_hh=substr($data_post, 0, 3999);
                $incidents[$indiceArreglo]->Subject                                = $value[$indiceSubject] ;                
                $incidents[$indiceArreglo]->Disposition                            = RNCPHP\ServiceDisposition::fetch($value[$indiceTipoSolicitud]);
                $incidents[$indiceArreglo]->PrimaryContact                         = RNCPHP\Contact::fetch($contact->ID); //RNCPHP\Contact::fetch(48170); //SC/SC/
                $incidents[$indiceArreglo]->AssignedTo->Account                    = RNCPHP\Account::fetch($value[$indiceAssignedTo]);
                $incidents[$indiceArreglo]->StatusWithType->Status->ID             = $value[$indiceEstado];
                $incidents[$indiceArreglo]->CustomFields->c->shipping_instructions = $value[$indiceshipping_instructions];
                $incidents[$indiceArreglo]->CustomFields->c->nota_pedido           = $value[$indiceOrden_Activacion];
                $incidents[$indiceArreglo]->CustomFields->c->id_hh                 = $value[$indiceHH];
                $incidents[$indiceArreglo]->CustomFields->c->orden_activacion      = $value[$indiceLinea_Orden_Activacion];
                $incidents[$indiceArreglo]->CustomFields->c->requiere_taller       = false;
                //$incidents[$indiceArreglo]->CustomFields->c->seguimiento_tecnico->LookupName='Visita Técnico Asignado';
                $incidents[$indiceArreglo]->CustomFields->c->Category_Type=$value['Category_Type'];
                $incidents[$indiceArreglo]->CustomFields->c->contract_number=$value['contract_number'];
                $incidents[$indiceArreglo]->CustomFields->c->solution_type=$value['solution_type'];
                $incidents[$indiceArreglo]->CustomFields->c->sub_type=$value['sub_type'];
                $incidents[$indiceArreglo]->Save(RNCPHP\RNObject::SuppressExternalEvents);


                
                try
                {

                  
                  //obtiene valor de HH
                  $id_hh = $incidents[$indiceArreglo]->CustomFields->c->id_hh; //168665
                  $array_post     = array('usuario' => 'appmind',
                              'accion' => 'info_hh',
                              'datos'=> array('id_hh'=> $id_hh)
                              );
                  $json_data_post = json_encode($array_post);
                  //$json_data_post = $this->Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
                  $json_data_post = $this->blowfish->encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
                  $json_data_post = base64_encode($json_data_post);
                  $postArray = array ('data' => $json_data_post);

                  $result = $this::requestPost($this->URL_GET_HH, $postArray);

                  

                  if ($result != false) {
                  $arr_json = json_decode($result, true);


                  if ($arr_json != false)
                  {
                    if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json)) ))
                    {
                      $respuesta  = base64_decode($arr_json['respuesta']);


                      
                      switch ($arr_json['resultado'])
                      {
                        case "true":

                          $json_hh = $this->blowfish->decrypt($respuesta, "D3t1H6q0p6V7z8", 10, 22, NULL);
              
                          $array_hh_data = json_decode(utf8_encode($json_hh),true);
                          if (!is_array($array_hh_data))
                          {
                            $message = "ERROR: Estructura JSON encriptado No valida ".PHP_EOL;
                            $message .= "JSON: ".$json_hh;
                            $bannerNumber = 3;
                            break;
                          }
                          $array_hh_data = $array_hh_data['respuesta'];
                          $incidents[$indiceArreglo]->CustomFields->c->marca_hh  = $array_hh_data['Marca'];
                          $incidents[$indiceArreglo]->CustomFields->c->modelo_hh = $array_hh_data['Modelo'];
                          $incidents[$indiceArreglo]->CustomFields->c->convenio  = (int)  $array_hh_data['Convenio'];
                          $incidents[$indiceArreglo]->CustomFields->c->tipo_contrato  = $array_hh_data['TipoContrato'];
                          $incidents[$indiceArreglo]->CustomFields->c->sla_hh    = $array_hh_data['SLA'];
                          $incidents[$indiceArreglo]->CustomFields->c->sla_hh_rsn    = $array_hh_data['RSN'];
                          $incidents[$indiceArreglo]->CustomFields->c->serie_maq  = $array_hh_data['Serie'];
                          $incidents[$indiceArreglo]->CustomFields->c->numero_delfos  = $array_hh_data['delfos'];
                          $array_hh_direccion_id =  $array_hh_data['Direccion'];
                          $incidents[$indiceArreglo]->CustomFields->c->cliente_bloqueado =(int) $array_hh_direccion_id['Bloqueado'];
                          $incidents[$indiceArreglo]->CustomFields->c->soporte_telefonico=0;
                          $id_ebs_direccion = $array_hh_direccion_id['ID_direccion'];

                          $array_Direccion_obj = RNCPHP\DOS\Direccion::find('d_id = '. $id_ebs_direccion);
                          if (is_array($array_Direccion_obj) and is_object($array_Direccion_obj[0]))
                          {
                            $incidents[$indiceArreglo]->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
                            $incidents[$indiceArreglo]->StatusWithType->Status->ID   =  $value[$indiceEstado];
                          }
                          
                          $this->AsignaRutIncidente($incidents[$indiceArreglo],$array_hh_data['Rut'],$contact);
                         
                          //$this->sendResponse($array_hh_data['Rut']);
                          //busca Organizacion
                          //$incidentR->Organization
                          //asignar organizacion a Contacto
                          //incidents[$indiceArreglo]->PrimaryContact

                          $array_hh_contadores =  $array_hh_data['Contadores'];
                          $incidents[$indiceArreglo]->Save(RNCPHP\RNObject::SuppressAll);
                          
                          $asset = RNCPHP\Asset::first( "SerialNumber = '".$incidents[$indiceArreglo]->CustomFields->c->id_hh."'");
                          if (empty($asset)) {
                            $asset = new RNCPHP\Asset;
                            //$asset->Name = $incident->CustomFields->c->id_hh."-".$ncident->CustomFields->c->marca_hh."-".$ncident->CustomFields->c->modelo_hh;
                            $nameHH = $incidents[$indiceArreglo]->CustomFields->c->id_hh."-".$incidents[$indiceArreglo]->CustomFields->c->marca_hh."-".$incidents[$indiceArreglo]->CustomFields->c->modelo_hh;
                            $asset->Name = substr($nameHH, 0, 80);

                            $asset->Contact = $incidents[$indiceArreglo]->PrimaryContact;
                            //$asset->Organization = $incident->Organization;
                            $asset->Product = 2;
                            $asset->SerialNumber = $incidents[$indiceArreglo]->CustomFields->c->id_hh;
                            $asset->Save(RNCPHP\RNObject::SuppressAll);
                          }
                          $asset->CustomFields->DOS->Direccion =  $incidents[$indiceArreglo]->CustomFields->DOS->Direccion;
                          $incidents[$indiceArreglo]->Asset = $asset;

                          $incidents[$indiceArreglo]->Save(RNCPHP\RNObject::SuppressAll);
                          //Contadores


                          foreach ($array_hh_contadores as $counter)
                          {
                            $count_id    = $counter['ID'];
                            $count_tipo  = $counter['Tipo'];
                            $count_valor = $counter['Valor'];
                            $contador               = new RNCPHP\DOS\Contador();
                            $contador->ContadorID   = $count_id;
                            $contador->Valor        = $count_valor;
                            $contador->Incident     = $incidents[$indiceArreglo];
                            $contador->TipoContador = RNCPHP\DOS\TipoContador::fetch($counter['Tipo']);
                            $contador->Asset        = $incident->Asset;

                            $contador->Save(RNCPHP\RNObject::SuppressAll);



                          }


                          $listaTicket[$indiceArreglo]['ref_num']= $incidents[$indiceArreglo]->ReferenceNumber;
                          $listaTicket[$indiceArreglo]['orden_activacion']=$value[$indiceOrden_Activacion];
                          $listaTicket[$indiceArreglo]['linea_orden_activacion']=$value[$indiceLinea_Orden_Activacion];
                          $listaTicket[$indiceArreglo]['message']='ok';
                          $listaTicket[$indiceArreglo]['estado']='true';
                          $listaTicket[$indiceArreglo]['HH']=$incidents[$indiceArreglo]->CustomFields->c->id_hh;
                          $listaTicket[$indiceArreglo]['Contadores'] =$array_hh_data['Contadores'];

                          RNCPHP\ConnectAPI::commit();


                          break;
                        case False:
                          $listaTicket[$indiceArreglo]['ref_num']='';
                          $listaTicket[$indiceArreglo]['orden_activacion']=$value[$indiceOrden_Activacion];
                          $listaTicket[$indiceArreglo]['linea_orden_activacion']=$linea_orden_activacion[$indiceArreglo] ;
                          $listaTicket[$indiceArreglo]['message']="ERROR: Servicio responde con fallo ".PHP_EOL;
                          $message ="ERROR: Servicio responde con fallo ".PHP_EOL;
                          $listaTicket[$indiceArreglo]['estado']='false';
                          $bannerNumber = 3;
                          RNCPHP\ConnectAPI::rollback();
                          break;
                        default:
                          $listaTicket[$indiceArreglo]['ref_num']='';
                          $listaTicket[$indiceArreglo]['orden_activacion']=$value[$indiceOrden_Activacion];
                          $listaTicket[$indiceArreglo]['linea_orden_activacion']=$linea_orden_activacion[$indiceArreglo] ;
                          $listaTicket[$indiceArreglo]['message']="ERROR: Respuesta fallida ".PHP_EOL;
                          $listaTicket[$indiceArreglo]['rest']=$arr_json['resultado'];
                          $listaTicket[$indiceArreglo]['resp']=$respuesta;
                          $listaTicket[$indiceArreglo]['resl']=$result;
                          $listaTicket[$indiceArreglo]['estado']='false';
                          $message ="ERROR: Respuesta fallida ".PHP_EOL;
                          $bannerNumber = 3;
                          RNCPHP\ConnectAPI::rollback();
                          break;
                      }
                    }
                    else {
                      $message = "ERROR: Estructura JSON No valida ". PHP_EOL;
                      $bannerNumber = 3;
                      $listaTicket[$indiceArreglo]['message']=$message;
                      $listaTicket[$indiceArreglo]['estado']='false';
                      RNCPHP\ConnectAPI::rollback();
                    }

                  }
                  else
                  {
                    $message = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
                    $bannerNumber = 3;
                    $respuestaApi['resultado']='false';
                    $respuestaApi['respuesta']['glosa']=$message;
                    $listaTicket[$indiceArreglo]['message']=$message;
                    $listaTicket[$indiceArreglo]['estado']='false';
                    RNCPHP\ConnectAPI::rollback();
                  }
                }
                else {
                  $message = "ERROR: ".$this::getResponseError();
                  $bannerNumber = 3;
                  $respuestaApi['resultado']='false';
                  $respuestaApi['respuesta']['glosa']=$message;
                  $listaTicket[$indiceArreglo]['message']=$message;
                  RNCPHP\ConnectAPI::rollback();
                }

                //self::insertPrivateNote($incidents[$indiceArreglo], $message);
                //self::insertBanner($incidents[$indiceArreglo], $bannerNumber);
                //129 - Información Validada
                $incidents[$indiceArreglo]->StatusWithType->Status->ID             = 129;
                $incidents[$indiceArreglo]->Save();


              }
              catch (Exception $e)
              {
                 $message = "Error ".$e->getMessage();
                 $respuestaApi['resultado']='false';
                 $respuestaApi['respuesta']['glosa']=$message;
                 $listaTicket[$indiceArreglo]['message']=$message;
                 //self::insertPrivateNote($incident, $message );
              }

            }
            $indiceArreglo++;

          }
            $respuestaApi['respuesta']['tickets']=$listaTicket;
            $respuestaApi['resultado']='true';
            $respuestaApi['respuesta']['glosa']='ok';
            $this->sendResponse(json_encode($respuestaApi));



      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        $this->error = "Codigo Z : ".$err->getCode()." ".$err->getMessage();
        $respuestaApi['resultado']='false';
        $respuestaApi['respuesta']['glosa']=$this->error;
        RNCPHP\ConnectAPI::rollback();
        $this->sendResponse(json_encode($respuestaApi));
        return;
      }
    } else {
    //RNCPHP\ConnectAPI::rollbak();
    $respuestaApi['resultado']='false';
    $respuestaApi['respuesta']['glosa']=$this->error;
    $this->sendResponse($this->responseError(2));
    }
    }


    private function AsignaRutIncidente($incident,$rut,$contact)
    {
      $Organization  = RNCPHP\Organization::find("CustomFields.c.rut='$rut'");
      
   
      $incident->Organization = RNCPHP\Organization::fetch($Organization[0]->ID);
      //echo $rut . '<br>';
      if(!$contact->Organization)
      {
        //echo $rut . '<br>';
        $contact->Organization = RNCPHP\Organization::fetch($Organization[0]->ID);
        $contact->save(RNCPHP\RNObject::SuppressAll);
      }
      $incident->save(RNCPHP\RNObject::SuppressAll);
      
     // $this->sendResponse(json_encode($incident->Organization));
      
    }

    public function CrearOportunidad()
    {



        $data_post='{"tickets":[{"ACCOUNT_NUMBER":"81380500-6","RN_DIR":"21316","PARTY_SITE_NUMBER":"21376","SITE_ORIG_SYSTEM_REFERENCE":"21316","ID_TCKRN":"160901-000284","HH_EQUIPO":"46631","TERMINOS":"1001","IDVENDEDOR":"100001107","TOTALNETO":"45201","VALORTOTAL":"53789","IDPRESUPUESTO":"9201","NOMBRE_CONTACTO":" RENATO BARRA","FONO_CONTACTO":"null"}]}';

        $array_data=json_decode($data_post,true);

        $listaTicket= array();
        $indiceArreglo=0;
        $texto='';
        foreach ($array_data['tickets']  as $key => $value)
          {





              //$this->sendResponse($value['ACCOUNT_NUMBER']);
            $incident = RNCPHP\Incident::fetch($value['ID_TCKRN'] );



              //$incident=$array_obj;
              //$this->sendResponse("[" . $incident->ReferenceNumber . "]["  . $value['PARTY_SITE_NUMBER'] . "]");
              $listaTicket[$indiceArreglo]['ref_no'] =  $value['ID_TCKRN'];

              $oportunidad = new RNCPHP\Opportunity();


              if($incident->AssignedTo->Account->SalesSettings->Territory->ID<>'')
              {
                $SalesTerritory=RNCPHP\SalesTerritory::fetch($incident->AssignedTo->Account->SalesSettings->Territory->ID);
                $oportunidad->Territory = $SalesTerritory;
              }

              $Organization  = RNCPHP\Organization::find("CustomFields.c.rut='" . $value['ACCOUNT_NUMBER'] . "'");
              $oportunidad->Organization  = $Organization[0];
              $oportunidad->Summary  =$value["NOMBRE_CONTACTO"] .  " Tel: " . $value["FONO_CONTACTO"] ;
              $eje=RNCPHP\Comercial\Ejecutivo::first("sales_rep_id ='" . $value['IDVENDEDOR']  . "'");
              $oportunidad->CustomFields->c->id_hh = $incident->CustomFields->c->id_hh;
              $oportunidad->Contacts=RNCPHP\Contact::fetch(48170);

              //$oportunidad->CustomFields->Comercial->EjecutivoZonal = new RNCPHP\Contact::fetch(36624);
              $oportunidad->CustomFields->Comercial->EjecutivoZonal = RNCPHP\Contact::fetch(48215);
              $oportunidad->CustomFields->Comercial->Vendedor= RNCPHP\Contact::fetch(48219);
              $array_Direccion_obj = RNCPHP\DOS\Direccion::find(' party_site_number = ' . $value['PARTY_SITE_NUMBER'] );
              $oportunidad->CustomFields->OP->Direccion =  $array_Direccion_obj[0];
              $oportunidad->name='PPTO VENUS TKRN ' . $value['ID_TCKRN'];
              $oportunidad->PrimaryContact                  = new RNCPHP\OpportunityContact();
              $oportunidad->PrimaryContact->Contact         = RNCPHP\Contact::fetch(36624);
              $oportunidad->AssignedToAccount=RNCPHP\Account::fetch( 88);

              $oportunidad->Save();
              $listaTicket[$indiceArreglo]['op_id']=$oportunidad->ID;

              $indiceArreglo++;
              $texto=$texto . $value['IDPRESUPUESTO'] . ";";
              $texto=$texto . $oportunidad->ID . ";";
              $texto=$texto .$value['ID_TCKRN'] . "<br>";
         }



         $items = '{"items":[
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"40825","CANTIDAD":"3","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"10000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"34793","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"20000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"46958","CANTIDAD":"5","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"330000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"46327","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"140000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"33729","CANTIDAD":"6","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"15000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"31978","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"10000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"38690","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"10000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"33647","CANTIDAD":"4","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"16000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"33732","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"10000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"38689","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"17000"},

             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"38583","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"18000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"36078","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"19000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"40832","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"11200"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"37802","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"1300"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"36794","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"1400"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"35165","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"15600"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"40826","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"1780"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"40827","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"18800"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"40825","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"13200"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"32870","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"13400"},

             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"45291","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"14500"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"45292","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"16500"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"34793","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"10760"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"46958","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"10870"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"46327","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"182000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"33729","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"1778"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"31978","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"1780"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"38690","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"10320"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"33647","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"12200"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"33732","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"22000"},

             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"38689","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"45600"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"38583","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"89000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"36078","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"78600"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"40832","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"89000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"37802","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"90000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"36794","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"6000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"35165","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"389000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"40826","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"70000"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"40827","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"72300"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"50099","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"7200"},

             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"50050","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"8900"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"50045","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"3450"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"50058","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"46700"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"35000","COD_DELFOS":"50037","CANTIDAD":"1","INVENTORY_ITEM_ID":"7941","VALORUNITARIO":"20500"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"10201","COD_DELFOS":"50046","CANTIDAD":"1","INVENTORY_ITEM_ID":"544495","VALORUNITARIO":"32200"},
             {"PTTO":"' . $oportunidad->ID . '","IDPRESUPUESTO":"9201","VALORNETO":"10201","COD_DELFOS":"45008","CANTIDAD":"1","INVENTORY_ITEM_ID":"544495","VALORUNITARIO":"11686"}
   ]}';
         $array_data=json_decode($items,true);
         $texto='';

         foreach ($array_data['items']  as $key => $value)
         {
             $oportunidad = RNCPHP\Opportunity::fetch( $value['PTTO']);
             $texto=$texto . '--' . $value['PTTO'];
             //$this->sendResponse($texto);

             $WorkForceLine                    = new RNCPHP\OP\OrderItems();
             $WorkForceLine->QuantitySelected  = $value['CANTIDAD'];
             $WorkForceLine->QuantityReserved  = $value['CANTIDAD'];
             $WorkForceLine->QuantityConfirmed = $value['CANTIDAD'];

             //$WorkForceLine->UnitSellingPrice= $value['VALORUNITARIO'];
             //$WorkForceLine->UnitTempSellPrice = $value['VALORUNITARIO'];
             //$WorkForceLine->ConfirmedSellPrice = value['VALORUNITARIO'];


             $producto = RNCPHP\OP\Product::find("CodeItem = '" . $value['COD_DELFOS'] .  " ' ");

             if (empty($producto)) {
                 $this->sendResponse($value['COD_DELFOS']);

             }
             $WorkForceLine->Product           = $producto[0];
             $WorkForceLine->Opportunity       = $oportunidad;
             $WorkForceLine->State             = 3; //Confirmado
             $WorkForceLine->Save();

             //Actualizando precio producto


             $producto[0]->UnitSellingPrice = $value['VALORUNITARIO'];
             $producto[0]->Save();

             //Actualizando precio linea
             $idOP                          = $oportunidad->ID;
             $obj_line                      = RNCPHP\OP\OrderItems::first("Product.ID =  '{$producto[0]->ID}' and Opportunity.ID = {$idOP} ");
             $obj_line->UnitTempSellPrice   = $producto[0]->UnitSellingPrice;

             $obj_line->Save();

           /*  $producto2 = RNCPHP\OP\Product::find("CodeItem = '" . $value['COD_DELFOS'] .  " ' ");
             $producto2[0]->UnitSellingPrice = $temprice;
             $producto2[0]->Save();
   */

         }

        $this->sendResponse($texto);

    }

    public function CrearIncidenteServicioM()
    {
      $bannerNumber = 0;
      $indiceDatos = 'datos';
      $indiceAccion = 'accion';
      $indiceUsuario = 'usuario';
      $indiceHH='HH';
      $indiceEstado = 'estado';
      $indiceTipoSolicitud = 'tiposolicitud';
      $indiceSubject = 'asunto';
      $indiceOrden_Activacion = 'orden_activacion';
      $indiceLinea_Orden_Activacion = 'linea_orden_activacion';
      $indiceAssignedTo = "AssignedTo";
      $indiceshipping_instructions ="instrucciones";

      $indiceArreglo=0;
      /*
      $data_post  = $this->getdataPOST();
      $array_data = json_decode(utf8_encode($data_post), true);
    */
      $respuesta= array();
      $listaTicket= array();
      $incidents=array();


      if (empty($_POST))
      {
        //  $response = $this->responseError(1);
        //  $this->sendResponse($response);

        //$data_post='{"usuario":"UserDimacofi","accion":"CrearIncidenteServicio","datos":[{"HH":"1308550","estado":1,"tiposolicitud":28,"asunto":"Instalacion Maquina Nueva HH 1308550","orden_activacion":"50002","linea_orden_activacion":"1-8","AssignedTo":"209","instrucciones":"Valentin Oyarce-+56974786715"}]}';
        $data_post='{"usuario": "UserDimacofi","accion": "CrearIncidenteServicio","datos":[
          {"HH": "1668569","estado": 1,"tiposolicitud": 28,"asunto": "Instalacion Maquina Nueva HH 1668569","orden_activacion": "4219","linea_orden_activacion": "4.1","AssignedTo": "209","instrucciones": "DENIS BAEZA-ebohn@pjud.cl-452479086"}


]}';

        $array_data=json_decode($data_post,true);
      }
      else {

        $data_post  = $this->getdataPOST();
        $array_data=json_decode($data_post,true);
      }

      if (empty($data_post))
      {
          $response = $this->responseError(1);
          $this->sendResponse("Error ->" . $data_post);
      }

      if (is_array($array_data) and ($array_data != false))
      {
             $this->ValidaArreglo($indiceAccion,$array_data);


      try
      {




          foreach ($array_data[$indiceDatos]  as $key => $value)
            {

              $orden_activacion[$indiceArreglo] =  $value[$indiceOrden_Activacion];
              $linea_orden_activacion[$indiceArreglo] =  $value[$indiceLinea_Orden_Activacion];
              $hh[$indiceArreglo] =  $value[$indiceHH];

              if(!array_key_exists($indiceDatos, $array_data))
              {
                $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
                $this->sendResponse($this->error);
                return;
              }


              //$res = RNCPHP\ROQL::query("SELECT ID FROM Contact where Name.Last ='" . $value['contacto']['last_name']  . "' and Name.First ='" . $value['contacto']['first_name']  . "' and login='" . $value['contacto']['email'] . "'  ORDER BY ID DESC LIMIT 1 "  )->next();

              //if (!empty($value['contacto']['email']) or $value['contacto']['email']='')

              if(!empty($value['instrucciones']))
              {

                $record = explode("-",$value['instrucciones'] );


                //$res = RNCPHP\ROQL::query("SELECT ID FROM Contact where  login='" . $value['contacto']['email'] . "'  ORDER BY ID DESC LIMIT 1 "  )->next();
                  $res = RNCPHP\ROQL::query("SELECT ID FROM Contact where  login='" . $record[1] . "'  ORDER BY ID DESC LIMIT 1 "  )->next();

                  $contactObj = $res->next();

                  if($contactObj['ID'])
                  {
                    $c_id = $contactObj['ID'];
                    $contact =  RNCPHP\Contact::fetch($c_id);
                    $person = explode(" ",$record[0] );

                    $contact->Name->First =$person[0];
                    $contact->Name->Last = $person[1];
                    $contact->save();
                  }
                  else {
                    if(strstr($record[1], '@'))
                    {



                    $contact = new RNCPHP\Contact();
                    $contact->Login = $record[1];
                    $person = explode(" ",$record[0] );
                    $contact->Name = new RNCPHP\PersonName();
                    $contact->Name->First =$person[0];
                    $contact->Name->Last = $person[1];

                              //add email addresses
                    $contact->Emails = new RNCPHP\EmailArray();
                    $contact->Emails[0] = new RNCPHP\Email();
                    $contact->Emails[0]->AddressType=new RNCPHP\NamedIDOptList();
                    $contact->Emails[0]->AddressType->LookupName = "Correo electrónico - Principal";
                    $contact->Emails[0]->Address = $record[1];


                    $i = 0;
                    if($record[2])
                    {
                    $contact->Phones = new RNCPHP\PhoneArray();
                    $contact->Phones[$i] = new RNCPHP\Phone();
                    $contact->Phones[$i]->PhoneType = new RNCPHP\NamedIDOptList();
                    $contact->Phones[$i]->PhoneType->LookupName = 'Teléfono de oficina';
                    $contact->Phones[$i]->Number =  $record[2];
                    }

                    $i++;
                    $contact->save();
                    }
                    else {
                        $contact =  RNCPHP\Contact::fetch(36624);
                    }

                  }
          }
          else {
            $contact =  RNCPHP\Contact::fetch(36624);
          }
              //$this->sendResponse($contact->Login);

              //$this->sendResponse($contact->ID  . ' ' . $value['contacto']['last_name'] . ' ' . $value['contacto']['first_name'] . ' ' .$value['contacto']['phone'] . ' ' .$value['contacto']['email'] );


              $array_obj = RNCPHP\Incident::find(" CustomFields.c.orden_activacion = '" . $linea_orden_activacion[$indiceArreglo]    .  "' and  CustomFields.c.nota_pedido =  "    .  $orden_activacion[$indiceArreglo]  .  " and CustomFields.c.id_hh ='"  .  $hh[$indiceArreglo] . "'"   );


              if($array_obj[0]->ReferenceNumber<>'')
              {
                // si existe crea un elemento con los datps devueltos
                $listaTicket[$indiceArreglo]['ref_num']=$array_obj[0]->ReferenceNumber;
                $listaTicket[$indiceArreglo]['orden_activacion']=$orden_activacion[$indiceArreglo];
                $listaTicket[$indiceArreglo]['linea_orden_activacion']=$linea_orden_activacion[$indiceArreglo] ;
                $listaTicket[$indiceArreglo]['message']='ok';
                $listaTicket[$indiceArreglo]['estado']='true';

                //$asset = RNCPHP\Asset::find( "asset_id = ". $array_obj[0]->asset_id );
                $res='';

                $contadores = RNCPHP\DOS\Contador::find("incident = "  . $array_obj[0]->ID  );
                $indicecontador=0;
                $cont=array();
                foreach ($contadores  as $key => $value2)
                {
                  $cont[$indicecontador]['ID']   =  $value2->ID;
                  $cont[$indicecontador]['Valor']   = $value2->Valor;
                  $cont[$indicecontador]['Tipo']=$value2->TipoContador->LookupName;
                  $indicecontador++;
                }


                $listaTicket[$indiceArreglo]['Contadores'] =$cont;
              }
              else
              {
                // si el  el ticket no existe se crea

                $listaTicket[$indiceArreglo]['ref_num']='';
                $listaTicket[$indiceArreglo]['orden_activacion']='';
                $listaTicket[$indiceArreglo]['message']='';
                $listaTicket[$indiceArreglo]['estado']='';




                //RNCPHP\ConnectAPI::commit();
                $incidents[$indiceArreglo]                                         = new RNCPHP\Incident();
                $incidents[$indiceArreglo]->Subject                                = $value[$indiceSubject] ;
                $incidents[$indiceArreglo]->Disposition                            = RNCPHP\ServiceDisposition::fetch($value[$indiceTipoSolicitud]);
                $incidents[$indiceArreglo]->PrimaryContact                         = RNCPHP\Contact::fetch($contact->ID); //RNCPHP\Contact::fetch(48170); //SC/SC/
                $incidents[$indiceArreglo]->AssignedTo->Account                    = RNCPHP\Account::fetch($value[$indiceAssignedTo]);
                $incidents[$indiceArreglo]->StatusWithType->Status->ID             = $value[$indiceEstado];
                $incidents[$indiceArreglo]->CustomFields->c->shipping_instructions = $value[$indiceshipping_instructions];
                $incidents[$indiceArreglo]->CustomFields->c->nota_pedido           = $value[$indiceOrden_Activacion];
                $incidents[$indiceArreglo]->CustomFields->c->id_hh                 = $value[$indiceHH];
                $incidents[$indiceArreglo]->CustomFields->c->orden_activacion      = $value[$indiceLinea_Orden_Activacion];
                $incidents[$indiceArreglo]->CustomFields->c->requiere_taller       = false;
                //$incidents[$indiceArreglo]->CustomFields->c->seguimiento_tecnico->LookupName='Visita Técnico Asignado';
                $incidents[$indiceArreglo]->Save(RNCPHP\RNObject::SuppressExternalEvents);




                try
                {
                  //obtiene valor de HH
                  $id_hh = $incidents[$indiceArreglo]->CustomFields->c->id_hh; //168665
                  $array_post     = array('usuario' => 'appmind',
                              'accion' => 'info_hh',
                              'datos'=> array('id_hh'=> $id_hh)
                              );
                  $json_data_post = json_encode($array_post);
                  //$json_data_post = $this->Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
                  $json_data_post = $this->blowfish->encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
                  $json_data_post = base64_encode($json_data_post);
                  $postArray = array ('data' => $json_data_post);

                  $result = $this::requestPost($this->URL_GET_HH, $postArray);


                  if ($result != false) {
                  $arr_json = json_decode($result, true);


                  if ($arr_json != false)
                  {
                    if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json)) ))
                    {
                      $respuesta  = base64_decode($arr_json['respuesta']);



                      switch ($arr_json['resultado'])
                      {
                        case "true":

                          $json_hh = $this->blowfish->decrypt($respuesta, "D3t1H6q0p6V7z8", 10, 22, NULL);

                          $array_hh_data = json_decode(utf8_encode($json_hh),true);
                          if (!is_array($array_hh_data))
                          {
                            $message = "ERROR: Estructura JSON encriptado No valida ".PHP_EOL;
                            $message .= "JSON: ".$json_hh;
                            $bannerNumber = 3;
                            break;
                          }
                          $array_hh_data = $array_hh_data['respuesta'];
                          $incidents[$indiceArreglo]->CustomFields->c->marca_hh  = $array_hh_data['Marca'];
                          $incidents[$indiceArreglo]->CustomFields->c->modelo_hh = $array_hh_data['Modelo'];
                          $incidents[$indiceArreglo]->CustomFields->c->convenio  = (int)  $array_hh_data['Convenio'];
                          $incidents[$indiceArreglo]->CustomFields->c->tipo_contrato  = $array_hh_data['TipoContrato'];
                          $incidents[$indiceArreglo]->CustomFields->c->sla_hh    = $array_hh_data['SLA'];
                          $incidents[$indiceArreglo]->CustomFields->c->sla_hh_rsn    = $array_hh_data['RSN'];
                          $incidents[$indiceArreglo]->CustomFields->c->serie_maq  = $array_hh_data['Serie'];
                          $incidents[$indiceArreglo]->CustomFields->c->numero_delfos  = $array_hh_data['delfos'];
                          $array_hh_direccion_id =  $array_hh_data['Direccion'];
                          $incidents[$indiceArreglo]->CustomFields->c->cliente_bloqueado =(int) $array_hh_direccion_id['Bloqueado'];
                          $incidents[$indiceArreglo]->CustomFields->c->soporte_telefonico=0;
                          $id_ebs_direccion = $array_hh_direccion_id['ID_direccion'];

                          $array_Direccion_obj = RNCPHP\DOS\Direccion::find('d_id = '. $id_ebs_direccion);
                          if (is_array($array_Direccion_obj) and is_object($array_Direccion_obj[0]))
                          {
                            $incidents[$indiceArreglo]->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
                            $incidents[$indiceArreglo]->StatusWithType->Status->ID   =  $value[$indiceEstado];
                          }
                          $array_hh_contadores =  $array_hh_data['Contadores'];
                          $incidents[$indiceArreglo]->Save(RNCPHP\RNObject::SuppressAll);

                          $asset = RNCPHP\Asset::first( "SerialNumber = '".$incidents[$indiceArreglo]->CustomFields->c->id_hh."'");
                          if (empty($asset)) {
                            $asset = new RNCPHP\Asset;
                            //$asset->Name = $incident->CustomFields->c->id_hh."-".$ncident->CustomFields->c->marca_hh."-".$ncident->CustomFields->c->modelo_hh;
                            $nameHH = $incidents[$indiceArreglo]->CustomFields->c->id_hh."-".$incidents[$indiceArreglo]->CustomFields->c->marca_hh."-".$incidents[$indiceArreglo]->CustomFields->c->modelo_hh;
                            $asset->Name = substr($nameHH, 0, 80);

                            $asset->Contact = $incidents[$indiceArreglo]->PrimaryContact;
                            //$asset->Organization = $incident->Organization;
                            $asset->Product = 2;
                            $asset->SerialNumber = $incidents[$indiceArreglo]->CustomFields->c->id_hh;
                            $asset->Save(RNCPHP\RNObject::SuppressAll);
                          }
                          $asset->CustomFields->DOS->Direccion =  $incidents[$indiceArreglo]->CustomFields->DOS->Direccion;
                          $incidents[$indiceArreglo]->Asset = $asset;

                          $incidents[$indiceArreglo]->Save(RNCPHP\RNObject::SuppressAll);
                          //Contadores


                          foreach ($array_hh_contadores as $counter)
                          {
                            $count_id    = $counter['ID'];
                            $count_tipo  = $counter['Tipo'];
                            $count_valor = $counter['Valor'];
                            $contador               = new RNCPHP\DOS\Contador();
                            $contador->ContadorID   = $count_id;
                            $contador->Valor        = $count_valor;
                            $contador->Incident     = $incidents[$indiceArreglo];
                            $contador->TipoContador = RNCPHP\DOS\TipoContador::fetch($counter['Tipo']);
                            $contador->Asset        = $incident->Asset;

                            $contador->Save(RNCPHP\RNObject::SuppressAll);



                          }


                          $listaTicket[$indiceArreglo]['ref_num']= $incidents[$indiceArreglo]->ReferenceNumber;
                          $listaTicket[$indiceArreglo]['orden_activacion']=$value[$indiceOrden_Activacion];

                          $listaTicket[$indiceArreglo]['linea_orden_activacion']=$value[$indiceLinea_Orden_Activacion];
                          $listaTicket[$indiceArreglo]['message']='ok';
                          $listaTicket[$indiceArreglo]['estado']='true';
                          $listaTicket[$indiceArreglo]['Contadores'] =$array_hh_data['Contadores'];

                          RNCPHP\ConnectAPI::commit();


                          break;
                        case False:
                          $listaTicket[$indiceArreglo]['ref_num']='';
                          $listaTicket[$indiceArreglo]['orden_activacion']=$value[$indiceOrden_Activacion];
                          $listaTicket[$indiceArreglo]['message']="ERROR: Servicio responde con fallo ".PHP_EOL;
                          $message ="ERROR: Servicio responde con fallo ".PHP_EOL;
                          $listaTicket[$indiceArreglo]['estado']='false';
                          $bannerNumber = 3;
                          RNCPHP\ConnectAPI::rollback();
                          break;
                        default:
                          $listaTicket[$indiceArreglo]['ref_num']='';
                          $listaTicket[$indiceArreglo]['orden_activacion']=$value[$indiceOrden_Activacion];
                          $listaTicket[$indiceArreglo]['message']="ERROR: Respuesta fallida ".PHP_EOL;
                          $listaTicket[$indiceArreglo]['rest']=$arr_json['resultado'];
                          $listaTicket[$indiceArreglo]['resp']=$respuesta;
                          $listaTicket[$indiceArreglo]['resl']=$result;
                          $listaTicket[$indiceArreglo]['estado']='false';
                          $message ="ERROR: Respuesta fallida ".PHP_EOL;
                          $bannerNumber = 3;
                          RNCPHP\ConnectAPI::rollback();
                          break;
                      }
                    }
                    else {
                      $message = "ERROR: Estructura JSON No valida ". PHP_EOL;
                      $bannerNumber = 3;
                      $listaTicket[$indiceArreglo]['message']=$message;
                      $listaTicket[$indiceArreglo]['estado']='false';
                      RNCPHP\ConnectAPI::rollback();
                    }

                  }
                  else
                  {
                    $message = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
                    $bannerNumber = 3;
                    $respuestaApi['resultado']='false';
                    $respuestaApi['respuesta']['glosa']=$message;
                    $listaTicket[$indiceArreglo]['message']=$message;
                    $listaTicket[$indiceArreglo]['estado']='false';
                    RNCPHP\ConnectAPI::rollback();
                  }
                }
                else {
                  $message = "ERROR: ".$this::getResponseError();
                  $bannerNumber = 3;
                  $respuestaApi['resultado']='false';
                  $respuestaApi['respuesta']['glosa']=$message;
                  $listaTicket[$indiceArreglo]['message']=$message;
                  RNCPHP\ConnectAPI::rollback();
                }

                //self::insertPrivateNote($incidents[$indiceArreglo], $message);
                //self::insertBanner($incidents[$indiceArreglo], $bannerNumber);



              }
              catch (Exception $e)
              {
                 $message = "Error ".$e->getMessage();
                 $respuestaApi['resultado']='false';
                 $respuestaApi['respuesta']['glosa']=$message;
                 $listaTicket[$indiceArreglo]['message']=$message;
                 //self::insertPrivateNote($incident, $message );
              }

            }
            $indiceArreglo++;

          }
            $respuestaApi['respuesta']['tickets']=$listaTicket;
            $respuestaApi['resultado']='true';
            $respuestaApi['respuesta']['glosa']='ok';
            $this->sendResponse(json_encode($respuestaApi));



      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $respuestaApi['resultado']='false';
        $respuestaApi['respuesta']['glosa']=$this->error;
        RNCPHP\ConnectAPI::rollback();
        $this->sendResponse(json_encode($respuestaApi));
        return;
      }
    } else {
    //RNCPHP\ConnectAPI::rollbak();
    $respuestaApi['resultado']='false';
    $respuestaApi['respuesta']['glosa']=$this->error;
    $this->sendResponse($this->responseError(2));
    }
    }

    public function send_mail($op_id)
    {
      require_once(get_cfg_var("doc_root") . "/ConnectPHP/Connect_init.php");
      //error_reporting(E_ALL);
      initConnectAPI();
      $oportunidad = RNCPHP\Opportunity::fetch( $op_id);

        try
        {
          $mm = new RNCPHP\MailMessage();
          $mm->To->EmailAddresses = array("kmorales@dimacofi.cl");

          //$mm->CC->EmailAddresses = array($email_2);
          //$mm->BCC->EmailAddresses = array($email_3);
          $mm->Subject = "PDF ". $op_id . " " . date("Y/m/d");

          if (count($oportunidad->FileAttachments))
          {
            //$mm->Body->Html = '<TABLE BORDER=1 WIDTH=300><TD WIDTH=100></TD><TD WIDTH=100></TD><TD WIDTH=100></TD></TABLE>';
            //$mm->Body->Text = 'Cotizacion Generada para Presupuesto ' . $op_id;
            $mm->Body->Html = '<B>Cotizacion Dimacofi S.A.<BR>Presupuesto ' . $op_id ;

            $mm->FileAttachments[] =  $oportunidad->FileAttachments[count($oportunidad->FileAttachments)-1];
          }
          else {
            $mm->Body->Text = "sin datos";
          }
          //$this->sendResponse(count($oportunidad->FileAttachments));

          $mm->send();

          if($mm->Status->Sent)
          {
            //Success
          }
          else
          {
            //Failure
          }
        }

        catch ( Exception $err )
        {
          echo "<br><b>Exception</b>: line ".__LINE__.": ".$err->getMessage()."</br>";
        }
      return $op_id;
    }

    public function testpdf()
    {
  //$this->sendResponse("955" .   $this->companyInfo1->Value );

        $op_id =   $_GET["op"];

        //se carga la librería

        //$this->sendResponse("1719");

        //Se trae la oportunidad
        $op = $this->OpportunityModel->getOpportunity($op_id);
        
        //Codigo para renombrar archivo
        if (!empty($op->FileAttachments)) {
            $last_pdf_name = end($op->FileAttachments)->FileName;
            $last_name = substr($last_pdf_name,0,strlen($last_pdf_name)-4);
            $name_pdf = "Cotizacion - ".$op->ID;

            if (!(strlen($last_name) > strlen($name_pdf))) {
              $name = "Cotizacion - ".$op->ID." (1)";
            }else {
              $num = substr($last_name,strlen($last_name)-2,strlen($last_name)-1);
              $num = (int)(str_replace(')','',$num));
              $num++;
              $name = "Cotizacion - ".$op->ID." (".$num.")";
            }

        }
        else {
          $name = "Cotizacion - ".$op->ID;
        }

        //Creando Contenido de archivo
        $this->fpdf2->AliasNbPages();
        $this->fpdf2->AddPage();
        //$this->fpdf2->SetFont('Times','',12);

        //Agregando Cabecera
        $this->xPos = $this->fpdf2->GetPageWidth()-100-100; // Ancho Página - Ancho Texto - Margenes LR
        $this->yPos = $this->fpdf2->GetY();
        $this->yspace = 3;

        $a_companyInformation = array();

        //$a_companyInformation[] = 'Dimacofi S.A. Giro:';
        //$a_companyInformation[] = $this->companyInfo1->Value;
        //$a_companyInformation[] = 'Importadora y Distribuidora de Equipos de Oficina';
        $a_companyInformation[] = $this->companyInfo2->Value ;
        // $a_companyInformation[] = 'RUT: 92.083.000-5';
        $a_companyInformation[] = $this->companyInfo3->Value;
        // $a_companyInformation[] = 'Casa Matriz: Av. Vitacura 2939, Piso 15, Las Condes';
        $a_companyInformation[] = $this->companyInfo4->Value;
        // $a_companyInformation[] = 'Fono: (02) 2549 7777 - Fax: (02) 2549 7250';
        $a_companyInformation[] = $this->companyInfo5->Value;
        // $a_companyInformation[] = 'Santiago de Chile - www.dimacofi.cl';
        $a_companyInformation[] = $this->companyInfo6->Value;

        //Agregando Información de Compañia al PDF
        $this->fpdf2->addCompanyInformation($a_companyInformation, $this->xPos, $this->yPos);

        //Agregando Titulo
        $this->xPos = 10;
        $this->yPos = $this->fpdf2->GetY() + $this->yspace;
        $fechaActual = date("d/m/Y");
        $this->fpdf2->addTitleInformation(["Número Presupuesto: {$op->ID}", "Fecha: {$fechaActual}"], $this->xPos, $this->yPos);

        //Información General del Cliente y del PPTO
        $this->yPos = $this->fpdf2->GetY() + $this->yspace;

        $a_customerInformation   = array();
        $a_customerInformation[] = array("label" => 'Razón Social' , "value" => "{$op->Organization->Name}" );
        $a_customerInformation[] = array("label" => 'Nombre Contacto' , "value" => $op->PrimaryContact->Contact->Name->First." ".$op->PrimaryContact->Contact->Name->Last );
        $a_customerInformation[] = array("label" => 'Email' , "value" => $op->PrimaryContact->Contact->Emails[0]->Address );
        $a_customerInformation[] = array("label" => 'RUT' , "value" => "{$op->Organization->CustomFields->c->rut}" );
        $a_customerInformation[] = array("label" => 'Teléfono' , "value" => "{$op->PrimaryContact->Contact->Phones[0]->Number}" );
        $a_customerInformation[] = array("label" => 'Dirección Contacto' , "value" => $op->CustomFields->OP->Direccion->dir_envio );
        $a_customerInformation[] = array("label" => 'Forma de Pago' , "value" => $op->CustomFields->c->payment_conditions->LookupName );
        //RTC 2017/03/13
        $a_customerInformation[] = array("label" => 'Vendedor' , "value" => $op->CustomFields->Comercial->Ejecutivo->name  );
        //$a_customerInformation[] = array("label" => 'Vendedor' , "value" => $op->CustomFields->Comercial->Vendedor->LookupName );
        /*$a_customerInformation[] = array("label" => 'Comuna' , "value" => $op->CustomFields->OP->Direccion->comuna->com_desc );
        $a_customerInformation[] = array("label" => 'Provincia' , "value" => $op->CustomFields->OP->Direccion->comuna->prov_id->prov_desc);
        $a_customerInformation[] = array("label" => 'Región' , "value" => $op->CustomFields->OP->Direccion->comuna->prov_id->reg_id->reg_desc);*/
        $a_customerInformation[] = array("label" => 'Valor de Dolar' , "value" => $op->CustomFields->c->usd_value);

        if (!empty($op->CustomFields->OP->IncidentService))
        {
          $a_hhInformation   = array();
          $a_hhInformation[] = array("label" => 'Modelo' , "value" => $op->CustomFields->OP->IncidentService->CustomFields->c->modelo_hh);
          $a_hhInformation[] = array("label" => 'HH' , "value" => $op->CustomFields->OP->IncidentService->CustomFields->c->id_hh );
          $a_hhInformation[] = array("label" => 'Serie' , "value" => $op->CustomFields->OP->IncidentService->CustomFields->c->serie_maq );
          $a_hhInformation[] = array("label" => 'Técnico' , "value" => $op->CustomFields->OP->IncidentReparation->AssignedTo->Account->Name->First." ".$op->CustomFields->OP->IncidentReparation->AssignedTo->Account->Name->Last );
        }
        else
        {
          $a_hhInformation = array();
        }
        //Agregar Información de Cliente y HH al PDF
        $this->fpdf2->addTableCustomerInformation($a_customerInformation,$a_hhInformation, $this->xPos, $this->yPos);


        //Información de las lineas
        $a_items_pdf = array();
        $a_items = $this->OpportunityModel->getItems($op_id);

        $totalNetValue = 0;
        //$a_items = array();
        //RTC 2016 10/03/2017   acumulador de total
        $descuentoTotal=0;
        foreach ($a_items as $key => $item)
        {
          $a_items_temp_pdf['quantity']    = $item->QuantitySelected;
          $a_items_temp_pdf['description'] = $item->Product->Name;
          $a_items_temp_pdf['stock'] = $item->temp_stock;
          $a_items_temp_pdf['unitValue']   = number_format($item->UnitTempSellPrice);
          $a_items_temp_pdf['netValue']    = number_format($item->ConfirmedSellPrice); //$item->ConfirmedSellPrice;
          $a_items_temp_pdf['dolar_value']    = number_format($item->dolar_value);
          $a_items_temp_pdf['codigo']    = $item->Product->CodeItem;
          //$a_items_temp_pdf['discount']    = (!empty($item->DiscountSellPrice)) ? $item->DiscountSellPrice:0 ;
          $a_items_pdf[] = $a_items_temp_pdf;

          $totalNetValue += $a_items_temp_pdf['netValue'];



          //RTC 2016 10/03/2017
          $descuentoTotal=$descuentoTotal + $item->QuantitySelected*$item->UnitTempSellPrice-$item->ConfirmedSellPrice;
        }

        //Iva aplicado al valor sin descuento
        /*
        $iva = ($totalNetValue * 19) / 100;
        $iva = round($iva);
        */

        //Iva aplicado al valor con Descuento
        $iva = ($op->ClosedValue->Value * 19) / 100;
        $iva = round($iva);

        $this->yPos = $this->fpdf2->GetY() + $this->yspace;

        $a_totalValues   = array();
        $a_totalValues[] = array("label" => 'Valor Neto $' , "value" => number_format($op->ClosedValue->Value) );
        $discount        = $op->CustomFields->c->discount_selling;
        //RTC 2016 10/03/2017
        $a_totalValues[] = array("label" => '% Descuento' , "value" => (!empty($discount)) ? $discount:0 );
        //
        $a_totalValues[] = array("label" => 'Valor Descuento' , "value" => number_format($descuentoTotal));

        $a_totalValues[] = array("label" => 'I.V.A.' , "value" => number_format($iva));
        $finallyPrice    = $op->ClosedValue->Value + $iva;
        $a_totalValues[] = array("label" => 'Total' , "value" => number_format($finallyPrice));

        //Agregando los Items al PDF
        $this->fpdf2->addTableLines_insumos($a_items_pdf, $a_totalValues , $this->xPos, $this->yPos);

        // Observaciones
        $this->yPos = $this->fpdf2->GetY() + $this->yspace;
        $this->fpdf2->addComment($op->Summary, $this->xPos, $this->yPos);

        $a_conditionsInformation = array();
        $a_inx = array();
        $a_conditionsInformation[] ="";
        $a_inx[]='Observaciones:';
        // $a_conditionsInformation[] = '1. Este presupuesto tiene vigencia de 20 días. En caso de cummplirse el plazo, se facturará 1 UF + IVA por concepto de visita doagnóstico.'; //CUSTOM_MSG_PDF_CONDITIONS_INFO_1
        $a_conditionsInformation[] = "";
        $a_inx[]='';
        // $a_conditionsInformation[] = '2. Si el cliente acepta el presupuesto, debe enviarlo firmado y timbrado al correo aconcepcion@dimacofi.cl o al Fax (02) 2549 7430.';
        $a_conditionsInformation[] = 'Tipo de Cambio :'  . $op->CustomFields->c->usd_value;
        $a_inx[]='-';
        /*
        // $a_conditionsInformation[] = '3. De no haber algún respuesto en stock, se debe informará el tiempo de disponibilidad, y se facturará una vez efecturada la repración.';
        $a_conditionsInformation[] = 'Para depósitos o transferencias electrónicas:';
        
        $a_inx[]='-';
        // $a_conditionsInformation[] = '4. La garantía de 3 meses sólo aplica en "Mano de Obra", siempre y cuando la segunda reparación tenga relación con la primera falla.';
        $a_conditionsInformation[] = 'BANCO SANTANDER, CUENTA CORRIENTE Nº 295396, RUT 92.083.000-5.';
        $a_inx[]='';
        // $a_conditionsInformation[] = '5. Dimacofi podrá facturar a partir de la fecha en que se acepta el presupuesto por parte del cliente.';
        $a_conditionsInformation[] = 'BANCO ESTADO, CUENTA CORRIENTE Nº 27629-4, RUT 92.083.000-5.';
        $a_inx[]='-';
        $a_conditionsInformation[] = 'BANCO BCI, CUENTA CORRIENTE Nº 10472436, RUT 92.083.000-5';
        $a_inx[]='-';
        $a_conditionsInformation[] = '';
        $a_inx[]='';
        $a_conditionsInformation[] = '';
        $a_inx[]='-';
        $a_conditionsInformation[] = '';
        $a_inx[]='-';
        
        $a_conditionsInformation[] = '';
        $a_inx[]=' ';
        

        $a_conditionsInformation[] = '';
        $a_inx[]='-';

        $a_conditionsInformation[] = '';
        $a_inx[]='-';

        $a_conditionsInformation[] = "";
        $a_inx[]='';
    
*/
        //Información Final
        $this->yPos = $this->fpdf2->GetY() + $this->yspace;

        //agregando las condiciones al PDF
        $this->fpdf2->addConditions_V2($a_conditionsInformation, $this->xPos, $this->yPos,$a_inx);

        $this->fpdf2->addTexto('Dimacofi S.A. RUT 92.083.000-5');
        $this->fpdf2->addTexto('BANCO SANTANDER, CUENTA CORRIENTE Nº 295396.', $this->xPos, $this->yPos);
        $this->fpdf2->addTexto('BANCO ESTADO, CUENTA CORRIENTE Nº 27629-4.', $this->xPos, $this->yPos);
        $this->fpdf2->addTexto('BANCO BCI, CUENTA CORRIENTE Nº 10472436.', $this->xPos, $this->yPos);
        $this->fpdf2->addTexto('Una vez realizada la transferencia favor enviar comprobante a arangel@dimacofi.cl', $this->xPos, $this->yPos);
        /*
      $this->SetFont('Arial', 'B', 9);
			$this->Cell($bounds[0], 4, utf8_decode('Dimacofi S.A. 92.083.000-5     Banco: Scotiabank       Cuenta Corriente: 71-10187-02'), 1, 0, 'L');
			$this->SetFont('Arial', '', 8);
        
      $this->SetFont('Arial', 'B', 9);
			$this->Cell($bounds[0], 4, utf8_decode('Una vez realizada la transferencia favor enviar comprobante a arangel@dimacofi.cl'), 1, 0, 'L');
			$this->SetFont('Arial', '', 8);
        */


        $this->yPos = $this->fpdf2->GetY() + $this->yspace;
        $this->fpdf2->addSing($this->xPos, $this->yPos);

        $this->yPos = $this->fpdf2->GetY() + $this->yspace;

        $a_ejecutiveInformation = array();
    /*
        $a_ejecutiveInformation[] = array('label' => 'Ejecutiva:', 'value' => $op->AssignedToAccount->Name->First. " ". $op->AssignedToAccount->Name->Last );
        $a_ejecutiveInformation[] = array('label' => 'Teléfono Contacto $:', 'value' => $op->AssignedToAccount->Phones[0]->Number );
        $a_ejecutiveInformation[] = array('label' => 'Correo Electrónico:', 'value' => $op->AssignedToAccount->Emails[0]->Address );
        $a_ejecutiveInformation[] = array('label' => 'Ciudad:', 'value' => $op->AssignedToAccount->CustomFields->c->sector_tecnico );
    */
        $acc=RNCPHP\Account::fetch($op->CustomFields->Comercial->EjecutivoZona->ID_cuenta);
        $a_ejecutiveInformation[] = array('label' => 'Ejecutiva/o:', 'value' => $acc->LookupName );
        $a_ejecutiveInformation[] = array('label' => 'Teléfono Contacto $:', 'value' => $acc->Phones[0]->Number );
        $a_ejecutiveInformation[] = array('label' => 'Correo Electrónico:', 'value' => $acc->Emails[0]->Address );
        $a_ejecutiveInformation[] = array('label' => 'Ciudad:', 'value' => $op->AssignedToAccount->CustomFields->c->sector_tecnico );
        //Agregando la información Ejecutiva al PDF
        $this->fpdf2->addEjecutiveInformation($a_ejecutiveInformation, $this->xPos, $this->yPos);

        //cargar archivo a PPTO
        $pdfBuffer = $this->fpdf2->Output();

       
     }


     public function SetTicketGuiaState ()
     {
       
       $data = json_decode(file_get_contents('php://input'), true);
        
      
       $array_data = $data;
       
      
       
        if (is_array($array_data) and ($array_data != false))
        {
      
          $indiceDatos = 'datos';
          $indiceAccion = 'accion';
          $indiceUsuario = 'usuario';
    
          if (!array_key_exists($indiceAccion, $array_data) and !array_key_exists($indiceUsuario, $array_data))
          {
              $response = $this->responseError(3);
              $this->sendResponse($response);
          }
    
          if ($array_data[$indiceUsuario] != self::USER)
          {
              $response = $this->responseError(5);
              $this->sendResponse($response);
          }
    
          if ($array_data[$indiceAccion] != "SetTicketGuiaState" )
          {
              $response = $this->responseError(6);
              $this->sendResponse($response);
          }

          $array_data = json_decode(file_get_contents('php://input'), false);
        }
       /* else
        {
          $d='{
            "usuario": "UserDimacofi",
            "accion": "SetTicketGuiaState",
            "datos": {
                "guia": [
                    "1034801"
                ],
                "status": "112"
            }
        }';

          $array_data = json_decode($d, false);
        }
        
        */        
        $error=array();
        $i=0;
       
        
        foreach ($array_data->datos->guia as $key => $value) 
        {
          /*Cambia estado de Cada ticket  buscando la Guia respectiva
          */ 
          //echo $value .'<br>';
    
          //$obj_incident = RNCPHP\Incident::first("CustomFields.c.guide_dispatch='" . $value  . "' and StatusWithType.status.ID not in(2,149,146)" );
          $obj_incident_temp = RNCPHP\Incident::first("CustomFields.c.guide_dispatch='" . $value  . "'" );
          //echo json_encode($incident->ReferenceNumber) .'<br>';
          
       
          $rep=false;
          if($obj_incident_temp->CustomFields->OP->Incident->ReferenceNumber and $obj_incident_temp->CustomFields->OP->Incident->Disposition->ID<>70)
          {
            $obj_incident=$obj_incident_temp->CustomFields->OP->Incident;
            $rep=true;
          }
          else
          {
            $obj_incident=$obj_incident_temp;
          }
         
          if($rep)
          {
            $obj_incident_temp->StatusWithType->Status->ID    = $array_data->datos->status; // Cambio de estado
            $obj_incident_temp->CustomFields->c->external_reference = $array_data->datos->external_reference;
            $obj_incident_temp->Save();
          }

          //$Incident = RNCPHP\Incident::fetch( $value);
          //echo $obj_incident->ReferenceNumber . '-' . $obj_incident->StatusWithType->Status->ID  . '<br>';

          if($obj_incident->ReferenceNumber)
          {
          
            $status_now=$obj_incident->StatusWithType->Status->ID;
            //$this->sendResponse(json_encode($obj_incident->StatusWithType->Status));
            switch($status_now)
            {
              
              case 2:
              case 149:
              case 146:
                $status_old=$status_now;
                $resultado="true";
                $mensage='Ticket ya No se puede Actualizar';
                break;
              case 148:
              case 149: // - Cancelado
              case 161: //- Visita Aceptada
              case 162: //- Visita Técnico Asignado
              case 163: //- Visita Técnico En ruta
              case 164: //- Visita a Re-agendar
              case 165: //- Visita Técnico Trabajando
              case 166: // Visita Finalizada
              case 151: // por despachad canibal
              case 152: // despachado canibal
              case 150: // Ejecución de Presupuesto     
                $status_old=$status_now;
                $resultado="true";
                $mensage='ok';
                break;
              case 118:
                $status_old=$status_now;
                $resultado="true";
                $mensage='Ticket ya asignado a Visita Tecnica';
                break;
              case 112:
                $status_old=$status_now;
                $resultado="true";
                $mensage='Ticket ya se encuentra  entregado';
                
                break;
              case 140:
                $status_old=$status_now;
                $obj_incident->StatusWithType->Status->ID    = $array_data->datos->status; // Cambio de estado
                $obj_incident->CustomFields->c->external_reference = $array_data->datos->external_reference;
                $obj_incident->Save();
                
                
                //$resultado=$this->TicketModel->setIncidentState($obj_incident, $array_data->datos->status);
                $resultado="true";
                $mensage='ok';
                  break;
              case 111:
                $status_old=$status_now;
                $obj_incident->StatusWithType->Status->ID    = $array_data->datos->status; // Cambio de estado
                $obj_incident->CustomFields->c->external_reference = $array_data->datos->external_reference;
                $obj_incident->Save();
                  //$resultado=$this->TicketModel->setIncidentState($obj_incident, $array_data->datos->status);
                $resultado="true";
                $mensage='ok';
                    break;                
              case 195: // Problemas de Entrega
                $status_old=$status_now;
                $obj_incident->StatusWithType->Status->ID    = $array_data->datos->status; // Cambio de estado
                $obj_incident->CustomFields->c->external_reference = $array_data->datos->external_reference;
                $obj_incident->Save();
                $resultado="true";
                $mensage='ok';
              break;
              case 158: // Por Buscar Canival
                // devemos solo cambiar el hijo que esta despachado, pordespachar o lo que sea
                $status_old=$status_now;
                $resultado="true";
                $mensage='ok';
                break;
              default:
                $status_old=$status_now;
                $resultado="false";
                $mensage='Ticket no se puede cambiar  en estado ' . $status_now;

              break;
            }
    
              //$s=$s . '.' . $value . '-' .$array_data->datos->status  . json_encode($obj_incident) ;
              $error[$i]['resultado']=$resultado;
              $error[$i]['ref_no']=$obj_incident->ReferenceNumber;
              $error[$i]['mensage']=$mensage;
              $error[$i]['status_new']=$array_data->datos->status;
              $error[$i]['status_old']=$status_old;
              $obj_incident=null;
              $status_now=null;
          }
          else {
          
            $mensage='No existe Ticket con Guia ' .$value ;
       
              $resultado="false";
              $error[$i]['resultado']=$resultado;
              $error[$i]['ref_no']=$value;
              $error[$i]['mensage']=$mensage;
              $error[$i]['status_new']=0;
              $error[$i]['status_old']=0;

          }
    
          $i++;
    
        }
        $this->sendResponse(json_encode($error));
      }


     public function SetTicketState ()
     {
        $data_post  = $this->getdataPOST();
      //$data_post='ew0KICAidXN1YXJpbyI6ICJVc2VyRGltYWNvZmkiLA0KICAiYWNjaW9uIjogIlNldFRpY2tldFN0YXRlIiwNCiAgImRhdG9zIjogew0KICAgICJ0aWNrZXRzIjogWw0KICAgICAgIjE3MTAyMC0wMDAxMTgiDQogICAgXSwNCiAgICAic3RhdHVzIjogIjExMiINCiAgfQ0KfQ==';
      //$d=base64_decode($data_post);
      //$this->sendResponse($d);
      /*$incident = $this->TicketModel->getObjectTicket('170809-000383');
      self::insertPrivateNote($incident, "[" .json_encode($data_post) . "]");
      */
//$this->sendResponse(json_encode($incident));

      //$json_data  = $this->blowfish->decrypt($d, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
      $array_data = json_decode(utf8_encode($data_post), true);
      //$this->sendResponse($data);
    //$_POST=json_decode($data,true);




/*    $data_post  = $this->getdataPOST();

    $json_data  = $this->blowfish->decrypt($data_post, self::KEY_BLOWFISH, 10, 22, NULL); //desencriptar blowfish
    $array_data = json_decode(utf8_encode($json_data), true);
*/

    if (empty($data_post))
    {
        //$response = $this->responseError(1);
        //$this->sendResponse($response);
      $data_post = '{"usuario":"UserDimacofi","accion":"SetTicketState","datos":{"tickets":["200726-000006","200723-000207","200720-000196","200721-000140","200722-000450","200728-000434","200618-000020","200619-000080","200623-000015","200615-000185","200630-000037","200626-000165","200710-000165","200709-000123","200727-000074","200804-000383","200703-000247","200713-000312","200710-000352","200715-000217","200709-000273","200709-000308","200707-000355","200713-000036","200715-000313","200709-000036","200709-000206","200723-000289","200724-000292","200724-000187","200715-000244","200715-000110","200713-000098","200723-000189","200713-000123","200803-000045","200724-000318","200803-000103","200803-000178","200720-000273","200731-000251","200729-000353","200713-000098","200804-000304","200805-000321","200805-000002","200728-000048","200727-000211","200806-000420","200807-000159","200722-000120","200729-000367","200806-000211","200806-000021","200806-000267","200807-000299","200721-000407","200721-000413","200805-000152","200806-000317","200805-000163","200806-000150","200806-000279","200805-000337","200806-000278","200806-000318","200810-000185","200806-000089","200806-000061","200806-000263","200806-000215","200806-000406","200806-000220","200806-000156","200806-000291","200803-000185","200810-000343","200806-000209","200807-000029","200729-000363","200806-000058","200221-000482","200727-000250","200722-000025","200720-000131","200727-000216","200729-000189","200720-000015","200806-000195","200806-000295","200806-000092","200806-000072","200803-000290","200810-000367","200803-000253","200806-000396","200803-000301","200810-000267","200728-000131","200811-000025","200811-000029","200731-000223","200807-000009","200811-000027","200722-000313","200806-000124","200806-000027","200806-000078","200806-000226","200806-000077","200806-000173","200805-000013","200805-000340","200805-000322","200805-000311","200807-000124","200807-000045","200807-000067","200806-000286","200811-000234","200805-000010","200806-000354","200807-000157","200803-000273","200807-000003","200731-000108","200807-000022","200720-000386","200806-000199","200806-000153","200805-000199","200811-000041","200810-000423","200724-000065","200701-000385","200723-000283","200803-000093","200803-000085","200805-000289","200810-000015","200806-000030","200805-000342","200806-000262","200806-000319","200807-000165","200806-000151","200806-000265","200806-000218","200806-000216","200806-000264","200806-000355","200806-000338","200720-000129","200806-000212","200730-000114","200810-000303","200728-000245","200805-000373","200807-000211","200807-000008","200810-000044","200807-000240","200805-000011","200806-000350","200807-000281","200807-000012","200806-000368","200806-000357","200806-000323","200806-000364","200806-000372","200806-000344","200807-000161","200806-000085","200806-000363","200806-000369","200806-000289","200806-000366","200810-000368","200803-000247","200806-000314","200810-000004","200728-000119","200803-000291","200807-000054","200803-000288","200724-000111","200729-000099","200713-000017","200730-000089","200807-000160","200805-000066","200805-000004","200805-000356","200805-000338","200805-000339","200803-000330","200722-000299","200810-000235","200810-000094","200810-000092","200810-000124","200810-000122","200809-000006","200809-000004","200731-000008","200731-000209","200721-000412","200811-000026","200806-000087","200805-000170","200807-000010"],"status": "112"}}';


      $array_data=json_decode(utf8_encode($data_post), true);
    }

/*$tickets[0]= '171020-000118';
$tickets[1]= '171020-000119';
$tickets[2]= '171020-000120';
$data = json_encode( array ("usuario" => 'UserDimacofi', "accion" => "SetTicketState", "datos" => array("tickets"=> $tickets,"status" =>'112')));
$response = $this->sendResponse($data);
$array_data=json_decode($data);
*/



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

        if ($array_data[$indiceAccion] != "SetTicketState" )
        {
            $response = $this->responseError(6);
            $this->sendResponse($response);
        }



  }
  $this->load->model('custom/ws/TicketModel');   //libreria para tickets


  $error=array();
  $i=0;
  $array_data = json_decode(utf8_encode($data_post), false);
  foreach ($array_data->datos->tickets as $key => $value) {

      /*Cambia estado de Cada ticket*/
      $obj_incident = $this->TicketModel->getObjectTicket($value);

      //$Incident = RNCPHP\Incident::fetch( $value);
      //echo $obj_incident->ReferenceNumber . '-' . $obj_incident->StatusWithType->Status->ID  . '<br>';

      if($obj_incident->ReferenceNumber==$value)
      {
      
        $status_now=$obj_incident->StatusWithType->Status->ID;
        //$this->sendResponse(json_encode($obj_incident->StatusWithType->Status));
        switch($status_now)
        {
          case 2:
          case 148:
          case 149: // - Cancelado
          case 161: //- Visita Aceptada
          case 162: //- Visita Técnico Asignado
          case 163: //- Visita Técnico En ruta
          case 164: //- Visita a Re-agendar
          case 165: //- Visita Técnico Trabajando
          case 166: // Visita Finalizada
          case 151: // por despachad canibal
          case 152: // despachado canibal
          case 150: // Ejecución de Presupuesto
          
                $status_old=$status_now;
                $resultado="true";
                $mensage='ok';
              break;
            case 118:
                $status_old=$status_now;
                $resultado="true";
                $mensage='Ticket ya asignado a Visita Tecnica';
              break;
            case 112:
                $status_old=$status_now;
                $resultado="true";
                $mensage='Ticket ya se encuentra  entregado';
                break;
            case 140:
              $status_old=$status_now;
              $obj_incident->StatusWithType->Status->ID    = $array_data->datos->status; // Cambio de estado
              $obj_incident->Save();
              //$resultado=$this->TicketModel->setIncidentState($obj_incident, $array_data->datos->status);
              $resultado="true";
              $mensage='OK';
                break;
            case 111:
              $status_old=$status_now;
              $obj_incident->StatusWithType->Status->ID    = $array_data->datos->status; // Cambio de estado
              $obj_incident->Save();
                //$resultado=$this->TicketModel->setIncidentState($obj_incident, $array_data->datos->status);
              $resultado="true";
              $mensage='OK';
                  break;                
            case 195: // Problemas de Entrega
              $status_old=$status_now;
              $obj_incident->StatusWithType->Status->ID    = $array_data->datos->status; // Cambio de estado
              $obj_incident->Save();
              $resultado="true";
              $mensage='ok';
            break;
            case 158: // Por Buscar Canival
              // devemos solo cambiar el hijo que esta despachado, pordespachar o lo que sea
              $status_old=$status_now;
              $resultado="true";
              $mensage='ok';
              break;
            default:
              $status_old=$status_now;
              $resultado="false";
              $mensage='Ticket no se puede cambiar  en estado ' . $status_now;

            break;
        }

        //$s=$s . '.' . $value . '-' .$array_data->datos->status  . json_encode($obj_incident) ;
        $error[$i]['resultado']=$resultado;
        $error[$i]['ref_no']=$value;
        $error[$i]['mensage']=$mensage;
        $error[$i]['status_new']=$array_data->datos->status;
        $error[$i]['status_old']=$status_old;
        $obj_incident=null;
        $status_now=null;
  }
 else {
    
    $status_old=$status_now;
        $resultado="true";
        $mensage='ok';
        $error[$i]['resultado']=$resultado;
        $error[$i]['ref_no']=$value;
        $error[$i]['mensage']=$mensage;
        $error[$i]['status_new']=112;
        $error[$i]['status_old']=112;
        $obj_incident=null;
        $status_now=null;
  }

      $i++;
  }
   $this->sendResponse(json_encode($error));
     }

     public function CrearIncidenteRetiro()
     {
       $bannerNumber = 0;
       $indiceDatos = 'datos';
       $indiceAccion = 'accion';
       $indiceUsuario = 'usuario';
       $indiceHH='HH';
       $indiceEstado = 'estado';
       $indiceTipoSolicitud = 'tiposolicitud';
       $indiceSubject = 'asunto';
       $indiceOrden_Activacion = 'orden_activacion';
       $indiceLinea_Orden_Activacion = 'linea_orden_activacion';
       $indiceAssignedTo = "AssignedTo";
       $indiceshipping_instructions ="instrucciones";
       $indiceorder_number_om ="order_number_om";

       $indiceArreglo=0;
       /*
       $data_post  = $this->getdataPOST();
       $array_data = json_decode(utf8_encode($data_post), true);
     */
       $respuesta= array();
       $listaTicket= array();
       $incidents=array();


       if (empty($_POST))
       {
           //$response = $this->responseError(1);
           //$this->sendResponse($response);

         //$data_post='{"usuario":"UserDimacofi","accion":"CrearIncidenteServicio","datos":[{"HH":"1308550","estado":1,"tiposolicitud":28,"asunto":"Instalacion Maquina Nueva HH 1308550","orden_activacion":"1","linea_orden_activacion":"1-8","AssignedTo":"209","instrucciones":"Valentin Oyarce-+56974786715"}]}';
         //$data_post='{"usuario": "UserDimacofi","accion": "CrearIncidenteServicio","datos":[{"HH": "1552529","estado": 1,"tiposolicitud": 28,"asunto": "Instalacion Maquina Nueva HH 1552529","orden_activacion": "951","linea_orden_activacion": "951-3769","AssignedTo": "209","instrucciones": "Camila Erdlandsen-227291006"}]}';
         $data_post='{
  "usuario": "UserDimacofi",
  "accion": "CrearIncidenteRetiro",
  "datos": [

    {"HH":1992552,"estado":109,"tiposolicitud":29,"asunto":"Retiro HH 1992552","orden_activacion":362000000 ,"linea_orden_activacion":28085,"order_number_om":1220590}




     ]
}';


         //'{"usuario": "UserDimacofi","accion": "CrearIncidenteServicio","datos":[
           //{"HH": "638030","estado": 1,"tiposolicitud": 28,"asunto": "Instalacion Maquina Nueva HH 638030","orden_activacion": "4018","linea_orden_activacion": "6.1","AssignedTo": "209","instrucciones": "Luis Molina-lmolinab@copec.cl-65878952-Entrega a: Luis Molina Fono:"}
     //  ] }';

         $array_data=json_decode($data_post,true);
       }
       else {

         $data_post  = $this->getdataPOST();
         $array_data=json_decode($data_post,true);
       }

       if (empty($data_post))
       {
           $response = $this->responseError(1);
           $this->sendResponse("Error ->" . $data_post);
       }

       if (is_array($array_data) and ($array_data != false))
       {
         if (!array_key_exists($indiceAccion, $array_data) and !array_key_exists($indiceUsuario, $array_data) and !array_key_exists( $indiceDatos, $array_data))
            {
              $response = $this->responseError(3);
              $this->sendResponse($response);
            }

            if ($array_data['usuario'] != self::USER)
            {
              $response = $this->responseError(5);
              $this->sendResponse($response);
            }

            if ($array_data['accion'] != 'CrearIncidenteRetiro' )
            {
              $response = $this->responseError(6);
              $this->sendResponse($response );
            }

            if (!is_array($array_data['datos']) )
            {
              $response = $this->responseError(3);
              $this->sendResponse($response );
            }

       try
       {




           foreach ($array_data[$indiceDatos]  as $key => $value)
             {


               $hh[$indiceArreglo] =  $value[$indiceHH];
               $orden_activacion[$indiceArreglo]=$value[$indiceOrden_Activacion] . '|' .  $value[$indiceLinea_Orden_Activacion] ;

               if(!array_key_exists($indiceDatos, $array_data))
               {
                 $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
                 $this->sendResponse($this->error);
                 return;
               }

               if(!empty($value['instrucciones']))
               {
                 $record = explode("-",$value['instrucciones'] );

                 //$res = RNCPHP\ROQL::query("SELECT ID FROM Contact where  login='" . $value['contacto']['email'] . "'  ORDER BY ID DESC LIMIT 1 "  )->next();
                   $res = RNCPHP\ROQL::query("SELECT ID FROM Contact where  login='" . $record[1] . "'  ORDER BY ID DESC LIMIT 1 "  )->next();


                   $contactObj = $res->next();

                   if($contactObj['ID'])
                   {
                      $contact =  RNCPHP\Contact::fetch($contactObj['ID']);


                        $person = explode(" ",$record[0] );


                        if(strlen($person[0])>80)
                        {
                          $first=substr($person[0], 0, 80);
                        }
                        else {
                          $first=$person[0];
                        }
                        $contact->Name->First =$first;

                        if(strlen($person[1])>80)
                        {
                          $last=substr($person[1], 0, 80);
                        }
                        else {
                          $last=$person[1];
                        }
                        $contact->Name->Last = $last;
                        $contact->save();

                }
                     else {

                       $n=strpos ($record[0], '@');
                       $a=strpos ($record[1], '@');
                       $p=strpos ($record[1], '.',$a);
                       $e=strpos ($record[1], ' ');
                       $cc=strpos ($record[1], '@.');


                         if($a>0 && $p>0 && $n=='' && $e=='' && $cc=='')
                         {
                           $contact = new RNCPHP\Contact();
                           $contact->Login = $record[1];
                           $person = explode(" ",$record[0] );
                           $contact->Name = new RNCPHP\PersonName();

                           if(strlen($person[0])>80)
                           {
                             $first=substr($person[0], 0, 80);
                           }
                           else {
                             $first=$person[0];
                           }
                           $contact->Name->First =$first;

                           if(strlen($person[1])>80)
                           {
                             $last=substr($person[1], 0, 80);
                           }
                           else {
                             $last=$person[1];
                           }
                           $contact->Name->Last = $last;

                                 //add email addresses
                           $contact->Emails = new RNCPHP\EmailArray();
                           $contact->Emails[0] = new RNCPHP\Email();
                           $contact->Emails[0]->AddressType=new RNCPHP\NamedIDOptList();
                           $contact->Emails[0]->AddressType->LookupName = "Correo electrónico - Principal";
                           if(strlen($record[1])>80)
                           {
                             $email=substr($record[1], 0, 80);
                           }
                           else {
                             $email=$record[1];
                           }
                           $contact->Emails[0]->Address = $email;


                           $i = 0;
                           if($record[2])
                           {
                             $contact->Phones = new RNCPHP\PhoneArray();
                             $contact->Phones[$i] = new RNCPHP\Phone();
                             $contact->Phones[$i]->PhoneType = new RNCPHP\NamedIDOptList();
                             $contact->Phones[$i]->PhoneType->LookupName = 'Teléfono de oficina';

                             if(strlen($record[2])>40)
                             {
                               $phone=substr($record[2], 0, 40);
                             }
                             else {
                               $phone=$record[2];
                             }
                             $contact->Phones[$i]->Number =  $phone;
                           }

                         $i++;
                           $contact->save();
                         }
                         else {
                           $contact =  RNCPHP\Contact::fetch(61293);
                         }
                      }

              }
           else {
             $contact =  RNCPHP\Contact::fetch(61293);
           }


           $array_obj = RNCPHP\Incident::find(" CustomFields.c.orden_activacion =  '"    .  $orden_activacion[$indiceArreglo]  .  "' and CustomFields.c.id_hh ='"  .  $hh[$indiceArreglo] . "'"   );

         //$this->sendResponse("'CustomFields.c.orden_activacion =  '"    .  $orden_activacion[$indiceArreglo]  .  "' and CustomFields.c.id_hh ='"  .  $hh[$indiceArreglo] . "'"  );
           if($array_obj[0]->ReferenceNumber<>'')
           {
             // si existe crea un elemento con los datps devueltos
             $listaTicket[$indiceArreglo]['ref_num']=$array_obj[0]->ReferenceNumber;
             $listaTicket[$indiceArreglo]['orden_activacion']=$orden_activacion[$indiceArreglo];
             //$listaTicket[$indiceArreglo]['linea_orden_activacion']=$linea_orden_activacion[$indiceArreglo] ;
             $listaTicket[$indiceArreglo]['HH']=$hh[$indiceArreglo];
             $listaTicket[$indiceArreglo]['message']='ok';
             $listaTicket[$indiceArreglo]['estado']='true';

             //$asset = RNCPHP\Asset::find( "asset_id = ". $array_obj[0]->asset_id );
             $res='';
           /*
             $contadores = RNCPHP\DOS\Contador::find("incident = "  . $array_obj[0]->ID  );
             $indicecontador=0;
             $cont=array();
             foreach ($contadores  as $key => $value2)
             {
               $cont[$indicecontador]['ID']   =  $value2->ID;
               $cont[$indicecontador]['Valor']   = $value2->Valor;
               $cont[$indicecontador]['Tipo']=$value2->TipoContador->LookupName;
               $indicecontador++;
             }


             $listaTicket[$indiceArreglo]['Contadores'] =$cont;
           */
             $array_obj[0]->PrimaryContact                         = RNCPHP\Contact::fetch($contact->ID);
             $array_obj[0]->Save();


           }
           else
           {
                 // si el  el ticket no existe se crea

                 $listaTicket[$indiceArreglo]['ref_num']='';
                 $listaTicket[$indiceArreglo]['orden_activacion']='';
                 $listaTicket[$indiceArreglo]['message']='';
                 $listaTicket[$indiceArreglo]['estado']='';
                 $listaTicket[$indiceArreglo]['HH']='';




                 //RNCPHP\ConnectAPI::commit();
                 $incidents[$indiceArreglo]                                         = new RNCPHP\Incident();
                 $incidents[$indiceArreglo]->Subject                                = $value[$indiceSubject] ;
                 $incidents[$indiceArreglo]->Disposition                            = RNCPHP\ServiceDisposition::fetch($value[$indiceTipoSolicitud]);
                 $incidents[$indiceArreglo]->PrimaryContact                         = RNCPHP\Contact::fetch($contact->ID); //RNCPHP\Contact::fetch(48170); //SC/SC/
                 $incidents[$indiceArreglo]->AssignedTo->Account                    = RNCPHP\Account::fetch(209);

                 $incidents[$indiceArreglo]->StatusWithType->Status->ID             = $value[$indiceEstado];
                 $incidents[$indiceArreglo]->CustomFields->c->shipping_instructions = $value[$indiceshipping_instructions];

                 $incidents[$indiceArreglo]->CustomFields->c->nota_pedido           = $value[$indiceOrden_Activacion];

                 $incidents[$indiceArreglo]->CustomFields->c->orden_activacion           = $value[$indiceOrden_Activacion] . '|'.  $value[$indiceLinea_Orden_Activacion];
                 $incidents[$indiceArreglo]->CustomFields->c->id_hh                 = $value[$indiceHH];
                 $incidents[$indiceArreglo]->CustomFields->c->order_number_om      = $value[$indiceorder_number_om];
                 $incidents[$indiceArreglo]->CustomFields->c->requiere_taller       = false;
                 //$incidents[$indiceArreglo]->CustomFields->c->seguimiento_tecnico->LookupName='Visita Técnico Asignado';
                 //$incidents[$indiceArreglo]->CustomFields->c->seguimiento_tecnico->ID=24;
                 $incidents[$indiceArreglo]->Save();




                 try
                 {
                   //obtiene valor de HH
                   $id_hh = $incidents[$indiceArreglo]->CustomFields->c->id_hh; //168665
                   $array_post     = array('usuario' => 'appmind',
                               'accion' => 'info_hh',
                               'datos'=> array('id_hh'=> $id_hh)
                               );
                   $json_data_post = json_encode($array_post);
                   //$json_data_post = $this->Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
                   $json_data_post = $this->blowfish->encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
                   $json_data_post = base64_encode($json_data_post);
                   $postArray = array ('data' => $json_data_post);

                   $result = $this::requestPost($this->URL_GET_HH, $postArray);


                   if ($result != false) {
                   $arr_json = json_decode($result, true);


                   if ($arr_json != false)
                   {
                     if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json)) ))
                     {
                       $respuesta  = base64_decode($arr_json['respuesta']);



                       switch ($arr_json['resultado'])
                       {
                         case "true":

                           $json_hh = $this->blowfish->decrypt($respuesta, "D3t1H6q0p6V7z8", 10, 22, NULL);

                           $array_hh_data = json_decode(utf8_encode($json_hh),true);
                           if (!is_array($array_hh_data))
                           {
                             $message = "ERROR: Estructura JSON encriptado No valida ".PHP_EOL;
                             $message .= "JSON: ".$json_hh;
                             $bannerNumber = 3;
                             break;
                           }


                           $array_hh_data = $array_hh_data['respuesta'];


                           $array_tecnico = $array_hh_data['Tecnico'];
                           $indiceIdTecnico = 'ID_IBS';
/*
                           if (array_key_exists($indiceIdTecnico, $array_tecnico) and $array_tecnico[$indiceIdTecnico] == "-1" and !empty($array_tecnico[$indiceIdTecnico]))
                           {
                               $message = "No se pudo ingresar el técnico, puesto que por WS viene vacio";
                               $listaTicket[$indiceArreglo]['message']=$message;
                               $listaTicket[$indiceArreglo]['estado']='false';
                               break;
                           }
*/
                           //debe asignarse a Waldo y entestado Retiro en Ruta.
                         /*  $array_Account_obj                    = RNCPHP\Account::fetch(209);
                           if (is_object( $array_Account_obj)){

                               $incidents[$indiceArreglo]->AssignedTo->Account = $array_Account_obj; //agregar técnico
                           }
                           else
                           {
                               $message = "Técnico enviado por ws no se encuentra en Oracle RightNow";
                               $listaTicket[$indiceArreglo]['message']=$message;
                               $listaTicket[$indiceArreglo]['estado']='false';
                               break;
                           }
                           */


                           $incidents[$indiceArreglo]->CustomFields->c->marca_hh  = $array_hh_data['Marca'];
                           $incidents[$indiceArreglo]->CustomFields->c->modelo_hh = $array_hh_data['Modelo'];
                           $incidents[$indiceArreglo]->CustomFields->c->convenio  = (int)  $array_hh_data['Convenio'];
                           $incidents[$indiceArreglo]->CustomFields->c->tipo_contrato  = $array_hh_data['TipoContrato'];
                           $incidents[$indiceArreglo]->CustomFields->c->sla_hh    = $array_hh_data['SLA'];
                           $incidents[$indiceArreglo]->CustomFields->c->sla_hh_rsn    = $array_hh_data['RSN'];
                           $incidents[$indiceArreglo]->CustomFields->c->serie_maq  = $array_hh_data['Serie'];
                           $incidents[$indiceArreglo]->CustomFields->c->numero_delfos  = $array_hh_data['delfos'];
                           $array_hh_direccion_id =  $array_hh_data['Direccion'];
                           $incidents[$indiceArreglo]->CustomFields->c->cliente_bloqueado =(int) $array_hh_direccion_id['Bloqueado'];
                           $incidents[$indiceArreglo]->CustomFields->c->soporte_telefonico=0;
                           $id_ebs_direccion = $array_hh_direccion_id['ID_direccion'];

                           $array_Direccion_obj = RNCPHP\DOS\Direccion::find('d_id = '. $id_ebs_direccion);
                           if (is_array($array_Direccion_obj) and is_object($array_Direccion_obj[0]))
                           {
                             $incidents[$indiceArreglo]->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
                             $incidents[$indiceArreglo]->StatusWithType->Status->ID   =  $value[$indiceEstado];
                           }
                           $array_hh_contadores =  $array_hh_data['Contadores'];

                           $incidents[$indiceArreglo]->Save();

                           $asset = RNCPHP\Asset::first( "SerialNumber = '".$incidents[$indiceArreglo]->CustomFields->c->id_hh."'");
                           if (empty($asset)) {
                             $asset = new RNCPHP\Asset;
                             //$asset->Name = $incident->CustomFields->c->id_hh."-".$ncident->CustomFields->c->marca_hh."-".$ncident->CustomFields->c->modelo_hh;
                             $nameHH = $incidents[$indiceArreglo]->CustomFields->c->id_hh."-".$incidents[$indiceArreglo]->CustomFields->c->marca_hh."-".$incidents[$indiceArreglo]->CustomFields->c->modelo_hh;
                             $asset->Name = substr($nameHH, 0, 80);

                             $asset->Contact = $incidents[$indiceArreglo]->PrimaryContact;
                             //$asset->Organization = $incident->Organization;
                             $asset->Product = 2;
                             $asset->SerialNumber = $incidents[$indiceArreglo]->CustomFields->c->id_hh;
                             $asset->Save(RNCPHP\RNObject::SuppressAll);
                           }
                           $asset->CustomFields->DOS->Direccion =  $incidents[$indiceArreglo]->CustomFields->DOS->Direccion;
                           $incidents[$indiceArreglo]->Asset = $asset;


                           $incidents[$indiceArreglo]->Save();
                           //Contadores


                           foreach ($array_hh_contadores as $counter)
                           {
                             $count_id    = $counter['ID'];
                             $count_tipo  = $counter['Tipo'];
                             $count_valor = $counter['Valor'];
                             $contador               = new RNCPHP\DOS\Contador();
                             $contador->ContadorID   = $count_id;
                             $contador->Valor        = $count_valor;
                             $contador->Incident     = $incidents[$indiceArreglo];
                             $contador->TipoContador = RNCPHP\DOS\TipoContador::fetch($counter['Tipo']);
                             $contador->Asset        = $incident->Asset;

                             $contador->Save();



                           }


                           $listaTicket[$indiceArreglo]['ref_num']= $incidents[$indiceArreglo]->ReferenceNumber;
                           $listaTicket[$indiceArreglo]['orden_activacion']=$value[$indiceOrden_Activacion];
                           $listaTicket[$indiceArreglo]['linea_orden_activacion']=$value[$indiceLinea_Orden_Activacion];
                           $listaTicket[$indiceArreglo]['order_number_om']=$value[$indiceorder_number_om];

                           $listaTicket[$indiceArreglo]['message']='ok';
                           $listaTicket[$indiceArreglo]['estado']='true';
                           $listaTicket[$indiceArreglo]['HH']=$incidents[$indiceArreglo]->CustomFields->c->id_hh;
                           //$listaTicket[$indiceArreglo]['Contadores'] =$array_hh_data['Contadores'];

                           RNCPHP\ConnectAPI::commit();

                           break;
                         case False:
                           $listaTicket[$indiceArreglo]['ref_num']='';
                           $listaTicket[$indiceArreglo]['orden_activacion']=$value[$indiceOrden_Activacion];
                           $listaTicket[$indiceArreglo]['linea_orden_activacion']=$linea_orden_activacion[$indiceArreglo] ;
                           $listaTicket[$indiceArreglo]['message']="ERROR: Servicio responde con fallo ".PHP_EOL;
                           $message ="ERROR: Servicio responde con fallo ".PHP_EOL;
                           $listaTicket[$indiceArreglo]['estado']='false';
                           $bannerNumber = 3;
                           RNCPHP\ConnectAPI::rollback();
                           break;
                         default:
                           $listaTicket[$indiceArreglo]['ref_num']='';
                           $listaTicket[$indiceArreglo]['orden_activacion']=$value[$indiceOrden_Activacion];
                           $listaTicket[$indiceArreglo]['linea_orden_activacion']=$linea_orden_activacion[$indiceArreglo] ;
                           $listaTicket[$indiceArreglo]['message']="ERROR: Respuesta fallida ".PHP_EOL;
                           $listaTicket[$indiceArreglo]['rest']=$arr_json['resultado'];
                           $listaTicket[$indiceArreglo]['resp']=$respuesta;
                           $listaTicket[$indiceArreglo]['resl']=$result;
                           $listaTicket[$indiceArreglo]['estado']='false';
                           $message ="ERROR: Respuesta fallida ".PHP_EOL;
                           $bannerNumber = 3;
                           RNCPHP\ConnectAPI::rollback();
                           break;
                       }
                     }
                     else {
                       $message = "ERROR: Estructura JSON No valida ". PHP_EOL;
                       $bannerNumber = 3;
                       $listaTicket[$indiceArreglo]['message']=$message;
                       $listaTicket[$indiceArreglo]['estado']='false';
                       RNCPHP\ConnectAPI::rollback();
                     }

                   }
                   else
                   {
                     $message = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
                     $bannerNumber = 3;
                     $respuestaApi['resultado']='false';
                     $respuestaApi['respuesta']['glosa']=$message;
                     $listaTicket[$indiceArreglo]['message']=$message;
                     $listaTicket[$indiceArreglo]['estado']='false';
                     RNCPHP\ConnectAPI::rollback();
                   }
                 }
                 else {
                   $message = "ERROR: ".$this::getResponseError();
                   $bannerNumber = 3;
                   $respuestaApi['resultado']='false';
                   $respuestaApi['respuesta']['glosa']=$message;
                   $listaTicket[$indiceArreglo]['message']=$message;
                   RNCPHP\ConnectAPI::rollback();
                 }

                 //self::insertPrivateNote($incidents[$indiceArreglo], $message);
                 //self::insertBanner($incidents[$indiceArreglo], $bannerNumber);



               }
               catch (Exception $e)
               {
                  $message = "Error ".$e->getMessage();
                  $respuestaApi['resultado']='false';
                  $respuestaApi['respuesta']['glosa']=$message;
                  $listaTicket[$indiceArreglo]['message']=$message;
                  //self::insertPrivateNote($incident, $message );
               }


             $indiceArreglo++;
             }
           }
             $respuestaApi['respuesta']['tickets']=$listaTicket;
             $respuestaApi['resultado']='true';
             $respuestaApi['respuesta']['glosa']='ok';
             $this->sendResponse(json_encode($respuestaApi));



       }
       catch ( RNCPHP\ConnectAPIError $err )
       {
         $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
         $respuestaApi['resultado']='false';
         $respuestaApi['respuesta']['glosa']=$this->error;
         RNCPHP\ConnectAPI::rollback();
         $this->sendResponse(json_encode($respuestaApi));
         return;
       }
     } else {
     //RNCPHP\ConnectAPI::rollbak();
     $respuestaApi['resultado']='false';
     $respuestaApi['respuesta']['glosa']=$this->error;
     $this->sendResponse($this->responseError(2));
     }
     }


     public function getLastOTfile()
     {


       //$this->sendResponse($ZZZ);



       //$this->sendResponse(json_encode($str));

      if (empty($_POST))
       {
        $POST='{"usuario":"UserDimacofi","accion":"getLastOTfile","datos":{"PTTO":"7644"}}';
        $array_data=json_decode($POST,true);
        //$oportunidad = RNCPHP\Opportunity::fetch( 7025);
        //$texto=$texto . '--' . $value['PTTO'];

        //$this->sendResponse(json_encode($oportunidad->CustomFields->OP->IncidentService->FileAttachments));
       }
       else {
         $data_post  = $this->getdataPOST();
         $array_data=json_decode($data_post,true);
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

                   if ($array_data[$indiceAccion] != 'getLastOTfile' )
                   {
                           $response = $this->responseError(6);
                           $this->sendResponse($response);
                   }
         }
         //$this->sendResponse(json_encode($array_data[$indiceDatos]['TICKET']));

                  initConnectAPI("consultas","Consultas1232018");


                $oportunidad = RNCPHP\Opportunity::fetch( $array_data[$indiceDatos]['PTTO']);

                if($oportunidad->CustomFields->OP->IncidentService->FileAttachments)
                {
                  foreach ($oportunidad->CustomFields->OP->IncidentService->FileAttachments as $key => $value) {
                    if( stristr($value->FileName, 'OT'))
                    {
                        $FILE=$value;
                    }
                  }
                //$this->sendResponse(json_encode($FILE));
                if($FILE)
                {
                $str1 = str_replace("//", "/", $FILE->getAdminURL(), $count);
                $str2 = str_replace("https", "http", $FILE->getAdminURL(), $count);
                $ZZZ='<a href="' . $str2 . '">' . $FILE->FileName . '</a>';
                $this->typeFormat='html';
                }
                $respuesta['OT']=$FILE->FileName;
                $respuesta['url']=$str2;
                }
                else {
                  $respuesta['OT']="Sin Archivo";
                  $respuesta['url']="null";
                }
                $this->sendResponse(json_encode($respuesta));
     }

     public function getAllfile()
     {


       //$this->sendResponse($ZZZ);

     $FileList= array();
     $index=0;
       //$this->sendResponse(json_encode($str));

      if (empty($_POST))
       {
        $POST='{"usuario":"UserDimacofi","accion":"getAllfile","datos":{"PTTO":"7644"}}';
        $array_data=json_decode($POST,true);
        //$oportunidad = RNCPHP\Opportunity::fetch( 7025);
        //$texto=$texto . '--' . $value['PTTO'];

        //$this->sendResponse(json_encode($oportunidad->CustomFields->OP->IncidentService->FileAttachments));
       }
       else {
         $data_post  = $this->getdataPOST();
         $array_data=json_decode($data_post,true);
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

                   if ($array_data[$indiceAccion] != 'getAllfile' )
                   {
                           $response = $this->responseError(6);
                           $this->sendResponse($response);
                   }
         }
         //$this->sendResponse(json_encode($array_data[$indiceDatos]['TICKET']));

                  initConnectAPI("soporte","Dtic.2020");


                $oportunidad = RNCPHP\Opportunity::fetch( $array_data[$indiceDatos]['PTTO']);

                if($oportunidad->CustomFields->OP->IncidentService->FileAttachments)
                {
                  foreach ($oportunidad->CustomFields->OP->IncidentService->FileAttachments as $key => $value) {

                        $FILE=$value;
                        //$this->sendResponse(json_encode($FILE));
                        if($FILE)
                        {
                          $str1 = str_replace("//", "/", $FILE->getAdminURL(), $count);
                          $str2 = str_replace("https", "http", $FILE->getAdminURL(), $count);
                          $ZZZ='<a href="' . $str2 . '">' . $FILE->FileName . '</a>';
                          $this->typeFormat='html';
                          $FileList[$index]["File"]=$FILE->FileName;
                          $FileList[$index]["url"]=$str2;

                        }
                        else {
                          $FileList[$index]['File']="Sin Archivo";
                          $FileList[$index]['url']="null";
                        }
                        $index++;
                  }


                 }
                 if($oportunidad->FileAttachments)
                 {
                   foreach ($oportunidad->FileAttachments as $key => $value) {

                         $FILE=$value;
                         //$this->sendResponse(json_encode($FILE));
                         if($FILE)
                         {
                           $str1 = str_replace("//", "/", $FILE->getAdminURL(), $count);
                           $str2 = str_replace("https", "http", $FILE->getAdminURL(), $count);
                           $ZZZ='<a href="' . $str2 . '">' . $FILE->FileName . '</a>';
                           $this->typeFormat='html';
                           $FileList[$index]["File"]=$FILE->FileName;
                           $FileList[$index]["url"]=$str2;

                         }
                         else {
                           $FileList[$index]['File']="Sin Archivo";
                           $FileList[$index]['url']="null";
                         }
                         $index++;
                   }

                  $this->sendResponse(json_encode($FileList));
                }
   }
     public function RegulaCuentas()
     {
     $ma="";
        //$respuesta = RNCPHP\Contact::find("Login is null and CustomFields.c.blocked is null  and Emails.Address is not null and Emails.Address not like '%sincorreo%' ");
        $respuesta = RNCPHP\Contact::find("Login is null and CustomFields.c.blocked is null  and Emails.Address is not null and Emails.Address not like '%sincorreo%' ");
        //$respuesta = RNCPHP\Contact::fetch(34);
        //$this->sendResponse(json_encode($respuesta->Emails[0]->Address));
        for($i=0;$i<2000;$i++)
        {

          //$this->sendResponse(json_encode($respuesta[$i]->Source->Parents[0]->ID). ' - ' . $respuesta[$i]->Source->Parents[0]->LookupName);
          //$this->sendResponse($i . ' - ' . json_encode($respuesta[$i]) . ' - ' . json_encode($record[0]) . ' - '. json_encode($respuesta[$i]->Login . ' - ' . json_encode($respuesta[$i]->CustomFields->c->blocked)));

            if($respuesta[$i]->Emails[0])
            {

              $org_id = RNCPHP\Incident::first("PrimaryContact.Contact.ID =" . $respuesta[$i]->ID);
              //$respuesta = RNCPHP\Incident::fetch("180502-000248");

                      //$this->sendResponse(json_encode($respuesta));
            // $this->sendResponse(json_encode($org_id));

     //$this->sendResponse(json_encode($respuesta[$i]));
              $x=$respuesta[$i]->Emails[0]->Address;
              $record = explode(".invalid",$x );
              //$this->sendResponse($i . ' - ' . json_encode($record[0]) . ' - '. json_encode($respuesta[$i]->Login . ' - ' . json_encode($respuesta[$i]->CustomFields->c->blocked)));

              $record = explode(".invalid",$x );
              $respuesta[$i]->Login=$record[0];


              //$respuesta[$i]->CustomFields->c->blocked=false;
              $respuesta[$i]->Organization=$org_id->CustomFields->DOS->Direccion->Organization;
              $respuesta[$i]->Save();

              $ma=$ma . "[" . $record[0] . "]";

              //$this->sendResponse($i . ' - ' . json_encode($record[0]) . ' - '. json_encode($respuesta[$i]->Login . ' - ' . json_encode($respuesta[$i]->CustomFields->c->blocked)));
            }

        }

     $this->sendResponse(json_encode($ma));
     }


     public function LeeOportunidadesCerradas()
       {

   /*
           if (empty($_POST))
           {
               $response = $this->responseError(1);
               $this->sendResponse($response);
           }
   */
   //        if (is_array($array_data) and ($array_data != false))
           if (1)
           {
   /*
               $indiceAccion = 'accion';
               $indiceUsuario = 'usuario';

               if (!array_key_exists($indiceAccion, $array_data) and !array_key_exists($indiceUsuario, $array_data))
               {
                   $response = $this->responseError(3);
                   $this->sendResponse($response);
               }

               if ($array_data[$indiceUsuario] != self::USER)
               {
                   $response = $this->responseError(5);
                   $this->sendResponse($response);
               }
   */


         $this->load->model('custom/ws/TicketModel');   //libreria para tickets

   // Looop Start

         $report_id = 101456 ;
         $filter_value= 1;
         $nro_referencias="";

         $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
         $status_filter->Name = 'resource_id';
         $status_filter->Values = array( $filter_value  );
         $filters = new RNCPHP\AnalyticsReportSearchFilterArray;
         $filters[] = $status_filter;
         $ar= RNCPHP\AnalyticsReport::fetch( $report_id);
         $arr= $ar->run();

         // Inicio - ALTERNATIVA ENCRIPTADA
         //$this->sendResponse(json_encode($arr->count()));

         for ( $i = $arr->count(); $i--; )
         {
            $row = $arr->next();

            $oportunidad = RNCPHP\Opportunity::fetch($row['op_id']);
$oportunidad->CustomFields->c->id_venus=1;
$oportunidad->save(RNCPHP\RNObject::SuppressAll);
//$this->sendResponse(json_encode($row['op_id']));
//$this->sendResponse(json_encode($oportunidad->CustomFields->OP->IncidentService->ReferenceNumber));
//$this->sendResponse(json_encode($oportunidad->CustomFields->c->id_venus));
//$oportunidad->CustomFields->c->id_venus=$oportunidad->CustomFields->OP->IncidentService->ReferenceNumber;
//$oportunidad->save(RNCPHP\RNObject::SuppressAll);
//$this->sendResponse(json_encode($oportunidad->CustomFields->c->id_venus . '-' . $oportunidad->CustomFields->OP->IncidentService->ReferenceNumber));
         }

       }
    }

    public function leePrediccion()
    {
      $predict=false;
      $items='';
      $nolista='';

      $mensaje='';

     
      $withRestriction = false;
      $incident2 = RNCPHP\Incident::fetch(698711);
      $incident3 = RNCPHP\Incident::find("Incident.CustomFields.OP.Incident=698711");
      

      //$a_items = RNCPHP\OP\OrderItems::find("Incident.ID = 700038");
      echo json_encode($incident->ID) .'<br>';
      return;
      $i=0;
      $a_items = RNCPHP\OP\OrderItems::find("Incident.ID = " . $incident->ID );
      try
         {
           
          $mensaje=$mensaje .' INICIO ';
          echo json_encode($a_items) .'<br>';
           foreach ($a_items as $item)
           {
            $mensaje=$mensaje .'- ' . $item->Product->ID;
             $idProduct       = $item->Product->ID;
             $typeFlow        = $incident->Disposition->ID;
             $objRestriction  = RNCPHP\OP\Restriction::first("TypeFlow.ID = {$typeFlow} and Product.ID = {$idProduct} ");
             if (is_object($objRestriction))
             {
              $mensaje=$mensaje .'- RESTRICCION';
               $withRestriction = true;
               
             }


 
             $idTecnico = intval($incident->AssignedTo ->Account->ID);

             $estalista=false;
             /*
               34:    // CARDENAS PALOMO, GUILLERMO
               108100:// GONZALEZ ESPINOZA, JORGE NICOLAS
               97:    // NEIRA MONTESINOS, SERGIO
               104023:// SOTO, HOLDRIN ENRIQUE
               175:   // Rodrigo Torrens Clerc
               88666: // DONQUIS MARTINEZ, JOHAN MANUEL
               70:    // GONZALEZ PAREDES, HECTOR JOSE
               44:    // CHAPARRO ROJAS, JAIME ENRIQUE
               148:   // LARA PASTEN, OSCAR IGNACIO
                */
                $prediccion_flag=false;
                switch ($idTecnico) {
                  case 34:    // CARDENAS PALOMO, GUILLERMO
                  case 108100:// GONZALEZ ESPINOZA, JORGE NICOLAS
                  case 97:    // NEIRA MONTESINOS, SERGIO
                  case 104023:// SOTO, HOLDRIN ENRIQUE
                  case 175:   // Rodrigo Torrens Clerc
                  case 88666: // DONQUIS MARTINEZ, JOHAN MANUEL
                  case 70:    // GONZALEZ PAREDES, HECTOR JOSE
                  case 44:    // CHAPARRO ROJAS, JAIME ENRIQUE
                  case 148:   // LARA PASTEN, OSCAR IGNACIO
          
                    $prediccion_flag=true;
                    break;
                default:
                    $prediccion_flag=false;
                  break;
                }
             if($prediccion_flag)
             {
              $mensaje=$mensaje .'- PREDICCION';
                $Prediccion=json_decode($incident->CustomFields->OP->Incident->CustomFields->c->predictiondata);


                 $delfos=$item->Product->CodeItem;
             
            
                foreach($Prediccion->reporte->d as $struct) {
                  
                    if ($delfos == $struct->C) {
                        $estalista=true;
                        $mensaje=$mensaje .'-ENCONTRADO [' . $struct->C . '-' .$delfos   . ' U=' . $struct->U .']';
                        if($struct->U > 0 &&  $struct->U < 85)
                        {
                          $predict=true;
                          
                          $items= $items . ' ' . $item->Product->CodeItem; //  ( deberia ser delfos)
                          break;
                        }
                        
                    }
                }
            

             if(!$estalista)
             {
                 $withRestriction = true;
                 $nolista=$nolista . ' ' . $item->Product->CodeItem;
                 $estalista=false;
                 $noesta=true;
             }
            }
            $i++;
           }

             //$incident->CustomFields->c->send_to_om = false;
             //$incident->StatusWithType->Status->ID  = 175;
             $incident->CustomFields->c->flag_restriction_ok = true;
             if($noesta===true)
             {
                $incident->CustomFields->c->flag_restriction_ok = false;
                self::insertPrivateNote($incident, "Solicitud se encuentra en Supervisión. Tienes ítems que no han sido solicitados para este modelo (" .$nolista  .")");
                $incident->Save(RNCPHP\RNObject::SuppressExternalEvents);
             }
             else
             {
               self::insertPrivateNote($incident, "No se encontraron Items con Problemas." );
             }

             if($predict===true)
             {
                $incident->CustomFields->c->flag_restriction_ok = false;
                self::insertPrivateNote($incident, "Solicitud se encuentra en Supervisión. Tienes ítems que tienen bajo porcentaje de uso  (" .$items  .")");
                $incident->Save(RNCPHP\RNObject::SuppressExternalEvents);

             }
             else
             {
               self::insertPrivateNote($incident, "No se encontraron Items que tengan bajo porcentaje["  . $items  ."]");
             }
             if ($withRestriction === true)
             {
                    $incident->CustomFields->c->flag_restriction_ok = false;
                    self::insertPrivateNote($incident, "El Caso ha sido detenido en Supervisión puesto que se han escogido items en restricción.....");
                    $incident->Save(RNCPHP\RNObject::SuppressExternalEvents);
             }
             else
             {
               self::insertPrivateNote($incident, "No se encontraron Items en Restricción");
             }
             self::insertPrivateNote($incident, $mensaje=$mensaje .'-' . $struct->C . '-' .$delfos .' <->' . $i);
        }
         catch (RNCPHP\ConnectAPIError $err){
             $incident->CustomFields->c->flag_restriction_ok = false;
             self::insertPrivateNote($incident, $err->getMessage());
         }
        //$this->sendResponse($mensaje);
    }

    public function leeTicketRepuesto()
    {
        //$r=RNCPHP\OP\Product::fetch(25);
        //$this->sendResponse(json_encode($r->UnitCostPrice));


        $lastSuppliersColorIncident = RNCPHP\Incident::first("CustomFields.c.id_hh = '1853542' and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont1_hh != 0 order by ClosedTime DESC");
      
        $this->sendResponse(json_encode($lastSuppliersColorIncident->CustomFields->c->cont1_hh));


        $obj_response                               = $this->CI->Supplier->getLastCounter($hh);


        $incident = RNCPHP\Incident::fetch('200121-000612');
        
        $ObjAsset =  RNCPHP\Asset::first("SerialNumber = " . $incident->CustomFields->c->id_hh);
        
        if (!empty($ObjAsset))
        {
            $incident->Asset=$ObjAsset;
            $incident->save(RNCPHP\RNObject::SuppressAll);
        }

      

        $incident->save(RNCPHP\RNObject::SuppressAll);
        $this->sendResponse(json_encode($ObjAsset));
       


        $UMBRAL    = RNCPHP\MessageBase::find('CUSTOM_MSG_PREDICTION_THRESHOLD');
        $this->sendResponse(json_encode($UMBRAL->ID));
        $incidentR = RNCPHP\Incident::fetch('190806-000014');
        $this->sendResponse(json_encode($incidentR->CustomFields->c));
        //$incidentR[0]->StatusWithType->Status->ID= 178; //Enviado
        //$incidentR[0]->Save();
        //$env=$this->EnviromentConditions->getObjectEnviromentConditions($incident->CustomFields->OP->Incident->ID);
        //$this->sendResponse(json_encode($incident->CustomFields->OP->Incident->CustomFields->c->commercial_approval ));
        //$array_Direccion_obj                           = RNCPHP\DOS\Direccion::find('d_id =  4590950');
        //$this->sendResponse(json_encode($array_Direccion_obj ));
      //$categoria= RNCPHP\OP\CategoryItem::fetch(40);
      //$this->sendResponse(json_encode($categoria->ID));
        $id_ebs_direccion                              = 142421;
        $array_Direccion_obj                           = RNCPHP\DOS\Direccion::find('d_id = '. $id_ebs_direccion);
        
        
        $this->sendResponse(json_encode($array_Direccion_obj[0] ));
        //$this->sendResponse(json_encode($incidentR->CustomFields->DOS->Direccion->start_am->ID  ) . '-');
        //$this->sendResponse(json_encode($incidentR->CustomFields->DOS->Direccion->start_am->LookupName  ). '-');
        //$this->sendResponse(json_encode($incidentR->CustomFields->DOS->Direccion->start_am->CreatedTime  ). '-');
        //$this->sendResponse(json_encode($incidentR->CustomFields->DOS->Direccion->start_am->UpdatedTime  ). '-');
        //$this->sendResponse(json_encode($incidentR->CustomFields->DOS->Direccion->start_am->DisplayOrder  ). '-');
        //$this->sendResponse(json_encode($incidentR->CustomFields->DOS->Direccion->start_am->Name  ). '-');

        //$env=RNCPHP\OP\EnviromentConditions::find("incident ={$incident->CustomFields->OP->Incident->ID}");
        //$env=$this->EnviromentConditions->getObjectEnviromentConditions($incident->CustomFields->OP->Incident->ID);
        //$this->sendResponse(json_encode($env[0]));
    }

    public function TestCPMBuscarHH()
    {

      try
        {

      $this->load->library('ConnectUrl');
      $incident = RNCPHP\Incident::fetch(579959);
        $bannerNumber = 0;
        $id_hh = $incident->CustomFields->c->id_hh; //168665


        self::insertPrivateNote($incident, "ID de HH ->".$id_hh);

        $array_post     = array('usuario' => 'appmind',
        'accion' => 'info_hh',
        'datos'=> array('id_hh'=> $id_hh)
        );

        $json_data_post = json_encode($array_post);
        self::insertPrivateNote($incident, "ID de json_encode ->".$json_data_post);

        $json_data_post = base64_encode($json_data_post);

        self::insertPrivateNote($incident, "ID de base64_encode ->".$json_data_post);
        
        $incident->Save(RNCPHP\RNObject::SuppressAll);

        $postArray = array ('data' => $json_data_post);

        $result = $this::requestPost($this->URL_GET_HH, $postArray);

        self::insertPrivateNote($incident, "ID de requestPost ->".$result);

        $incident->Save(RNCPHP\RNObject::SuppressAll);

            if ($result != false) {
                $arr_json = json_decode($result, true);
                if ($arr_json != false)
                {
                    if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json)) ))
                    {
                        $respuesta  = base64_decode($arr_json['respuesta']);

                        switch ($arr_json['resultado'])
                        {
                            case "true":
                                self::insertPrivateNote($incident, "TODO BIEN");
                                $incident->Save(RNCPHP\RNObject::SuppressAll);
                                
                                $json_hh = $respuesta;

                                self::insertPrivateNote($incident, "TODO BIEN ->" . $json_hh);
                                $incident->Save(RNCPHP\RNObject::SuppressAll);
                                $array_hh_data = json_decode(utf8_encode($json_hh),true);

                                

                                if (!is_array($array_hh_data))
                                {
                                    $message = "ERROR: Estructura JSON encriptado No valida ".PHP_EOL;
                                    $message .= "JSON: ".$json_hh;
                                    $bannerNumber = 3;
                                    break;
                                }

                                $array_hh_data         = $array_hh_data['respuesta'];
                                $hh_marca              = $array_hh_data['Marca'];
                                $hh_sla                = $array_hh_data['SLA'];
                                $sla_hh_rsn            = $array_hh_data['RSN'];

                                $hh_modelo             = $array_hh_data['Modelo'];
                                $hh_convenio           = $array_hh_data['Convenio'];

                                $array_hh_contadores   = $array_hh_data['Contadores'];
                                $array_hh_direccion_id = $array_hh_data['Direccion'];
                                $hh_tipo_contrato      = $array_hh_data['TipoContrato'];
                                $serie_hh              = $array_hh_data['Serie'];
                                $numero_delfos         = $array_hh_data['delfos'];
                                $item_type             = $array_hh_data['Tipo_Articulo'];
                                $Rut                   = $array_hh_data['Rut'];

                                //$inc_hh = new HHIncidentModel($incident);
                                //$hh_result = $inc_hh->saveInfoHH($hh_marca, $hh_modelo, $hh_sla,$sla_hh_rsn, $hh_convenio, $hh_tipo_contrato ,$array_hh_contadores, $array_hh_direccion_id,$serie_hh,$numero_delfos);
                                $hh_result  = self::saveInfoHH($incident, $hh_marca, $hh_modelo, $hh_sla,$sla_hh_rsn, $hh_convenio, $hh_tipo_contrato ,$array_hh_contadores, $array_hh_direccion_id,$serie_hh,$numero_delfos,$Rut,$this);

                                

                                if ($hh_result == false)
                                {
                                    $message =  "ERROR: en guardado de HH ";
                                    $bannerNumber = 3;
                                }
                                else {
                                    $bannerNumber = 1;
                                    $message = "Los datos de HH han sido ingresados correctamente";
                                }

                                break;
                            case False:
                                $message = "ERROR: Servicio responde con fallo ".PHP_EOL;
                                $bannerNumber = 3;
                                break;
                            default:
                                $message = "ERROR: Respuesta fallida ".PHP_EOL;
                                // $json_hh = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                                // $array_hh_data = json_decode(utf8_encode($json_hh),true);
                                // $message .= "JSON: ". $array_hh_data['msg'];
                                $message .= "JSON: " . $result;
                                $bannerNumber = 3;
                                break;
                        }


                    }
                    else {

                        $message = "ERROR: Estructura JSON No valida ". PHP_EOL;
                        $message .= "JSON: ". $result;
                        $bannerNumber = 3;
                    }
                }
                else
                {
                    $message = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
                    $bannerNumber = 3;
                }
            }
            else {
                $message = "ERROR: ".ConnectUrl::getResponseError();
                $bannerNumber = 3;
            }
            self::insertPrivateNote($incident, $message);
            self::insertBanner($incident, $bannerNumber);

      

        }
        catch (Exception $e)
        {
             $message = "Error ".$e->getMessage();
             self::insertPrivateNote($incident, $message);
        }
    }


    static function saveInfoHH($incident, $marca, $modelo, $sla,$sla_rsn, $bool_convenio, $hh_tipo_contrato, $array_contadores, $array_direcciones,$serie_hh,$numero_delfos,$Rut,$este)
    {
        try
        {
          
            RNCPHP\ConnectAPI::commit();

            self::insertPrivateNote($incident, "saveInfoHH ->" . $marca);
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            $incident->CustomFields->c->marca_hh           = $marca;
            $incident->CustomFields->c->modelo_hh          = $modelo;
            $incident->CustomFields->c->convenio           = (int) $bool_convenio;
            $incident->CustomFields->c->tipo_contrato      = $hh_tipo_contrato;
            $incident->CustomFields->c->sla_hh             = $sla;
            $incident->CustomFields->c->sla_hh_rsn         = $sla_rsn;
            $incident->CustomFields->c->cliente_bloqueado  = (int) $array_direcciones['Bloqueado'];
            $incident->CustomFields->c->serie_maq          = $serie_hh;
            $incident->CustomFields->c->numero_delfos      = $numero_delfos;
            $incident->CustomFields->c->item_type          = $item_type;
            $incident->CustomFields->c->order_number_om_ref= $Rut;
            self::insertPrivateNote($incident, $Rut);

            self::insertPrivateNote($incident, "saveInfoHH ->" . $array_direcciones['ID_direccion']);

            $incident->Save(RNCPHP\RNObject::SuppressAll);


            $id_ebs_direccion                              = $array_direcciones['ID_direccion'];
            $array_Direccion_obj                           = RNCPHP\DOS\Direccion::find('d_id = '. $id_ebs_direccion);
            
            self::insertPrivateNote($incident, "array_Direccion_obj ->" . json_encode($array_Direccion_obj));

            $incident->Save(RNCPHP\RNObject::SuppressAll);
            if (is_array($array_Direccion_obj) and is_object($array_Direccion_obj[0]))
                $incident->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
            $incident->save(RNCPHP\RNObject::SuppressAll);

            $este->sendResponse("DATA -->". json_encode($array_Direccion_obj));


           
            return  $incident->ID;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $error = "Codigo : ".$err->getCode()." ".$err->getMessage();
            self::insertPrivateNote($incident, $error);
            RNCPHP\ConnectAPI::rollback();
            return false;
        }
    }

    public function GeneraFacturaAR()
   {
    $this->load->library('ConnectUrl');
    $j=0;
    $i=0;
    $Numero_Factura='';
    $Numero_Presupuesto='';
    $LineNumber=0;
    $Correlativo=1;
    //if ($cycle !== 0) return;
    $ID=13754    ;
    $opportunity = RNCPHP\Opportunity::fetch($ID);
    //$this->sendResponse(json_encode($opportunity));
    self::insertPrivateNoteO($opportunity, "oportunidad " . $opportunity->ID );
    $opportunity->Save(RNCPHP\RNObject::SuppressAll);
    try
    {
      

      $a_orderItems          = RNCPHP\OP\OrderItems::find("Opportunity.ID ='{$opportunity->ID}'");
      foreach ($a_orderItems as $item)
      {
        if ($item->Enabled === false )
          continue;
        $a_tmp_result['Inventory_item_id']  = $item->Product->InventoryItemId;
        $a_tmp_result['line_id']            = $item->ID;
        $a_tmp_result['ordered_quantity']   = $item->QuantitySelected;
        $a_tmp_result['line_selling_price'] = $item->ConfirmedSellPrice;

        //Valor Unitario con descuento.
        if (!empty($item->DiscountSellPrice))
          $amount_discount = ($item->DiscountSellPrice * $item->UnitTempSellPrice)/100;
        else
          $amount_discount = 0;
        $a_tmp_result['unit_selling_price'] = $item->UnitTempSellPrice - $amount_discount;

        //$a_list_items[] = $a_tmp_result;
        // Calcula el numero de facturas a imprimir

        $a_list_items[$j][$i] = $a_tmp_result;
        $i++;
        if(!($i%15))
        {
            $j++;
            $i=0;

        }
        $LineNumber++;
      }


        self::insertPrivateNoteO($opportunity, "json enviado:Lineas" . $LineNumber . "-Facturas" . count($a_list_items)  );
        $numero_facturas=count($a_list_items);
      /*  for($x=0;$x<count($a_list_items);$x++)
        {
          self::insertPrivateNote($opportunity, "json enviado: " . json_encode( $a_list_items[$x])  );
        }
*/
      for($x=0;$x<$numero_facturas ;$x++)
      {
        $z= $a_list_items[$x];

        if ($numero_facturas>=1)
        {
          $Numero_Presupuesto=$opportunity->ID . '-' . $Correlativo  . '/' . $numero_facturas;
          $Correlativo++;
        }
        else {
          $Numero_Presupuesto=$opportunity->ID ;
        }

      $array_post    = array("usuario" => "Integer",
                              "accion"  => "setInvoiceAR",
                              "order_detail" => array(
                                "ref_id_ppto_rn"         => $Numero_Presupuesto,
                                "client_rut"             => $opportunity->Organization->CustomFields->c->rut,
                                "deliver_to_customer_id" => $opportunity->Organization->CustomFields->c->id_cliente,
                                "Bill_to"                => $opportunity->CustomFields->OP->Direccion->d_id,
                                "hh"                     => $opportunity->CustomFields->c->id_hh,
                                "Batch_source_id"        => null,
                                "Cust_trx_type_id"       => null,
                                "Terms_id"               => null,
                                "Customer_id"            => null,
                                "salesreps_id"           => $opportunity->CustomFields->Comercial->Ejecutivo->sales_rep_id,
                                //"salesreps_id"           => $opportunity->CustomFields->Comercial->Vendedor->CustomFields->c->resource_id, //Cambiar por relacion de objeto
                                "Purchase_order_ref"     => $opportunity->CustomFields->c->oc_number,
                                "list_products"          => $z
                              ));

      $json_data_post = json_encode($array_post);

      self::insertPrivateNoteO($opportunity, "json enviado: ". $json_data_post);

     // $json_data_post = Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
      $json_data_post = base64_encode($json_data_post);
      $postArray      = array ('data' => $json_data_post);
      $result         = $this::requestPost($this->URL_GET_HH , $postArray);

      self::insertPrivateNoteO($opportunity, "json devuelto: ". $result);
      $opportunity->save();
      if ($result != false) // No hubo error en el servicio de actualización de precios
      {
        $arr_json  = json_decode($result, true);


        //No fallo el JSON Decode
        if ($arr_json != false)
        {
          if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json)) ))
          {
            $respuesta  = base64_decode($arr_json['respuesta']);
            switch ($arr_json['resultado'])
            {
              case true:
                  //$json_resp       = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                  $json_resp       = $respuesta;
                  $array_data_resp = json_decode(utf8_encode($json_resp), true); //Transformar a array

                  if (!is_array($array_data_resp))
                  {
                    $message = "ERROR, problema al decofificar Respuesta ";
                    break;
                  }

                  if (!empty($array_data_resp['id_invoice_ar']))
                  {
                    if($x==0){
                        $Numero_Factura=$array_data_resp['id_invoice_ar'];
                    }
                    else {
                        $Numero_Factura=$Numero_Factura . '-' . $array_data_resp['id_invoice_ar'];
                    }

                    self::insertPrivateNoteO($opportunity, $Numero_Factura);
                    $opportunity->CustomFields->c->id_ar        =$Numero_Factura;

                    $opportunity->StatusWithType->Status->ID    = 11; // Estado Cerrado - Facturado
                    $opportunity->CustomFields->c->flag_send_ar = false;
                    $message                                    = "Factura ingresada con exito !!";

                    break;
                  }
                  else
                  {
                    //Codigo para exponer los arreglos
                    $a_products = $array_data_resp;
                    $var = print_r($a_products, true);
                    $message = "ID de Factura viene vacio OBJ: ".$var;
                    break;
                  }


                  break;
              case false:
                  //$message = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                  $message = $respuesta;
                  break;
              default:
                  $message = "ERROR: Estructura JSON No valida R".PHP_EOL;
                  $message .= "JSON: ". $result;
                  break;
            }
            self::insertPrivateNoteO($opportunity, $message);
          }
          else
          {
            $message = "ERROR: Estructura JSON No valida, no se encontro 'resultado' ni 'respuesta' ". PHP_EOL;
            self::insertPrivateNoteO($opportunity, $message);
            $opportunity->save();
          }
        }
        else
        {
          $message = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
          self::insertPrivateNoteO($opportunity, $message);
          $opportunity->save();
        }
      }
      else
      {
          $message = "ERROR: ". "json devuelto: ". $result;
          self::insertPrivateNoteO($opportunity, $message);

          $opportunity->save();
      }

    }
  }
    catch (RNCPHP\ConnectAPIError $err )
    {
       $message = "Error ".$e->getMessage();
       self::insertPrivateNoteO($opportunity, "Error Query: ".$message);
       self::insertBannerO($opportunity, $bannerNumber);
       $opportunity->save();
    }


}

static function insertPrivateNoteO($opportunity, $textoNP)
{
  try
  {
     $opportunity->Notes                 = new RNCPHP\NoteArray();
     $opportunity->Notes[0]              = new RNCPHP\Note();
     $opportunity->Notes[0]->Text        = $textoNP;
     $opportunity->Save(RNCPHP\RNObject::SuppressAll);
  }
  catch ( RNCPHP\ConnectAPIError $err )
  {
      return false;
  }
}

static function insertBannerO($opportunity, $typeBanner, $texto = '')
{
    if (!is_numeric($typeBanner) and $typeBanner > 3 and $typeBanner < 0)
        $typeBanner = 1;

    $texto = '';
    if ($typeBanner == 3)
        $texto = "Error respuesta OM";

    $opportunity->Banner->Text           = $texto;
    $opportunity->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
    $opportunity->Save(RNCPHP\RNObject::SuppressAll);
}
public function getTEST()
{

  

  $jsondata =  file_get_contents('php://input');
  $data=json_decode($jsondata);
  
  $this->sendResponse( $data->ref_num);


}

public function updateIncidentRetiro()
{

  $jsondata =  file_get_contents('php://input');
  $data=json_decode($jsondata);


  $mensajes = array();
  $ref_num = $data->ref_num;
  $rma = $data->rma;
  $mensaje = $data->mensaje;
  $retiro = $data->retiro;
  $status = $status->status;
  

  $incident = RNCPHP\Incident::fetch( $ref_num);
  
  if($incident)
  {

   
    self::insertPrivateNote($incident, "Actualiza Retiro " . $ref_num .' - RMA[ ' . $rma  .']' . '- mensaje[ ' . $mensaje  .']'.' - Retiro[ ' . $retiro  .']');

    if($rma)
    {
      if( $incident->Disposition->ID==123 and $incident->StatusWithType->Status->ID!=2)
      {
          // se actualiza con numero de OA de Reempazo de Equipo
          $incident->CustomFields->c->order_number_om_ref = $retiro . '-' .$rma;    // Actualiza la OA 
          $incident->StatusWithType->Status->ID=2;
      }
      else
      {
          // reemplazo prestamo
          $incident->CustomFields->c->orden_activacion = $oa;    // Actualiza la OA 
          
          if(substr($incident->CustomFields->c->hh_replacement, 0, 1)=='T')
          {
            self::insertPrivateNote($incident, 'Retiro ya fue ejecutado');
          }
          else
          {
            $incident->CustomFields->c->hh_replacement='T'.$incident->CustomFields->c->hh_replacement.'-' .$rma ;
            $incident->Save(RNCPHP\RNObject::SuppressAll);
          }
      }
      $incident->Save(RNCPHP\RNObject::SuppressAll);
      
    }

    
  }
  /*
  if (empty($_POST))
   {
     $response = $this->responseError(1);

     $this->sendResponse($response);
   }

   if(empty($_POST['data']))
   {

     $response = $this->responseError(2);
     $this->sendResponse($response);
   }
  */

  $response=array('ref_num' => $data->ref_num,'rma' => $rma);

   $this->sendResponse(json_encode($response));

}

}