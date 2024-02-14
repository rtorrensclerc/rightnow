<?php

namespace Custom\Controllers;



  use RightNow\Connect\v1_3 as RNCPHP;
   
  //----------- Ready to proceed with script --------//

class Test extends \RightNow\Controllers\Base
{

  CONST USER = "UserDimacofi";
  public function __construct()
  {
    parent::__construct();
    $this->load->model('custom/ConnectUrl');
    $this->load->library('Blowfish', false); //carga Libreria de Blowfish
    $this->load->model('custom/ws/TicketReparation'); //Modelo para acceder a tickets de reparación
    $this->load->model('custom/ws/TicketDevolution'); //Modelo para acceder a tickets de devolución



  }

  public function prueba() 
  {
    $color = array();
    $i=0;
    for ($i=0;$i<=24;$i++)
    {
      $color[]=0;
    }
    
    for ($i=0;$i<=24;$i++)
    {
      echo $color[$i]. "<br>";
    }
    
      
  }

  public function SetProfile() 
  {
    $contact                                       = RNCPHP\Contact::fetch(140112);
    //$contact->CustomFields->PROF->ProfileType = RNCPHP\PROF\Type::fetch(18);
    //$contact->Save(RNCPHP\RNObject::SuppressAll);
    echo json_encode($contact->CustomFields->PROF->ProfileType->ID);
    return;
    $contact->Save(RNCPHP\RNObject::SuppressAll);
      
  }

  public function cierreticket()
  {

    $retiro=38863;
    $rma=1733376;
    $incident = RNCPHP\Incident::fetch("231211-000173");
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

  public function hhsuppliers()
  {
    $asset = RNCPHP\Asset::first("SerialNumber = '" . 4091543  . "'");
    $productId =   $asset->CustomFields->DOS->Product->ID;
    $incident = RNCPHP\Incident::fetch("240125-000463");
    $a_items                         = RNCPHP\OP\OrderItems::find("Incident.ID = {$incident->ID}");
    //echo json_encode($a_suppliers) . "<br>"; 
    
    echo json_encode($incident->CustomFields->c) . "<br>"; 
    $ProductPrice=json_decode($incident->CustomFields->c->predictiondata);

    echo $incident->CustomFields->c->predictiondata .  count($ProductPrice->valores->values) . '<br>';

    if(count($ProductPrice->valores->values)>1)
    {

      foreach ($a_items as $item)
      {
          foreach($ProductPrice->valores->values as $value)
          {
            if($value->CODIGO_PRODUCTO==$item->Product->CodeItem)
            {
              echo "ENCONTRADO -- " . $value->VALOR_CONVENIO . "<br>";
              //VALOR_CONVENIO
              $item->UnitTempSellPrice=round($value->VALOR_CONVENIO*$value->CONVERSION_RATE);
              $item->Save();
            }
          }
      }
    }
    else
    {
        echo $ProductPrice->valores->values->CODIGO_PRODUCTO ."-" .$supplier->CodeItem. " uno <br> ";

        foreach ($a_items as $item)
        {
           
           
            if($ProductPrice->valores->values->CODIGO_PRODUCTO==$item->Product->CodeItem)
            {
              echo "ENCONTRADO ---" . $ProductPrice->valores->values->VALOR_CONVENIO . "<br>";
              //VALOR_CONVENIO
              $item->UnitTempSellPrice=round($ProductPrice->valores->values->VALOR_CONVENIO*$ProductPrice->valores->values->CONVERSION_RATE);
              $item->Save();
            }
        }
        
    }
  }
  public function getwso2()
  {
    $CI =& get_instance();
    $CI->load->model('custom/ConnectUrl');
    $data           = array("grant_type" => "client_credentials");
    $consumerKey    = "yh8wgLIb4RLIHwQ868CIifi2EYca"; // Prod 
    $consumerSecret = "bfaZkjfdIWoEtiXoDbo4E_EPpAka"; // Prod
  
    $consumerKey    = "gaIIMLvsZM6tMv7G6WgDeAdDb7Ma"; // TEST 
    $consumerSecret = "5cTKPPLY2mCsiR23jvwB63j446ka"; // TEST

    $tokenA = $CI->ConnectUrl->requestCURLByPost("https://api.dimacofi.cl:8290/token", $data, $consumerKey . ":" . $consumerSecret);
    echo "[".json_encode($tokenA)."]";
    $a_jsonToken = json_decode($tokenA, TRUE);
    $token = $a_jsonToken["access_token"];

    $org_rut = '65175239-6';
    $a_request = array(
        "RUT" => $org_rut
    );
    $json_request=json_encode($json_request);
    $response=$CI->ConnectUrl->requestCURLJsonRaw('https://api.dimacofi.cl/apiCloudMD/getUSDValue', '', $token); 
   

    
    echo $response;
  }

  public function getIncident() 
  {
    
    //$incident = RNCPHP\Incident::first("CustomFields.c.guide_dispatch='1008289' and StatusWithType.status.ID not in(2,149,146)" );
    $incident = RNCPHP\Incident::fetch("231031-000394");
    echo json_encode($incident->Threads[0]) .'<br>';
    echo json_encode($incident->Threads[0]->Account) .'<br>';
    echo json_encode($incident->Threads[0]->Account->DisplayName) .'<br>';
    
    $Account=$incident->Threads[0]->Account->DisplayName;
    $Channel=$incident->Threads[0]->Channel;
    $Contact=$incident->Threads[0]->Contact;
    $ContentType=$incident->Threads[0]->ContentType;
    $incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
    $incident->Threads[0]->EntryType->ID = 1;
    $textoNP=$incident->Threads[0]->Text;

    $incident->Threads = new RNCPHP\ThreadArray();
    $incident->Threads[0] = new RNCPHP\Thread();
 
    
    $incident->Threads[0]->Channel = $Channel;
    $incident->Threads[0]->EntryType->ID = 1; // Used the ID here. See the Thread object for definition
    $incident->Threads[0]->Text = "Nota de Jefe de Taller(" . $Account . "): [" . $textoNP . "]";
    $incident->Save(RNCPHP\RNObject::SuppressAll);

  }


  public function getContact() 
  {

    $obj_org = RNCPHP\Organization::first("CustomFields.c.rut = '96885940-4'");
    echo json_encode($obj_org) .'<br>';
    echo json_encode($obj_org->Emails[0]) .'<br>';
    $cfg_domain   = RNCPHP\Configuration::fetch(CUSTOM_CFG_BLACK_LIST_DOMAIN);
   
    
    echo $obj_org->Emails[0]->Address;


    //$pos = strpos(strtoupper($a_data['data']->Emails[0]->Address), strtoupper($item_domain));


    $contact = RNCPHP\Contact::fetch(140112);
    echo json_encode($contact->ContactType->ID) .'<br>';
    echo json_encode($contact->ContactType->LookupName) .'<br>';
    echo json_encode($contact->CustomFields->PROF->ProfileType).'<br>';
    echo json_encode($contact->CustomFields->PROF->ProfileType->LookupName) .'<br>';
    echo json_encode($contact->Emails[0]->Address);
  }
  

  public function FindTicket()
  {
    $incident = RNCPHP\Incident::fetch("231214-000292" );
    //echo json_encode($incident);
    //$asset = RNCPHP\Asset::first("SerialNumber = '" . $incident->CustomFields->c->id_hh . "'");
    $asset = RNCPHP\Asset::first("SerialNumber = '" . $incident->CustomFields->c->id_hh . "'");
    //echo $incident->CustomFields->c->id_hh .'<br>';
    $incident2=RNCPHP\Incident::first(" CustomFields.c.hh_replacement='" . $incident->CustomFields->c->id_hh ."' and  StatusWithType.Status.ID = 3 and Disposition.ID = 123");
    //$incident2=RNCPHP\Incident::first("StatusWithType.Status.ID = 3 and Disposition.ID = 123");
    //echo $incident2->CustomFields->c->hh_replacement.'<br>';
    //$str="CustomFields.c.hh_replacement='" . $incident->CustomFields->c->hh_replacement ."'";
    //echo $str .'<br>';
   
    if($incident2)
    {
      echo $incident2->CustomFields->c->id_hh .'-' . $incident2->CustomFields->c->hh_replacement;
    }
    else
    {
      echo "No existe";
    }

  }
  public function getdir()
  {

    $a_dir    = RNCPHP\DOS\Direccion::find("Organization.CustomFields.c.rut in ('77839870-2')");
    foreach($a_dir as $dir)
    {
      echo $dir->ID .'-' . $dir->d_id. '-' . $dir->LookupName .'<br>';
    }

  }

  public function testHTML()
  {
    $contact=RNCPHP\Contact::fetch(140015);
    echo json_encode($contact);
    $text                                = "<p>su cuenta en Sucursal Virtual Dimacofi, ha sido creada y quedará habilitada a la brevedad.
    Por temas de seguridad para usted la estamos validando.

En caso de cualquier consulta puede comunicarse al  correo midimacofi@dimacofi.cl o al  +569 77480590</p>";

    $First = 'Rodrigo';
    $Last = 'Torrens';
    $html_final =
    "<div style='FONT-FAMILY:Arial,sans-serif'>
      <div>
        <table style='FONT-FAMILY: Segoe UI, Verdana, sans-serif' cellspacing='0' cellpadding='0' width='550' align='center' border='0'>
           <tbody>
           <tr>
           <td><img border='0' alt='Image' src='https://soportedimacoficl.custhelp.com/euf/assets/images/email/header.png' width='550' height='109' /></td>
           </tr>
           <tr>
           <td style='BACKGROUND-COLOR:#fff'>
              <table style='FONT-FAMILY:Arial,sans-serif;WIDTH:530px' cellspacing='0' cellpadding='0' bgcolor='white'>
                <tbody>
                  <tr>
                    <td>
                      <p style='MARGIN:0px 0px 0px 15px'><span></span></p>
                      <div><span>Un contacto ha sido creado en  MiDimacofi <br> Favor validar para su activación:</span></div>
                      <p><div><span>Nombre   :".$contact->Name->First ." ".$contact->Name->Last ."</span></div></p>
                      <p><div><span>Telefono : ".json_encode($contact->Phones[0]->Number) . "</span></div></p>
                      <p><div><span>Correo   :".$contact->Emails[0]->Address."</span></div></p>
                      <p><div><span>Razon Social :".$contact->Organization->Name ."</span></div></p>
                      <p><div><span>RUT      :".$contact->Organization->CustomFields->c->rut ."</span></div></p>
                      
                   
                      <table style='FONT-FAMILY: Segoe UI, Verdana, sans-serif' cellpadding='1' width='100%''>
<tbody>
<tr>
<td style='BORDER-TOP: #d0d0d0 1px solid'>
<h1 style='FONT-SIZE: 11pt; FONT-WEIGHT: bold; COLOR: #848484; MARGIN: 10px 0px 5px'></h1>
</td>
</tr>
</tbody>
</table>

                      <div><span>&nbsp;</span></div>
                      <div><span>&nbsp;</span></div>
                    </td>
                  </tr>
                </tbody>
              </table>
              <p><div><span>".$adicional."</span></div></p>
           </td>
           <tr>
           <td><img alt='Image' src='https://soportedimacoficl.custhelp.com/euf/assets/images/email/footer.png' width='550' height='47' />&#160;</td>
           </tr>
           </tbody>
           </table>

      </div>
    </div>";
  

    echo $html_final;
  }

  public function GetOpo()
  {

    {

       
      //if ($cycle !== 0) return;
      $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
      $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
      $incident = RNCPHP\Incident::fetch(1184526);

      //self::insertPrivateNote($incident, "RESPxxxx : ". $incident->CustomFields->c->invoice_number);
      if($incident->CustomFields->c->invoice_number==0)
      {
       
        
        $new_opportunity                                  = new RNCPHP\Opportunity();
        //self::insertPrivateNote($incident, "NUEVO 2: ");
        $new_opportunity->Name                            = "Cobro Mano de Obra Inicial - {$incident->ReferenceNumber}";
        $new_opportunity->InitialContactDate              = $incident->UpdatedTime;
        $new_opportunity->PrimaryContact                  = new RNCPHP\OpportunityContact();
        $new_opportunity->PrimaryContact->Contact         = $incident->PrimaryContact;
        $new_opportunity->StatusWithType                  = new RNCPHP\StatusWithType();
        $new_opportunity->StatusWithType->Status->ID      = 200; //14; //Aceptado
        $new_opportunity->CustomFields->c->source_opp->ID = 66; // Cobro Mano de Obra
        $new_opportunity->CustomFields->c->id_venus=$incident->ReferenceNumber;
        $new_opportunity->CustomFields->c->id_hh=$incident->CustomFields->c->id_hh;
        $new_opportunity->CustomFields->c->oc_number=$incident->CustomFields->c->contract_number;
        //self::insertPrivateNote($incident, "RESP11 : ". $incident->CustomFields->c->invoice_number);
        //Asignar Territorio
        if ($incident->AssignedTo->Account->SalesSettings->Territory)
        {
          $new_opportunity->Territory                     = RNCPHP\SalesTerritory::fetch($incident->AssignedTo->Account->SalesSettings->Territory->ID);
        }
     
        //self::insertPrivateNote($incident, "RESP2 : ". $incident->CustomFields->c->invoice_number);
        //Organización del Incidente
        if ($incident->CustomFields->DOS->Direccion->Organization)
        {
          $new_opportunity->Organization                  = RNCPHP\Organization::fetch($incident->CustomFields->DOS->Direccion->Organization->ID);
        }
  
        $new_opportunity->CustomFields->OP->IncidentService = $incident;
       
        $a=RNCPHP\Comercial\Ejecutivo::fetch(105);
        //self::insertPrivateNote($incident, "RESP3 : ". $incident->CustomFields->c->invoice_number);
  
        $new_opportunity->CustomFields->Comercial->EjecutivoZona = $a;
      
        if (!empty($incident->CustomFields->DOS->Vendedor))
        {
           $new_opportunity->CustomFields->Comercial->Ejecutivo = $incident->CustomFields->DOS->Vendedor;
        }
        if (!empty($incident->CustomFields->DOS->DireccionFacturacioon))
        {
          $new_opportunity->CustomFields->OP->Direccion = $incident->CustomFields->DOS->DireccionFacturacioon;
        }
        //self::insertPrivateNote($incident, "RESP4 : ". $incident->CustomFields->c->invoice_number);
        //$new_opportunity->CustomFields->c->id_ar= $incident->CustomFields->c->invoice_number;
        $new_opportunity->CustomFields->c->id_venus= $incident->ReferenceNumber;
        
  
        $new_opportunity->save();
  
  
        $cfg_idProductWF = RNCPHP\Configuration::fetch( "CUSTOM_CFG_PRODUCT_ID_VISITA" );
        $Product           = RNCPHP\OP\Product::fetch($cfg_idProductWF->Value);
      
        //Agregar Linea Cobro mano de obra
        $WorkForceLine                    = new RNCPHP\OP\OrderItems();
        $WorkForceLine->QuantitySelected  = 1;
        $WorkForceLine->QuantityReserved  = 1;
        $WorkForceLine->QuantityConfirmed = 1;
        $WorkForceLine->Product           = $Product;
        $WorkForceLine->Opportunity       = $new_opportunity;
        $WorkForceLine->State             = 3; //Confirmado
        $WorkForceLine->Save();

        echo $new_opportunity->ID;
        //deja que el Incidente avance a la siguiente etapa 
       /* $incident->CustomFields->c->invoice_number=$new_opportunity->ID;
        $incident->StatusWithType->Status->ID=117;
        self::insertPrivateNote($incident, "PTTO Creado : ". $new_opportunity->ID);
        $incident->Save(RNCPHP\RNObject::SuppressAll);*/
        
      }
      else
      {
        echo "Nada";
      }
    }

  }
  public function SetTicketState ()
  {



    $obj_incident_temp = RNCPHP\Incident::first("CustomFields.c.guide_dispatch='1011167'" );
    echo "<br>".json_encode($obj_incident_temp->Disposition->ID);
    echo "<br>".$obj_incident_temp->CustomFields->OP->Incident->ReferenceNumber;
    echo "<br>".$obj_incident_temp->CustomFields->OP->Incident->Disposition->ID;
    
    if($obj_incident_temp->CustomFields->OP->Incident->ReferenceNumber and $obj_incident_temp->CustomFields->OP->Incident->Disposition->ID<>70)
    {
        echo "OK";
    }
    else
    {
      echo "NOOK";
    }
    return;
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
   $data_post = '{"usuario":"UserDimacofi","accion":"SetTicketState","datos":{"guia":["123456789","123456788"],"status": "112"}}';


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
foreach ($array_data->datos->guia as $key => $value) {

   /*Cambia estado de Cada ticket  buscando la Guia respectiva
   */

    echo $value .'<br>';

    $incident = RNCPHP\Incident::first("CustomFields.c.guide_dispatch='" . $value  . "' and StatusWithType.status.ID not in(2,149,146)" );
    echo json_encode($incident->ReferenceNumber) .'<br>';

          $obj_incident = $this->TicketModel->getObjectTicket($incident->ReferenceNumber);

          //$Incident = RNCPHP\Incident::fetch( $value);
          echo $obj_incident->ReferenceNumber . '-' . $obj_incident->StatusWithType->Status->ID  . '<br>';

            if($obj_incident->ReferenceNumber)
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


  private function CierraCanival($fatherIncident) 
  {
    $canibal= RNCPHP\Incident::find("CustomFields.OP.Incident.ReferenceNumber = '" . $fatherIncident->ReferenceNumber ."' and StatusWithType.StatusType not in(2,149) and  Disposition.ID =43"  ); 
      // $canibal= RNCPHP\Incident::find("CustomFields.OP.Incident.ReferenceNumber = '{$fatherIncident->ReferenceNumber}'  and StatusWithType.StatusType not in(2,149) and  Disposition.ID =43");

    foreach ($canibal as $tk)
    {
    
      echo "CERRADO<br>" . json_encode($tk->StatusWithType->Status->ID)  ;
      $tk->StatusWithType->Status->ID=2;
      $tk->Save(RNCPHP\RNObject::SuppressAll);
    }
    return $canibal;
  }
  public function testcanibal() 
  {
      $fatherIncident = RNCPHP\incident::fetch('230620-000346');
      echo json_encode($this->CierraCanival($fatherIncident) );
  }

  public function testRP()
  {
    $ar                    = RNCPHP\AnalyticsReport::fetch(102309);
    //echo json_encode($ar->Filters);
    foreach($ar->Filters as $key => $value)
    {
        echo $value->Name . ' ' . $value->Operator->ID. '<br>';
    }
  }


  public function testOP()
  {

    
    $this->load->model('custom/ws/OpportunityModel');
    $opportunity = RNCPHP\Opportunity::fetch(23921);
    echo json_encode($opportunity->AssignedToAccount->Login);

    //$obj_line=RNCPHP\OP\OrderItems::find("Opportunity.ID = 23912 ");
    $obj_line=RNCPHP\OP\OrderItems::find("Opportunity.ID = 23921 and Enabled = 1");
    //$opportunity = RNCPHP\Opportunity::fetch(23445);
    //$obj_line = $this->OpportunityModel->getItems(23912);
    $total_acumulado=0;


    foreach($obj_line as $key => $line)
    {
      echo '<br>';
      echo json_encode($line) . '<br>';
      echo "...>" . $line->Discount . '<br>';
      echo "...>" . $line->item_dolar_value . '<br>';
      $UnitTempSellPrice            = $line->Opportunity->CustomFields->c->dolar_value * $line->item_dolar_value;
      $discountPrice                = round($UnitTempSellPrice  * $line->Discount/100);
      echo $UnitTempSellPrice. '    ---- <br>';
      echo $discountPrice. '<br>';
      echo  round($UnitTempSellPrice-$discountPrice). '<br>';
      echo round(($UnitTempSellPrice-$discountPrice) * $line->QuantitySelected). '<br>';



      echo   '$ ' . number_format($line->UnitTempSellPrice/((100-$line->Discount)/100) ) . '<br>';
      echo     $iva_acumulado   + round($line->ConfirmedSellPrice* (19/100))  . '<br>';
      echo   round($line->ConfirmedSellPrice) .'<br>'; 

      /*    
      echo  json_encode($line) .'<br>';
      $tempValue                     = $line->UnitTempSellPrice  * $line->QuantitySelected;
      $discountPrice                 = ($tempValue * $line->Discount)/100;
      //$line->item_dolar_value=number_format(83.08641975*(100+3)/100,2);
      //$line->save();
      echo  $opportunity->CustomFields->c->dolar_value . '<br>';
      echo  number_format(83.08641975*(100+3)/100,2) . '<br>';
      echo  $line->item_dolar_value  .'<br>';
      echo  $opportunity->CustomFields->c->dolar_value * $line->item_dolar_value  .'<br>';
      echo  $tempValue .'<br>';
      echo  $discountPrice .'<br>';
      echo  $line->Discount .'<br>';
      echo  $line->DiscountSellPrice .'<br>';
      */
    }

    return;
  }
  public function TESTXXX()
  {
    $incident = RNCPHP\Incident::fetch('230606-000337');

    echo json_encode($incident->CustomFields->c);
    return;

 
    $new_opportunity                                  = new RNCPHP\Opportunity();
         $new_opportunity->Name                            = "Cobro Mano de Obra Inicial - TEST";
         $new_opportunity->InitialContactDate              = $incident->UpdatedTime;
         $new_opportunity->PrimaryContact                  = new RNCPHP\OpportunityContact();
         $new_opportunity->PrimaryContact->Contact         = $incident->PrimaryContact;
         $new_opportunity->StatusWithType                  = new RNCPHP\StatusWithType();
         $new_opportunity->StatusWithType->Status->ID      = 200; // Cerrado y Facturado
         $new_opportunity->CustomFields->c->source_opp->ID = 66; // Cobro Mano de Obra
         $new_opportunity->CustomFields->c->id_venus=$incident->ReferenceNumber;
         $new_opportunity->CustomFields->c->id_hh=$incident->CustomFields->c->id_hh;
    
          //Asignar Territorio
          if ($incident->AssignedTo->Account->SalesSettings->Territory)
          {
            $new_opportunity->Territory                     = RNCPHP\SalesTerritory::fetch($incident->AssignedTo->Account->SalesSettings->Territory->ID);
          }
       
    
          //Organización del Incidente
          if ($incident->CustomFields->DOS->Direccion->Organization)
          {
            $new_opportunity->Organization                  = RNCPHP\Organization::fetch($incident->CustomFields->DOS->Direccion->Organization->ID);
          }
    
          $new_opportunity->CustomFields->OP->IncidentService = $incident;
         
          $a=RNCPHP\Comercial\Ejecutivo::fetch(105);
         
    
          $new_opportunity->CustomFields->Comercial->EjecutivoZona = $a;
        
          if (!empty($incident->CustomFields->DOS->Vendedor))
          {
             $new_opportunity->CustomFields->Comercial->Ejecutivo = $incident->CustomFields->DOS->Vendedor;
          }
          if (!empty($incident->CustomFields->DOS->DireccionFacturacioon))
          {
            $new_opportunity->CustomFields->OP->Direccion = $incident->CustomFields->DOS->DireccionFacturacioon;
          }
    
          //$new_opportunity->CustomFields->c->id_ar= $incident->CustomFields->c->invoice_number;
          $new_opportunity->CustomFields->c->id_venus= $incident->ReferenceNumber;
          
    
          $new_opportunity->save();
          $cfg_idProductWF = RNCPHP\Configuration::fetch( "CUSTOM_CFG_PRODUCT_ID_VISITA" );
          $Product           = RNCPHP\OP\Product::fetch($cfg_idProductWF->Value);
        
          //Agregar Linea Cobro mano de obra
          $WorkForceLine                    = new RNCPHP\OP\OrderItems();
          $WorkForceLine->QuantitySelected  = 1;
          $WorkForceLine->QuantityReserved  = 1;
          $WorkForceLine->QuantityConfirmed = 1;
          $WorkForceLine->Product           = $Product;
          $WorkForceLine->Opportunity       = $new_opportunity;
          $WorkForceLine->State             = 3; //Confirmado
          $WorkForceLine->Save();
         
          $incident->CustomFields->c->invoice_number=$new_opportunity->ID;
          $incident->Save(RNCPHP\RNObject::SuppressAll);
          echo json_encode($new_opportunity->ID);
          return;

  
/*
    $CI =& get_instance();
    $CI->load->model('custom/ConnectUrl');
    $data           = array("grant_type" => "client_credentials");
    $consumerKey    = "yh8wgLIb4RLIHwQ868CIifi2EYca"; // Prod 
    $consumerSecret = "bfaZkjfdIWoEtiXoDbo4E_EPpAka"; // Prod
  
    $tokenA = $CI->ConnectUrl->requestCURLByPost("https://api.dimacofi.cl/token", $data, $consumerKey . ":" . $consumerSecret);
    echo json_encode($tokenA);
    $a_jsonToken = json_decode($tokenA, TRUE);
    $token = $a_jsonToken["access_token"];
    echo $token;

    //$this->sendResponse($token);

            $org_rut = '65175239-6';
            $a_request = array(
                "RUT" => $org_rut
            );
            $json_request=json_encode($json_request);
            $response=$CI->ConnectUrl->requestCURLJsonRaw('https://api.dimacofi.cl/apiCloudMD/getRutStatusSAI', '{"RUT":"65175239-6"}', $token); 
            $status=json_decode($response);
            $TOTAL=0;
    foreach($status->Invoice->InvoiceData->Invoices as $s)        
    {
      /*$origin = date_create($s->DUE_DATE);
      $now = date('d-m-y');
      $interval = date_diff($origin,$target);
      echo  $interval .'<br>' ;
      
      

      $data=explode('/',$s->DUE_DATE);
      $dateString = $data[2].'-'.$data[1].'-'.$data[0];
      $currentDate = date('Y-m-d'); // Fecha actual en formato 'Y-m-d'

      $dateTimestamp = strtotime($dateString);
      $currentTimestamp = time();

      $secondsDifference = $dateTimestamp - $currentTimestamp;
      $monthsDifference = floor($secondsDifference / (30 * 24 * 60 * 60));
      echo "<br>[" . $monthsDifference . "]" . $TOTAL . ' ';

      if ($monthsDifference >= -1) {
          echo "La diferencia es menor a un mes." . +$TOTAL;
    
      } else {
          echo "La diferencia es igual o mayor a un mes." . +$TOTAL;
          $TOTAL=$TOTAL+$s->AMOUNT;
      }

    }
    echo "<br>" . number_format($TOTAL);
    return;

    */
    /*foreach ($opportunity->FileAttachments as $key => $value) {

      $FILE=$value;
      //$this->sendResponse(json_encode($FILE));
      if($FILE)
      {  
        $str2 = str_replace("//", "/", $FILE->getAdminURL(), $count);
        //$str2 = str_replace("https", "https", $FILE->getAdminURL(), $count);
        $ZZZ='<a href="' . $str2 . '">' . $FILE->FileName . '</a>';
        $this->typeFormat='html';
        $FileList[$index]["File"]=$FILE->FileName;
        $FileList[$index]["url"]=$str2;
        $imagedata = file_get_contents($str2);
        echo $imagedata . '<br>';
      }
      else {
        $FileList[$index]['File']="Sin Archivo";
        $FileList[$index]['url']="null";
      }
      $index++;
    }
*/
    /*$CI =& get_instance();
    $CI->load->model('custom/ConnectUrl');
    $url='https://soportedimacoficl.custhelp.com/services/rest/connect/v1.4/opportunities/23041/fileAttachments/963768/data'; 
    $service=$CI->ConnectUrl->requestGetA($url, "rtorrens", "Rtorrens.2020"); 
    */
  /*Print the request response*
	echo "Response code: $osn_req->HttpResponseCode \n Response payload: $osn_req->ResponsePayload";
  */

  $url='https://api.dimacofi.cl/cloudsai/filedata';



  $opportunity = RNCPHP\Opportunity::fetch(23294);

  $a_orderItems          = RNCPHP\OP\OrderItems::find("Opportunity.ID ='{$opportunity->ID}'");
  
  echo json_encode($opportunity->StatusWithType->Status->ID) .'<br>';
  echo json_encode($opportunity->CustomFields->c->id_venus) .'<br>';
  echo json_encode($opportunity->CustomFields->c->oc_number) .'<br>';
  echo json_encode($opportunity->AssignedToAccount->ID) .'<br>';
  
  echo json_encode($a_orderItems[0]->Product->CodeItem);
  echo json_encode($opportunity->PrimaryContact->Contact->LookupName) .'<br>';
  echo json_encode($opportunity->PrimaryContact->Contact->Emails[0]->Address) .'<br>';
  echo json_encode($opportunity->PrimaryContact->Contact->Phones[0]->RawNumber) .'<br>';

    echo json_encode($opportunity) .'<br><br><br>';
    echo json_encode($opportunity->CustomFields) .'<br><br><br>';
    echo json_encode($opportunity->CustomFields->OP->DireccionEnvio->d_id) .'<br>';
    echo json_encode($opportunity->CustomFields->c->payment_conditions->ID) .'<br>';
    return;
    echo json_encode($opportunity->FileAttachments) .'<br>';
    foreach($opportunity->FileAttachments as $file)
    {
      $postArray = array(
        "id_ptto" => $opportunity->ID,
        "id_file" => $file->ID
      );
      $response = $this->ConnectUrl->requestCURLJsonRaw($url, json_encode($postArray));
      echo $response;
      //$link=$link .'<a href="https://soportedimacoficl.custhelp.com/ci/fattach/get/'. $file->ID .'/'. $file->CreatedTime .'/filename/'. $file->FileName .'">' . $file->FileName .'</a><br>';
      
    }

  return;
    $lineas = array();

    $array_linea = array(
      "tipo_transaccion_linea_id"=> 22,
      "sub_rubro_id"=> 62,
      "cantidad"=> 1,
      "delfos"=> "57999",
      "precio"=> 40000,
      "direccion_despacho_id"=> 201673,
      "instrucciones_envio"=> "es una prueba",
      "nombre_contacto"=> "Juan Perez",
      "email_contacto"=> "juanito@dimacofi.cl",
      "telefono_contacto"=> "133"
    );
    $lineas[] = $array_linea;

    $array_linea = array(
      "tipo_transaccion_linea_id" => 22,
      "sub_rubro_id" => 62,
      "cantidad" => 1,
      "delfos"=> "57999",
      "precio" => 40000,
      "direccion_despacho_id"=> 201673,
      "instrucciones_envio"=> "es una prueba",
      "nombre_contacto"=> "Juan Perez",
      "email_contacto"=> "juanito@dimacofi.cl",
      "telefono_contacto"=> "133"
    );


    $lineas[] = $array_linea;

  
    

    $a_request = array(
      "customer_id" => 145055,
      "direccionfacturacion_id" => 118925,
      "tipo_transaccion_id" => 12,
      "v_leasing_cliente_id" => 145055,
      "v_leasing_direccion_id" => 118925,
      "vendedor_id" => 405,
      "condiciones_de_pago_id" => 1,
      "forma_de_pago_id" => 1,
      "numero_oc" => "1234",
      "comentarios" => "es una prueba",
      "aprobacion" => true,
      "referencia_externa" =>"12345678",
      "lineas"=> $lineas 
    );
    echo json_encode($a_request);
  }

  public function valid_url()
  {
    if(!isset($_POST["test"]))
    {
        echo '
        <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Probar Servicios</title>
            </head>
            <body>
                <form action="valid_url" method="post">
                    <input type="text" name="url_service" id="url_service" lenght="250">
                    <input type="submit" value="Probar Servicio" name="test">
                </form>
            </body>
        </html>';
    }
    else
    {
        $url = $_POST["url_service"];
        // $response = $this->ConnectUrl->requestCURLJsonRaw($url, "{}");
        $response = $this->ConnectUrl->requestGet($url);
        var_dump($url, $response, $this->ConnectUrl->getResponseError());
    }
  }

  public function loginNubePrint()
  {
    $url = "https://dimacofi.nubeprint.com/panel/apiv1/auth/";
    $postArray = array(
      "login" => "atenciones@dimacofi.cl",
      "pass" => "1234567D."
    );
    $response = $this->ConnectUrl->requestCURLByPost($url, $postArray);
    var_dump($url, $response, $this->ConnectUrl->getResponseError());
  }

  public function getPanelInventory()
  {
    $url = "https://dimacofi.nubeprint.com/panel/apiv1/auth/";
    $postArray = array(
      "login" => "atenciones@dimacofi.cl",
      "pass" => "1234567D."
    );
    $response = $this->ConnectUrl->requestCURLByPost($url, $postArray);

    if($response === FALSE)
    {
      var_dump($url, $response, $this->ConnectUrl->getResponseError());
    }

    if(trim($response) == "Nubeprint - Access granted")
    {
      $headersLogin = $this->ConnectUrl->getLastHeaders();
      $a_headersLogin = explode("\n", $headersLogin);
      $headerSesion = NULL;
      $headerRequest = NULL;
      $a_headersRequest = array();

      // Se buscará el header que tiene el ID de sesión.
      foreach ($a_headersLogin as $key => $header) 
      {
        if(strpos($header, "nubeprint") !== FALSE)
        {
          $headerSesion = $header;
          break;
        }
      }

      // Se despejará el valor del header sesión.
      preg_match('/Set-Cookie: (.*); Path=.*/', $headerSesion, $matches, PREG_OFFSET_CAPTURE);

      if(isset($matches[1]))
      {
        $headerRequest = $matches[1][0];

        $a_headersRequest[] = "Cookie: " . $headerRequest;
  
        $url = "https://dimacofi.nubeprint.com/panel/apiv1/inventory/";
        $response = $this->ConnectUrl->requestGet($url, $a_headersRequest);
        if($response === FALSE)
        {
          var_dump($url, $response, $this->ConnectUrl->getResponseError());
        }
        else
        {
          var_dump($response);
        }
      }
    }
  }



  public function testXX()
  {
    $json_data = '{"dolar":{"values":{"CODIGO_PRODUCTO":"38521","DESCRIPCION_PRODUCTO":"DEVELOPER TYPE F CYAN RICOH","VALOR_US":"39.27694064"}}}';

    $data = json_decode($json_data);
    // echo json_encode($data->dolar->values->VALOR_US);
    if($data->dolar->values->VALOR_US>=0)
    {
        $opportunity = RNCPHP\Opportunity::fetch(23041);

        
        $obj_product = RNCPHP\OP\Product::first("CodeItem =  '{$data->dolar->values->CODIGO_PRODUCTO}' ");
        // echo "-->". $data->dolar->values->CODIGO_PRODUCTO .'-'. json_encode($obj_product->ID);
        $idOP       = $opportunity->ID;
        echo "Product.ID =  '{$obj_product->ID}' and Opportunity.ID = {$idOP} ";
       
        $obj_line   = RNCPHP\OP\OrderItems::find("Product.ID =  '{$obj_product->ID}' and Opportunity.ID = {$idOP} ");
        echo  json_encode($obj_line[0]->UnitTempSellPrice);
        
        
        if(count($obj_line) > 0)
        {
          foreach($obj_line as $order_items_expected_object)
          {
            $order_items_expected_object->UnitTempSellPrice = "Integer Test";
            $order_items_expected_object->Save(RNCPHP\RNObject::SuppressAll);

            echo "valor configurado a base de datos -> ".$order_items_expected_object->UnitTempSellPrice;
            exit();
          }
        }
        // echo 'ConfirmedCost     :' . $obj_line->ConfirmedCost .' <br>';
        // echo 'Enabled           :' . $obj_line->Enabled .' <br>';
        //echo 'State             :' . json_encode($obj_line->State) .' <br>';
        // echo 'Alternative       :' . $obj_line->Alternative .' <br>';
        //echo 'RefLineOM         :' . json_encode($obj_line->RefLineOM) .' <br>';
        //echo 'Opportunity       :' . json_encode($obj_line->Opportunity) .' <br>';
        // echo 'DiscountSellPrice :' . $obj_line->DiscountSellPrice .' <br>';
        // echo 'UnitTempSellPrice :' . $obj_line->UnitTempSellPrice .' <br>';
        
        
        /*
                $item                   = new RNCPHP\OP\OrderItems();
                $item->QuantitySelected = 1;
                $item->Product          = RNCPHP\OP\Product::fetch(10331);
                $item->Opportunity=$opportunity;
                $item->Save();

        */

        //$obj_line->UnitTempSellPrice   = 'TEST';
        //$obj_line->Save();
        
       
        
        


        $a_orderItems1          = RNCPHP\OP\OrderItems::find('Opportunity.ID =' . $idOP);

        // echo json_encode($a_orderItems1);
        // $a_orderItems1[0]->UnitTempSellPrice   = 'TEST';
        // $a_orderItems1[0]->Save();

        // echo 'ConfirmedCost     :' . $a_orderItems1[0]->ConfirmedCost .' <br>';
        // echo 'Enabled           :' . $a_orderItems1[0]->Enabled .' <br>';
        //echo 'State             :' . json_encode($obj_line->State) .' <br>';
        // echo 'Alternative       :' . $a_orderItems1[0]->Alternative .' <br>';
        //echo 'RefLineOM         :' . json_encode($obj_line->RefLineOM) .' <br>';
        //echo 'Opportunity       :' . json_encode($obj_line->Opportunity) .' <br>';
        
        // echo 'DiscountSellPrice :' . $a_orderItems1[0]->DiscountSellPrice .' <br>';
        // echo 'UnitTempSellPrice :' . $a_orderItems1[0]->UnitTempSellPrice .' <br>';

    }
    return;

  }


  public function setPickRelease()
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
        $indiceAccion      = 'accion';
        $indiceUsuario     = 'usuario';
        $indiceOrderDetail = "order_detail";

        //Verficación de que el array tiene las llaves minimas solicitud
        if (!array_key_exists($indiceAccion , $array_data) and
            !array_key_exists($indiceUsuario, $array_data) and
            !array_key_exists($indiceOrderDetail, $array_data))
        {
          $response = $this->responseError(3);
          $this->sendResponse($response);
        }

        //Verificación de Usuario
        if ($array_data[$indiceUsuario] != self::USER)
        {
          $response = $this->responseError(5);
          $this->sendResponse($response);
        }

        //Verificación de Método Invocado
        if ($array_data[$indiceAccion] != __FUNCTION__ )
        {
          $response = $this->responseError(6);
          $this->sendResponse($response);
        }

        if (is_array($array_data[$indiceOrderDetail]))
        {
          $a_order       = $array_data[$indiceOrderDetail];
          $refNumber     = $a_order['ref_number_order'];
          $omNumberOrder = $a_order['order_number_om'];
          $a_infoitems   = $a_order['list_products'];

          //Items no son array
          if (!is_array($a_infoitems))
          {
            $response = $this->responseError(3);
            $this->sendResponse($response);
          }

          //Sin items
          if (count($a_infoitems) <= 0)
          {
            $response = $this->responseError(7);
            $this->sendResponse($response);
          }

          //Numero de referencia no es String
          if (!is_string($refNumber))
          {
            $response = $this->responseError(10);
            $this->sendResponse($response);
          }

          $result = $this->TicketReparation->reservationItems($refNumber, $omNumberOrder,  $a_infoitems);

          //if ($result === true)
          if ($result !== false)
          {
            $a_response    = array("resultado" => true, "respuesta" => array("glosa"        => "Pick release registrado con éxito",
                                                                             "order_detail" => array("order_number_om" => $omNumberOrder,
                                                                                                     "list_new_lines"  => $result
                                                                                                    )

                                                                             ));
            $json_response = json_encode($a_response);
            $this->sendResponse($json_response);
          }
          else {
            $response = $this->responseError(4, $this->TicketReparation->getLastError());
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
        $response = $this->responseError(1);
        $this->sendResponse($response);
      }

    }

    public function ConfirmShipping()
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
        $indiceAccion      = 'accion';
        $indiceUsuario     = 'usuario';
        $indiceOrderDetail = "order_detail";

        //Verficación de que el array tiene las llaves minimas solicitud
        if (!array_key_exists($indiceAccion , $array_data) and
            !array_key_exists($indiceUsuario, $array_data) and
            !array_key_exists($indiceOrderDetail  , $array_data))
        {
          $response = $this->responseError(3);
          $this->sendResponse($response);
          //$this->sendResponse($response .  " LLaves ".  );
        }

        //Verificación de Usuario
        if ($array_data[$indiceUsuario] != self::USER)
        {
          $response = $this->responseError(5);
          $this->sendResponse($response);
        }

        //Verificación de Método Invocado
        if ($array_data[$indiceAccion] != __FUNCTION__ )
        {
          $response = $this->responseError(6);
          $this->sendResponse($response);
        }

        if (is_array($array_data[$indiceOrderDetail]))
        {
          $a_order       = $array_data[$indiceOrderDetail];
          $refNumber     = $a_order['ref_number_order'];
          $omNumberOrder = $a_order['order_number_om'];
          $guideDispatch = $a_order['guide_dispastch'];

          $a_infoitems   = $a_order['list_products'];
          $confirmed     = $a_order['confirmed'];

          //Verificación de Keys
          //if (is_array($a_infoitems)  and array_key_exists('line_id', $a_infoitems) and array_key_exists('ordered_quantity', $a_infoitems))
          if (!is_array($a_infoitems))
          {
            $response = $this->responseError(3);
            $this->sendResponse($response);
          }
          //verificación de Array
          if (count($a_infoitems) <= 0)
          {
            $response = $this->responseError(7);
            $this->sendResponse($response);
          }

          if (!is_string($refNumber) and $confirmed == true)
          {
            $response = $this->responseError(10);
            $this->sendResponse($response);
          }

          if($omNumberOrder=='1171137' )
          {
            $a_response    = array("resultado" => true, "respuesta" => array("glosa" => "Se ha confirmado la orden con exito. guia [NNN]"));
            $json_response = json_encode($a_response);
            $this->sendResponse($json_response);
          }
          if ($confirmed == false)
          {
            $result = $this->TicketReparation->cancelOrder($refNumber);
            if ($result === true)
            {
              $a_response    = array("resultado" => true, "respuesta" => array("glosa" => "La orden ha sido cancelado"));
              $json_response = json_encode($a_response);
              $this->sendResponse($json_response);
            }
            else {
              $response = $this->responseError(4, $this->TicketReparation->getLastError());
              $this->sendResponse($response);
            }
          }
          else if ($confirmed == true)
          {
            $result = $this->TicketReparation->confirmItems($refNumber, $a_infoitems, $guideDispatch);
            if ($result === true)
            {
              $a_response    = array("resultado" => true, "respuesta" => array("glosa" => "Se ha confirmado la orden con exito. guia [" . $guideDispatch . "]"));
              $json_response = json_encode($a_response);
              $this->sendResponse($json_response);
            }
            else {
              $response = $this->responseError(4, $this->TicketReparation->getLastError());
              $this->sendResponse($response);
            }
          }

        }
        else
        {
          $response = $this->responseError(1);
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
            //$data_decode = utf8_encode($data_decode);
            return $data_decode;
        }
        return false;
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
                $response =  array('Error' => 7, 'Glosa' => 'Solicitud sin items');
                break;
            case 8:
                $response =  array('Error' => 8, 'Glosa' => 'ID de ticket desconocido o no presente en Oracle RightNow');
                break;
            case 9:
                $response =  array('Error' => 9, 'Glosa' => 'ID de ticket no valido, no se encuentra en estado previo requerido');
                break;
            case 10:
                $response =  array('Error' => 10, 'Glosa' => 'Numero de referencia debe ser de tipo String');
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

    public function simulateOMresponse($encrypt = true)
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
        $indiceAccion      = 'accion';
        $indiceUsuario     = 'usuario';
        $indiceOrderDetail = "order_detail";
        //Verificación de Usuario
        if ($array_data[$indiceUsuario] != "Integer")
        {
          $response = $this->responseError(5);
          $this->sendResponse($response);
        }

        //Verificación de Método Invocado
        if ($array_data[$indiceAccion] != "setOrderOM" )
        {
          $response = $this->responseError(6);
          $this->sendResponse($response);
        }

        //Verficación de que el array tiene las llaves minimas solicitud
        if (!array_key_exists($indiceAccion , $array_data) and
            !array_key_exists($indiceUsuario, $array_data) and
            !array_key_exists($indiceOrderDetail  , $array_data))
        {
          $response = $this->responseError(3);
          $this->sendResponse($response);
        }

        if (is_array($array_data[$indiceOrderDetail]))
        {
          $a_order                = $array_data[$indiceOrderDetail];
          $refNumber              = $a_order['ref_number_order'];
          $fatherTicket           = $a_order['ref_number_ticket'];
          $clientRut              = $a_order['client_rut'];
          $typeOrder              = $a_order['type_order'];
          $hh                     = $a_order['hh'];
          $shippingInstructions   = $a_order['shipping_instructions'];
          $a_infoitems            = $a_order['list_products'];

          //Verificación de Keys
          if (is_array($a_infoitems)  and array_key_exists('line_id', $a_infoitems) and array_key_exists('ordered_quantity', $a_infoitems)
          and array_key_exists('Inventory_item_id', $a_infoitems) and array_key_exists('line_type_id', $a_infoitems) )
          {
            $response = $this->responseError(3);
            $this->sendResponse($response);
          }
          //verificación de Array
          if (count($a_infoitems) <= 0)
          {
            $response = $this->responseError(7);
            $this->sendResponse($response);
          }

          if (!is_string($refNumber) or !is_string($fatherTicket) or !is_numeric($hh) or !is_string($clientRut))
          {
            $response = $this->responseError(10);
            $this->sendResponse($response);
          }

          $a_response['resultado']       = true;
          $response["order_number_OM"]   = time();


          if ($encrypt == true)
          {
            $response                = $this->blowfish->encrypt(json_encode($response), self::KEY_BLOWFISH, 10, 22, NULL); //encriptar blowfish
            $a_response['respuesta'] = base64_encode($response);
          }
          else
            $a_response['respuesta'] = $response;

          $responseEncode  = json_encode($a_response);
          $this->sendResponse($responseEncode);

        }
        else
        {
          $response = $this->responseError(3);
          $this->sendResponse($response);
        }

      }
      else {
        $response = $this->responseError(3); //Error de estructura
        $this->sendResponse($response);
      }

    }

    public function refundShipping()
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
        $indiceAccion      = 'accion';
        $indiceUsuario     = 'usuario';
        $indiceOrderDetail = "order_detail";

        //Verficación de que el array tiene las llaves minimas solicitud
        if (!array_key_exists($indiceAccion , $array_data) and
            !array_key_exists($indiceUsuario, $array_data) and
            !array_key_exists($indiceOrderDetail  , $array_data))
        {
          $response = $this->responseError(3);
          $this->sendResponse($response);
        }

        //Verificación de Usuario
        if ($array_data[$indiceUsuario] != self::USER)
        {
          $response = $this->responseError(5);
          $this->sendResponse($response);
        }

        //Verificación de Método Invocado
        if ($array_data[$indiceAccion] != __FUNCTION__ )
        {
          $response = $this->responseError(6);
          $this->sendResponse($response);
        }

        if (is_array($array_data[$indiceOrderDetail]))
        {
          $a_order          = $array_data[$indiceOrderDetail];
          $omNumberOrder    = $a_order['order_number_om'];
          $omNumberOrderDev = $a_order['order_number_om_dev'];
          $a_infoitems      = $a_order['list_products'];

          if (!is_array($a_infoitems))
          {
            $response = $this->responseError(3);
            $this->sendResponse($response);
          }
          //verificación de Array
          if (count($a_infoitems) <= 0)
          {
            $response = $this->responseError(7);
            $this->sendResponse($response);
          }

          if (!is_string($refNumber) and $confirmed == true)
          {
            $response = $this->responseError(10);
            $this->sendResponse($response);
          }

          $result = $this->TicketDevolution->create($omNumberOrder, $a_infoitems, $omNumberOrderDev);
          if ($result != false)
          {
            $a_response    = array("resultado" => true, "respuesta" => array("glosa" => "Se ha registrado la orden de devolución con exito"));
            $json_response = json_encode($a_response);
            $this->sendResponse($json_response);
          }
          else {
            $response = $this->responseError(4, $this->TicketDevolution->getLastError());
            $this->sendResponse($response);
          }

        }
        else
        {
          $response = $this->responseError(1);
          $this->sendResponse($response);
        }
      }
      else
      {
        $response = $this->responseError(1);
        $this->sendResponse($response);
      }
    }


    public function testNewField()
    {
  
      
      $this->load->model('custom/ws/OpportunityModel');
      //$obj_line=RNCPHP\OP\OrderItems::find("Opportunity.ID = 23445    and Enabled = 1");
      //$obj_line=RNCPHP\OP\OrderItems::find("Opportunity.ID = {$op_id} and Enabled = 1");
      //$opportunity = RNCPHP\Opportunity::fetch(23445);
      $obj_line = $this->OpportunityModel->getItems(23445);
      echo "<br>";
      var_dump($obj_line[0]->DiscountLine);
      echo "<br>---------------<br>";
      foreach($obj_line as $key => $line)
      {

        echo  json_encode($line) .'<br>';
        $tempValue      = $line->UnitTempSellPrice  * $line->QuantitySelected;
        $discountPrice  = ($tempValue * $line->Discount)/100;
        echo  "tempValue = ".$tempValue .'<br>';
        echo  "discountPrice = ".$discountPrice .'<br>';
        echo  "Discount = ".$line->Discount .'<br>';
        echo  "DiscountSellPrice = ".$line->DiscountSellPrice .'<br>';
        echo  "DiscountLine = ".$line->DiscountLine;
  
      }
    }

}