<?php
namespace Custom\Widgets\integer;
use RightNow\Connect\v1_2 as RNCPHP;
class special_detail extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);

        $this->setAjaxHandlers(array(
            'SendRequest_ajax_endpoint' => array(
                'method'      => 'handle_SendRequest_ajax_endpoint',
                'clickstream' => 'SendRequest_ajax_endpoint',
            ),
        ));
    }

    public function getData()
    {
      $this->CI->load->model('custom/ws/TicketModel');
      $this->CI->load->model('custom/ws/EnviromentConditions');
      $result = $this->CI->TicketModel->getIncident($this->data['attrs']['ref_no']);
  
      if ($result == false)
      {
        $this->data['error'] = $this->CI->TicketModel->getLastError();
        // $this->CI->session->setSessionData(array('Request_info' => ''));
        // $this->data['Request_info'] = '';
        $this->CI->session->setFlashData('Request_info', serialize(''));
      }
      else {
  
        try
        {
          $Conditions = $this->CI->EnviromentConditions->getObjectEnviromentConditions($result->order->ID);
        }
        catch (RNCPHP\ConnectAPIError $err )
        {
          $Conditions= null;
        }
  
        $this->data['order']                 = $result->order;
  
        $a_request_values['idStatus']        = $result->order->StatusWithType->Status->ID;
        $a_request_values['idHH']            = $result->CustomFields->c->id_hh;
        $a_request_values['id']              = $result->order->ID;
        $a_request_values['ref_no']          = $result->order->ReferenceNumber;
       
        $result_cnt = $this->CI->TicketModel->getCounters($this->data['attrs']['ref_no']);
 
        /*
        Si Grupo es B/N   sólo se pide 1  TIPO "B/N"
        Si grupo es COLOR  se piden 2   TIPO "B/N"  y "COPIAS COLOR"
        Si grupo es DUPLICADORES se pide 1 TIPO "DUPL"
        Si grupo es FORMATO ANCHO se piden 2  TIPO "FORMATO ANCHO" y "METRO"
        Si no pertenece a ninguno de los anteriores se pide 1
        */
        if ($Conditions->VisitNumber=="")
        {
           $VisitNumber=1;
        }
        else {
          $VisitNumber=$Conditions->VisitNumber;
        }
        $grupo=0;
        
  if($result_cnt->Contadores)
  {
        foreach ($result_cnt->Contadores as $key => $value) {
            switch($value->TipoContador->ID)
            {
              case 1:
  
                $grupo=$grupo|1;
                $this->data['copia_BN']=$value;
               
                break;
              case 2:
                $grupo=$grupo|2;
                $this->data['copia_Color']=$value;
                break;
              case 13:
                $grupo=$grupo|4;
                $this->data['copia_Dupl']=$value;
                break;
              case 16:
                $grupo=$grupo|8;
                $this->data['copia_FAncho']=$value;
              break;
              case 14:
                $grupo=$grupo|16;
                $this->data['copia_Metro']=$value;
              break;
  
            }
        }
        if ($grupo&4)
        {
            $grupo=$grupo&4;
        }
  }
  else {
    $grupo=$grupo|1;
    $this->data['copia_BN']= new RNCPHP\DOS\Contador();
    $this->data['copia_BN']->TipoContador = RNCPHP\DOS\TipoContador::fetch('COPIA B/N');
    $this->data['copia_BN']->Valor=0;
  $grupo=$grupo|2;
    $this->data['copia_Color']= new RNCPHP\DOS\Contador();
    $this->data['copia_Color']->TipoContador = RNCPHP\DOS\TipoContador::fetch('COPIA COLOR');
    $this->data['copia_Color']->Valor=0;
  }
  

  
  $this->data['cont1_hh']          = $result->order->CustomFields->c->cont1_hh;
  $this->data['cont2_hh']          = $result->order->CustomFields->c->cont2_hh;
 
  /*
        $this->data['copia_BN']          = $result_cnt->copia_BN;
        $this->data['copia_Color']          = $result_cnt->copia_Color;
        $this->data['Conditions']=  $Conditions;
        $this->data['Contadores']=  $result_cnt->Contadores;
  */
        $gastos=RNCPHP\OP\Expenses::find('incident= ' . $this->data['order']->ID );
        $this->data['grupo']=$grupo;
        $this->data['Conditions']=  $Conditions;
        $this->data['gastos']=$gastos;
        $this->CI->session->setFlashData('Request_info', serialize($a_request_values));
  
      }

      
  
      //  buscar si esta firmado el docuento  en este ciclo, dependiendo si tubo conexion a Internet
      $CI = get_instance();
      $username = $CI->session->getSessionData("username");
      $password = $CI->session->getSessionData("password");
  
      initConnectAPI($username,$password);
  
      // Si esta firmado permite avanzar
      $this->data['firma']=0;
      // Si esta Con los caontadores Adjuntos permite Avanzar,  a no ser que este con la maquina detenina
      $this->data['Contadores']=0;
      if($this->data["order"]->CustomFields->c->equipo_detenido)
      {
         $this->data['Contadores']=1;
      }
      if($this->data['order']->FileAttachments)
      {
          foreach ($this->data['order']->FileAttachments as $file)
          {
              if ($file->FileName=='OT-' . $this->data['attrs']['ref_no'] .'-'.$VisitNumber .'.pdf' or $file->FileName== 'OT-' . $this->data['attrs']['ref_no'] .'-'.$VisitNumber .'.jpg')
              {
                  $this->data['firma']=1;
              }
  
              if ($file->FileName=='Contadores-' . $this->data['attrs']['ref_no'] .'-'.$VisitNumber .'.pdf' or $file->FileName== 'Contadores-' . $this->data['attrs']['ref_no'] .'-'.$VisitNumber .'.jpg')
              {
                  $this->data['Contadores']=1;
              }
  
          }
      }
      $this->data['firma']=1;
  
      $this->data['Contadores']=1;

      $this->data['test']='hola-' . $result_cnt->copia_BN->Valor;
      return parent::getData();
    }
  
    /**
     * Handles the SendRequest_ajax_endpoint AJAX request
     * @param array $params Get / Post parameters
     */
    function handle_SendRequest_ajax_endpoint($params) {
        // Perform AJAX-handling here...
        // echo response
        $this->CI->load->model('custom/ws/EnviromentConditions');
        $Conditions = (object) array('Notes'=>'');


        $a=json_decode($params['data']);
        $this->data['firma']=1;
        $obj_incident             = RNCPHP\incident::fetch($a->ref_no);
        $obj_incident->CustomFields->c->seguimiento_tecnico->ID=$a->select_status;
        $obj_incident->CustomFields->c->gasto                  =$a->gasto;
        $expend_type                                           =$a->expend_type;
        
        $gsto_detail                                           =$a->gsto_detail;
        /*if(!$a->gasto)
        {
          $obj_incident->CustomFields->c->gasto=0;
       }
        else {
          $obj_incident->CustomFields->c->gasto=$a->gasto;
        }
        if($obj_incident->CustomFields->c->gasto>=0 and $expend_type!=90 )
        {
          $gastos=new  RNCPHP\OP\Expenses();
          $gastos->Incident=$obj_incident->ID;
          $gastos->ExpenseType=$expend_type;
          $gastos->Description=$gsto_detail;
          $gastos->Expenses=$a->gasto;
          $gastos->save();

        }
*/
        //$Enviroment = new RNCPHP\OP\EnviromentConditions();
            //$Enviroment->Notes= $Conditions['Notes'];
    
        $EnviromentConditions = $this->CI->EnviromentConditions->getObjectEnviromentConditions($obj_incident->ID);


        if(!($EnviromentConditions))
        {

            $Conditions->Incident=$obj_incident->ID;
            $EnviromentConditions=$this->CI->EnviromentConditions->createEnviromentConditions($Conditions,"Hola");
        }    
        
        //  Solo cuando sea Trabajando Guardamos los datos especiales
        if($a->select_status==18 && $a->select_status==$a->id_status_prev) 
        {
                //  Contadores
                 if($a->cont1_hh)
                 {
                   $obj_incident->CustomFields->c->cont1_hh=$a->cont1_hh;
                 }
                 
                 if($a->cont2_hh)
                 {

                   $obj_incident->CustomFields->c->cont2_hh=$a->cont2_hh;
                 }

                 $obj_incident->CustomFields->c->equipo_detenido=$a->EquipoDetenido;
                 //$obj_incident->CustomFields->c->motivo_solucion->ID =$a->motivo_solucion;
                 //$obj_incident->CustomFields->c->diagnostico->ID=$a->diagnostico;
                 $obj_incident->CustomFields->c->direccion_incorrecta=$a->direccion_incorrecta;
                 //if ($obj_incident->CustomFields->c->direccion_incorrecta == "1") {
                  
                  $values                    = new \stdClass();
                  $values->Direccion = $a->Direccion ; 
                  $values->Comuna = $a->Comuna ; 
                  $values->Region = $a->Region ; 
                  $values->Nueva_Etiqueta = $a->Nueva_Etiqueta ; 
                  $values->Cambio_Etiqueta = $a->Cambio_Etiqueta ; 
                  $values->Estado_Conexion = $a->Estado_Conexion ; 
                  $values->Estado_General = $a->Estado_General ; 
                  $values->motivo_solucion = $a->motivo_solucion ; 
                  $values->Phone = $a->Phone;
                  $values->Reception_Name2 = $a->Reception_Name2;
                  $values->AlternativeEmails2 = $a->AlternativeEmails2;
                  $values->Phone2 = $a->Phone2;
                  $values->Alerta_Insumo=$a->Alerta_Insumo;
                  $values->IgualSerie=$a->IgualSerie;
                  $values->ClickScanner = $a->ClickScanner;

                  $values->UsersNumber = $a->UsersNumber;
                  $result=json_encode($values);
               

                  $obj_incident->CustomFields->c->predictiondata = $result;


                 $EnviromentConditions->NoDataMobile= $a->sin_cobertura;
                 //$EnviromentConditions->Description=  $a->Description;
                 //$EnviromentConditions->Solution= $a->Solution;                
                

                 $EnviromentConditions->Temperture= $a->Temperture;
                 $EnviromentConditions->IssueCausa= $a->IssueCausa;
                 $EnviromentConditions->ElectricalCondition= $a->ElectricalCondition;
                 $EnviromentConditions->EnviromentCondit= $a->EnviromentCondit;
                 $EnviromentConditions->Reception_Name= $a->Reception_Name;
                 $EnviromentConditions->AlternativeEmails=$a->AlternativeEmails;
                 
                 $EnviromentConditions->IpNumber= $a->IpNumber;
                 $EnviromentConditions->Copy=$a->Copy;
                 $EnviromentConditions->Scan=$a->Scan;
                 $EnviromentConditions->Printer=$a->Printer;
                 $EnviromentConditions->Fax=$a->Fax;
                 $EnviromentConditions->PrintFlow= $a->PrintFlow;
                 $EnviromentConditions->OperatingSystem= $a->OperatingSystem ;
                
                 //$EnviromentConditions->Area= $a->Area;
                 //$EnviromentConditions->CostCenter= $a->CostCenter;    
        }

        $EnviromentConditions->save();

        $obj_incident->save();
        $params['ref_no']=$a->ref_no;
        $params['incident']=$obj_incident;
        $params['CUST']=$obj_incident->CustomFields->c;
        $params['EMB']=$EnviromentConditions;
        
        $params['cont1_hh']=$a->cont1_hh;
        $params['cont2_hh']=$a->cont2_hh;
        
        echo  json_encode($params,true,5);

    }
}