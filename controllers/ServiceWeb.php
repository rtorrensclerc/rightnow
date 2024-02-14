<?php

namespace Custom\Controllers;
use RightNow\Connect\v1_2 as RNCPHP;

class ServiceWeb extends \RightNow\Controllers\Base
{
  protected $typeFormat = 'json';
  function __construct()
  {
      parent::__construct();
      $this->load->model('custom/ws/EnviromentConditions'); //Modelo para acceder a tecnicos y modelo
      $this->load->model('custom/ConnectUrl');
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
          $response = json_encode($response); //desencriptar blowfish
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


  private function InsertaContadoresHH($incident,$cont1_hh, $cont2_hh,   $id_BN_type,$id_Color_type)
  {

    $cfg2 = RNCPHP\Configuration::fetch( CUSTOM_CFG_WSO2_URL );

    //echo $cfg2->Value . "---->";

    $counter_bn =0;
    $counter_color =0;
    $counter_a3_bn =0;
    $counter_a3_color =0;
    $counter_b4_bn =0;
    $counter_b4_color =0;
    $counter_dupl =0;
    $counter_metro =0;


      
      switch($id_BN_type)
      {
        case 1:
          $counter_bn=$cont1_hh;
         break;
        
        case 13:
          $counter_dupl=$cont1_hh;
         break;
        case 16:
          $counter_metro=$cont1_hh;
        break;
      }

      switch($id_BN_type)
      {
        case 2:
          $counter_color=$cont2_hh;
        break;
        
        case 14:
          $counter_metro=$cont2_hh;
          break ;
      }
     
      $json_data='{
        "hh": 184961,
        "Comments": "",
        "Purchase_order": "",
        "user_id": "",
        "counters": {
            "counter_bn": ' .$counter_bn .',
            "counter_color": ' .$counter_color .',
            "counter_a3_bn": ' .$counter_a3_bn .',
            "counter_a3_color": ' .$counter_a3_color .',
            "counter_b4_bn": ' .$counter_b4_bn .',
            "counter_b4_color": ' .$counter_b4_color .',
            "counter_dupl": ' .$counter_dupl .',
            "counter_metro": ' .$counter_metro .'
        }
    }';

    $json_request=json_encode($json_request);
    $response=$this->ConnectUrl->requestCURLJsonRaw($cfg2->Value .'/mb2/insertCounterRN', $json_data); 



    $data=json_decode($response);
    return $data->status;


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

  public function updateIncidentStateAr ()
  {
    $EnviromentConditions = (object) array('Notes'=>'');
    $CI = get_instance();
    $username = $CI->session->getSessionData("username");
    $password = $CI->session->getSessionData("password");
    initConnectAPI($username,$password);
    if (empty($_POST))
    {
      $data='
      {"order_detail":{
       "action": 1,
       "ref_no": "221012-00035
      }}';
    }
    else
    {
      $data = trim($_POST['data']);
    }
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
    $status_prev          = $orderDetail['status_prev'];
    $cont1_hh             = $orderDetail['cont1_hh'];
    $cont2_hh             = $orderDetail['cont2_hh'];+
    $seguimiento_tecnico  = $orderDetail['seguimiento_tecnico'];
    $motivo_solucion      = $orderDetail['motivo_solucion'];
    $equipo_detenido      = $orderDetail['equipo_detenido'];
    $diagnostico          = $orderDetail['diagnostico'];
    $nota                 = $orderDetail['nota'];
    $tipo_contrato        = $orderDetail['tipo_contrato'];
    $disposition          = $orderDetail['disposition'];
    $VisitNumber          = $orderDetail['VisitNumber'];
    $ArFlow               = $orderDetail['ArFlow'];
    $motivoar             = $orderDetail['motivoar'];

    $obj_incident             = RNCPHP\incident::fetch($refNo);

    $validar=0;

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
   
    $hh = RNCPHP\Asset::first("SerialNumber = " . $obj_incident->CustomFields->c->id_hh  );
    $contadores = RNCPHP\DOS\Contador::find("Asset.ID = {$hh->ID}");
 
    $id_BN=0;
    $id_Color=0;
    $id_BN_type=-1;
    $id_Color_type=-1;
    //echo $seguimiento_tecnico . "-" . $status;
    
    

    //297 Reagenda asistencia remota

    $Env=$this->EnviromentConditions->getObjectEnviromentConditions($obj_incident->ID);
    if(!$Env)
    {
      logMessage("ServiceWeb EnviromentConditions 1" );

      $EnviromentConditions->Incident= $obj_incident->ID;
    }
    else {
      logMessage("ServiceWeb EnviromentConditions 2" );

        $EnviromentConditions=$Env;
    }
    //echo json_encode($Env);
   
    // status es el seguimiento_tecnico actual
    //  seguimiento_tecnico es el seguimiento_tecnico  nuevo


    if($status==297)
    {

    }
    else
    {
      if($seguimiento_tecnico==299)
      {
        //echo "[" .json_encode($obj_incident->CustomFields->c->ar_flow) .']';
        if($ArFlow<>null and $ArFlow<>"0" )
        {
          $obj_incident->CustomFields->c->ar_flow->ID =$ArFlow;
        }
        else
        {
          $validar=23;
        }

        if($ArFlow==283 and $motivoar==300)
        {
          $validar=25;
        }
        else
        {
          //echo "--->" .$motivoar;
          $obj_incident->CustomFields->c->ar_reason->ID =$motivoar;
        }
        
        if($orderDetail['Description']=='' || $orderDetail['Description']=='-descripcion-')
        {
            if($ArFlow<>283)
            {
              $validar=7;
            }
            $EnviromentConditions->Description=  '-descripcion-';
        }
        else
        {
          $EnviromentConditions->Description=  $orderDetail['Description'];
        }
        
        if($orderDetail['Solution']=='' || $orderDetail['Solution']=='-solucion-')
        {
          if($ArFlow<>283)
          {
            $validar=8;
          }
          $EnviromentConditions->Solution= '-solucion-';
        }
        else
        {
          $EnviromentConditions->Solution= $orderDetail['Solution'];
        }
      }
    }
      if($seguimiento_tecnico==299  &&  ($status==299 || $status==19 || $status==43))
      {
      
        $obj_incident->CustomFields->c->motivo_solucion->ID =$motivo_solucion;
        if($motivo_solucion==95 )
        {
          if($ArFlow<>283)
            {
            $validar=20;
            }
        }
        $obj_incident->CustomFields->c->diagnostico->ID=$diagnostico;


        //echo "<<<<" . $ArFlow . ">>>>>>>>>>>";
        if($diagnostico==94 )
        {
            if($ArFlow<>283)
            {
              $validar=22;
            }
            
            
        }
    
        
        foreach($contadores as $key => $value)
        {
            if( ($value->TipoContador->ID==1 or $value->TipoContador->ID==13 or $value->TipoContador->ID==16 ) and $value->Valor )
          {
        
            $copia_BN=$value;
            $id_BN=$value->ContadorID;
            $id_BN_type=$value->TipoContador->ID;
      
          }

          if( ($value->TipoContador->ID==2 or $value->TipoContador->ID==14 ) and $value->Valor )
          {
            $copia_Color=$value;
            $id_Color=$value->ContadorID;
            $id_Color_type=$value->TipoContador->ID;
          }
        }

      }

      if($seguimiento_tecnico==299  &&  ($status==299 || $status==19 || $status==43))
      {
     
        //echo $seguimiento_tecnico .' ' . $motivo_solucion;
    
        $obj_incident->CustomFields->c->motivo_solucion->ID =$motivo_solucion;
        if($motivo_solucion==106 )
        {
          if($ArFlow<>283)
          {
            $validar=20;
          }
        }
        $obj_incident->CustomFields->c->diagnostico->ID=$diagnostico;
        if($diagnostico==107 )
        {
          if($ArFlow<>283)
          {
            $validar=22;
          }
        }

     
        foreach($contadores as $key => $value)
        {
            if( ($value->TipoContador->ID==1 or $value->TipoContador->ID==13 or $value->TipoContador->ID==16 ) and $value->Valor )
          {
        
            $copia_BN=$value;
            $id_BN=$value->ContadorID;
            $id_BN_type=$value->TipoContador->ID;
      
          }

          if( ($value->TipoContador->ID==2 or $value->TipoContador->ID==14 ) and $value->Valor )
          {
            $copia_Color=$value;
            $id_Color=$value->ContadorID;
            $id_Color_type=$value->TipoContador->ID;
          }
        }

        $id=$this->EnviromentConditions->updateEnviromentConditions($EnviromentConditions,$nota);
     }
  



    $hh = RNCPHP\Asset::first("SerialNumber = " . $obj_incident->CustomFields->c->id_hh  );
    $contadores = RNCPHP\DOS\Contador::find("Asset.ID = {$hh->ID}");
 
    $id_BN=0;
    $id_Color=0;
    $id_BN_type=-1;
    $id_Color_type=-1;
    //echo $seguimiento_tecnico . "-" . $status;
    
    
   

    

    $action=1;
    $array_obj = RNCPHP\Incident::find(" AssignedTo.Account.ID=" . $obj_incident->AssignedTo->Account->ID .    " and StatusWithType.status.ID in(163,165) and Incident.ID  not in ("  .  $obj_incident->ID . ")"   );




    if($array_obj && $status<>98)
    {
      $action=4;
    }
    else
    {
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
      $mensaje[23]="Flujo AR sin valor.";
      $mensaje[24]="No ha ingresado Datos.";
      $mensaje[25]="Falta Motivo Sin Solucuion AR.";

      if($validar==0)
      {
        if($ArFlow<>null and $ArFlow<>"0" )
        {
          $obj_incident->CustomFields->c->ar_flow->ID =$ArFlow;
          if($ArFlow==280)
          {
            $status = 19;
          } 
          if($ArFlow==282)
          {
            $status = 43;
          } 
          if($ArFlow==281)
          {
            $status = 43;
          } 
          if($ArFlow==283)
          {
            $status = 15;
          } 
          if($ArFlow==284)
          {
            $status= 17;
          } 

        }
        

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
            $resultado=$this->InsertaContadoresHH($obj_incident, $cont1_hh, $cont2_hh,   $id_BN_type,$id_Color_type);
            //$this->sendResponse($responseEncode);
          //$obj_incident->CustomFields->c->shipping_instructions="3";
          //$obj_incident->save();
          //echo "Prueba " . $validar . ' A-' . $action . ' B-' . $cont1_hh. ' C-' .  $cont2_hh. ' D-' .    $id_BN_type. ' E-[' . $id_Color_type .']';
          self::insertPrivateNote($obj_incident,'Guardando Contadores - ['. json_encode($resultado) .']');
      
            /* Aqui deberiamos guardar contadores*/
            
        
            
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
          case  297:  // 
            $obj_incident->StatusWithType->Status->ID =119;  
          break;

        }
        $obj_incident->CustomFields->c->seguimiento_tecnico->ID=$status;
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
   
    
    if($validar>0)
    {
      $Status_Validar=false;
    }
    else
    {
      $Status_Validar=true;
    }



    $obj_incident->Save();
    $msg=$mensaje[$validar];
    $array_response['response'] = array ('status' => $Status_Validar, 'ref_no' => $refNo , 'error' => 6,'cont1_hh' => $cont1_hh ,'cont2_hh' => $cont2_hh,'copia_BN'=>$copia_BN->Valor,'copia_Color'=>$copia_Color->Valor,"id_BN" => $id_BN,"id_Color" => $id_Color, "msg" => $msg, 'select_status'=> $status, 'validar' => $validar,'tipo_contrato'=>$tipo_contrato);
    $responseEncode = json_encode($array_response);
    $this->sendResponse($responseEncode);


  }
  public function updateIncidentState ()
  {

    $EnviromentConditions = (object) array('Notes'=>'');
    $mensajes = array();

   
    $CI = get_instance();
    $username = $CI->session->getSessionData("username");
    $password = $CI->session->getSessionData("password");
    initConnectAPI($username,$password);
    
    if (empty($_POST))
     {
        $response = $this->responseError(1);

       //$this->sendResponse($response);
      // 165 tecnico Trabajando 
       $data='
       {"order_detail":{
        "action": 1,
        "ref_no": "221012-000357",
        "select_status": "18",
        "cont1_hh": "196783",
        "cont2_hh": "",
        "status_prev": "16",
        "shipping_instructions": "",
        "file": null,
        "filedata": null,
        "contents": null,
        "Description": "test",
        "Solution": "test",
        "Incident": null,
        "IpNumber": "-IpNumbe-",
        "Copy": true,
        "Scan": true,
        "Printer": true,
        "Fax": true,
        "Temperture": "OK",
        "IssueCausa": "CV",
        "ElectricalCondition": "NR",
        "EnviromentCondit": "EP",
        "PrintFlow": "SV",
        "username": "",
        "disposition": "25",
        "seguimiento_tecnico": "18",
        "motivo_solucion": "57",
        "equipo_detenido": false,
        "diagnostico": "53",
        "gasto": "0",
        "expend_type": "90",
        "gsto_detail": "Sin Observacion",
        "AlternativeEmails": "-Sin Correos-",
        "nota": "",
        "tipo_contrato": "Convenio",
        "Area": "Sin Valor",
        "CostCenter": "Sin Valor",
        "Reception_Name": "N/A",
        "NoDataMobile": true,
        "VisitNumber": "1",
        "tipo_id": "29",
        "ArFlow": ""
    }}';

       //$data = trim($_POST['data']);
      //echo $data;
     }
     else
     {

     $data = trim($_POST['data']);
     }
    /* if(empty($_POST['data']))
      {
        $response = $this->responseError(2);
        $this->sendResponse($response);
      }
*/
    
      
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
      $status_prev          = $orderDetail['status_prev'];
      $cont1_hh             = $orderDetail['cont1_hh'];
      $cont2_hh             = $orderDetail['cont2_hh'];+
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
      $VisitNumber          = $orderDetail['VisitNumber'];
      $NoDataMobile         = $orderDetail['NoDataMobile'];
      $ArFlow               = $orderDetail['ArFlow'];
    
     $obj_incident             = RNCPHP\incident::fetch($refNo);

     // Agerar validacion de cola  AR

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
      logMessage("ServiceWeb updateIncidentState " . $obj_incident->ID);
    
     $array_response['response'] = array (
            'status' => true,
            'ref_no' => $refNo ,
            'error' => 0,
            'cont1_hh' => $cont1_hh ,
            'cont2_hh' => $cont2_hh,
            'copia_BN'=>1000,
            'copia_Color'=>0,
            'id_BN' => 12,
            'id_Color' => 13,
            'msg' => 'vuelta1',
            'select_status'=> $status,
            'validar' => 12,
            'tipo_contrato'=> $tipo_contrato );

      $validar=0;


      $Env=$this->EnviromentConditions->getObjectEnviromentConditions($obj_incident->ID);

      if(!$Env)
      {
        logMessage("ServiceWeb EnviromentConditions 1" );

        $EnviromentConditions->Incident= $obj_incident->ID;
      }
      else {
        logMessage("ServiceWeb EnviromentConditions 2" );

         $EnviromentConditions=$Env;
      }


      if($seguimiento_tecnico==18)
      {

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

     }
     $EnviromentConditions->NoDataMobile= $orderDetail['NoDataMobile'];
    //  logMessage("EnviromentConditions " . json_encode($EnviromentConditions));
      logMessage("EnviromentConditions " . $status  . ' - ' . $status_prev  . ' - ' . $VisitNumber);
      if($status==16 && ($status_prev==15 || $status_prev==17 ))
      {
        if($VisitNumber=="" or is_null($VisitNumber))
        {
          $VisitNumber=0;
        }
        $VisitNumber++;
        $EnviromentConditions->VisitNumber=$VisitNumber;
        //logMessage("EnviromentConditions " . $status  . ' - ' . $status_prev  . ' - ' . $VisitNumber);
      }
      else {
        $EnviromentConditions->VisitNumber=$VisitNumber;
      }
    
      logMessage("EnviromentConditions " . json_encode($EnviromentConditions));
      $id=$this->EnviromentConditions->updateEnviromentConditions($EnviromentConditions,$nota);
      logMessage("ServiceWeb EnviromentConditions 3" );
      
      
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
      
       $hh = RNCPHP\Asset::first("SerialNumber = " . $obj_incident->CustomFields->c->id_hh  );
       $contadores = RNCPHP\DOS\Contador::find("Asset.ID = {$hh->ID}");
    
       $id_BN=0;
       $id_Color=0;
       $id_BN_type=-1;
       $id_Color_type=-1;
       //echo $seguimiento_tecnico . "-" . $status;
       
      

       if($seguimiento_tecnico==18  &&  ($status==18 || $status==19 || $status==43))
       {
         
    
      
         $obj_incident->CustomFields->c->motivo_solucion->ID =$motivo_solucion;
         if($motivo_solucion==106 )
         {
              $validar=20;
         }
         $obj_incident->CustomFields->c->diagnostico->ID=$diagnostico;
         if($diagnostico==107 )
         {
              $validar=22;
         }

         $BN=0;
         $Color=0;
         date_default_timezone_set('UTC');
         $delta=time()-100*60*60*24;
         $indice_color=0;
         $indice_bn=0;


         foreach($contadores as $key => $value)
         {
          if($value->UpdatedTime-$delta>0)
          {
          
             if( ($value->TipoContador->ID==1 or $value->TipoContador->ID==13 or $value->TipoContador->ID==16 ) and  $value->ID>=$indice_bn )
              {
              
                $copia_BN=$value;
                $BN=$value->Valor;
                $id_BN=$value->ContadorID;
                $id_BN_type=$value->TipoContador->ID;
                $indice_bn=$value->ID;
            
              }

              if( ($value->TipoContador->ID==2 or $value->TipoContador->ID==14 ) and $value->ID>=$indice_color  )
              {
                $copia_Color=$value;
                $Color=$value->Valor;
                $id_Color=$value->ContadorID;
                $id_Color_type=$value->TipoContador->ID;
                $indice_color=$value->ID;
              }
          }
         }

       }

       $action=1;
       $array_obj = RNCPHP\Incident::find(" AssignedTo.Account.ID=" . $obj_incident->AssignedTo->Account->ID .    " and StatusWithType.status.ID in(163,165) and Incident.ID  not in ("  .  $obj_incident->ID . ")"   );
  



      if($array_obj && $status<>98)
      {
        $action=4;
      }
      else
      {
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

     
      /*if($orderDetail['ArFlow']==''  )
      {
           $validar=17;
      }*/

      $mensaje[0]='';
      $mensaje[1]='';
      $mensaje[2]='Contador B/N Menor al Actual R. ' . $copia_BN->Valor  . '-(' . $validar . ')-' . $orderDetail['action']. '-' . $action . '-' . $seguimiento_tecnico . '-' .$copia_BN->Valor .'-' .$cont1_hh .'-' . $BN;
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
      $mensaje[23]="Flujo AR sin valor.";
      $mensaje[24]="No ha subido Contadores.";
      //$array_response['response'] = array ('status' => true, 'ref_no' => $refNo , 'error' => 0,'cont1_hh' => $cont1_hh ,'cont2_hh' => $cont2_hh,'copia_BN'=>$copia_BN->Valor,'copia_Color'=>$copia_Color->Valor,"id_BN" => $id_BN,"id_Color" => $id_Color, 'msg' => $msg, 'select_status'=> $status, 'validar' => $validar,'tipo_contrato'=>$tipo_contrato);
      
     
      $obj_incident->Save();
      
      $msg=$mensaje[$validar];
      //$array_response['response'] = array ('status' => true, 'ref_no' => $refNo , 'error' => 0,'cont1_hh' => $cont1_hh ,'cont2_hh' => $cont2_hh,'copia_BN'=>111,'copia_Color'=>0,"id_BN" => 11,"id_Color" => 12, 'msg' => $msg  , 'select_status'=> $status, 'validar' => $validar,'tipo_contrato'=>$tipo_contrato);
      /*$array_response['response'] = array (
          'status' => true,
          'ref_no' => $refNo ,
          'error' => 0,
          'cont1_hh' => $cont1_hh ,
          'cont2_hh' => $cont2_hh,
          'copia_BN'=>$copia_BN->Valor,
          'copia_Color'=>$copia_Color->Valor,
          'id_BN' => $id_BN,
          'id_Color' => $id_Color,
          'msg' => $msg ,
          'select_status'=> $status,
          'validar' => $validar,
          'tipo_contrato'=>$tipo_contrato
      );
*/
     //$array_response['response'] = array ('status' => true, 'ref_no' => $refNo ,'cont1_hh' => $cont1_hh ,'cont2_hh' => $cont2_hh , 'error' => 0,"id_BN" => $id_BN,"id_Color" => $id_Color, 'msg' => $msg);
     //logMessage("ServiceWeb updateIncidentState " . json_encode($array_response));
     //$responseEncode = json_encode($array_response);
    
     switch ($action) {
       case 1: //Crear

             //Validar que todos los campos esten ingresados

             
             if($validar==0)
             {
               

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
                   $resultado=$this->InsertaContadoresHH($obj_incident, $cont1_hh, $cont2_hh,   $id_BN_type,$id_Color_type);
                    //$this->sendResponse($responseEncode);
                  //$obj_incident->CustomFields->c->shipping_instructions="3";
                  //$obj_incident->save();
                  //echo "Prueba " . $validar . ' A-' . $action . ' B-' . $cont1_hh. ' C-' .  $cont2_hh. ' D-' .    $id_BN_type. ' E-[' . $id_Color_type .']';
                  self::insertPrivateNote($obj_incident,'Guardando Contadores - ['. json_encode($resultado) .']');
              
                   /* Aqui deberiamos guardar contadores*/
                   
                
                   
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
               $obj_incident->CustomFields->c->seguimiento_tecnico->ID=$status;
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
             logMessage("updateIncidentState " . json_encode($array_response));
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
}
