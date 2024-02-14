<?php
namespace Custom\Widgets\integer;
use RightNow\Connect\v1_2 as RNCPHP;
class SummaryTicket extends \RightNow\Libraries\Widget\Base {
  public function __construct($attrs)
  {
      parent::__construct($attrs);

  }
  public function getData()
  {
    $this->CI->load->model('custom/ws/TicketModel');
    $this->CI->load->model('custom/ws/EnviromentConditions');
    $this->CI->load->model('custom/GeneralServices');
    
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
      //echo "-->". json_encode($this->data['order']->CustomFields->c->ar_flow->ID);
      //echo "-->". json_encode($this->data['order']->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut);
      $result_HH = $this->CI->GeneralServices->BuscaInfoHHNbp($this->data['order']->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut,$this->data['order']->CustomFields->c->id_hh,'','');
      //$result_HH = $this->CI->GeneralServices->BuscaInfoHHNbp('69070400-5','1817540','','');
      $this->data['order']->nbp=$result_HH->hhs;
      
      
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
  
  $BN=0;
  $Color=0;
      foreach ($result_cnt->Contadores as $key => $value) {

          switch($value->TipoContador->ID)
          {
            case 1:
              if( $BN<=$value->Value)
              {
                $BN=$value->Value;
              $grupo=$grupo|1;
              $this->data['copia_BN']=$value;
              $this->data['copia_Dupl']=$value;
              }
              break;
            case 2:
              $grupo=$grupo|2;
              $this->data['copia_Color']=$value;
              break;
            case 13:
              if( $BN<=$value->Value)
              {
                $BN=$value->Value;
                $grupo=$grupo|4;
                $this->data['copia_Dupl']=$value;
              }
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


    return parent::getData();
  }
}
