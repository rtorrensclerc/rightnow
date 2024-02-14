<?php

namespace Custom\Controllers;

use RightNow\Connect\v1_3 as RNCPHP;

class Supplier extends \RightNow\Controllers\Base
{
 protected $_typeFormat = 'json';
 public function __construct()
 {
  parent::__construct();

  $this->load->model("custom/Supplier");
  $this->load->model('custom/Contact');
  $this->load->model('custom/Organization');
  $this->load->model('custom/IncidentGeneral');
  $this->load->model('custom/GeneralServices');

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
 private function __getdataPOST()
 {
  $data = trim($_POST['data']);
  if (!empty($data)) {
   $data_decode = base64_decode($data);
   //$data = utf8_encode($data);
   return $data_decode;
  }
  return false;
 }

 public function getPOST()
 {
  $entityBody = file_get_contents('php://input');
  $this->sendResponse($entityBody);
 }
 public function getTodo()
 {
  $data = trim($_POST['data-raw']);
  return $data;
 }
 public function echoTest()
 {
  //$incident1=RNCPHP\Incident::fetch('200527-000020');

  $z = array('status' => false, 'ref_no' => $refNo, array('a' => 1));

  $s = json_encode($z);
  $this->sendResponse($s);
  //$_SERVER['REQUEST_URI']
  //$this->sendResponse($data);
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


    public function TestgetSuggested($asset, $cont1_hh, $cont2_hh, $quantityBlack, $quantityColor,$quantityCyan, $quantityYellow, $quantityMagenta,$Actual,$Ultimo)
    {
    
      try
      {
        //$product = RNCPHP\DOS\Product::fetch($asset->CustomFields->DOS->Product->ID); //Se Obtiene el objeto producto(Equipo), asociado a la HH
        if (is_object ($asset->CustomFields->DOS->Product))
        {
          
          //Se verfica que exista un producto
          $productId =   $asset->CustomFields->DOS->Product->ID;
          if (empty($productId))
          {
            $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_HH_WITHOUT_DEVICE), $asset->ID);
            $this->error['numberID'] = 2;
            return false;
          }

          $counterBN         = $cont1_hh;
          $counterColor      = $cont2_hh;
         

          //Se buscan los Insumos asociados al Equipo
          // TODO:rtorrens , debemos buscar una manera de no usar 2 o mas items del mismo tipo, por ejemplo. solo elegir el mas popular o 
          // el items con mas stock. 
          $a_suppliers       = RNCPHP\OP\SuppliersRelated::find("Product.ID = {$productId} and (EnabledSupplierRequest = 1 or EnabledSupplierRequest is null)");

          
          
          $quantitySuppliers = count($a_suppliers);
          echo "estamos <br>" . json_encode($a_suppliers) . "<br>" ;
          if ($quantitySuppliers > 0)
          {
            //Se buscan las ultimas solicitudes creadas para la HH en particular
            $lastSuppliersIncident      = RNCPHP\Incident::first("Asset.ID = {$asset->ID} and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont1_hh != 0 order by ClosedTime DESC");
            $lastSuppliersColorIncident = RNCPHP\Incident::first("Asset.ID = {$asset->ID} and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont2_hh != 0 order by ClosedTime DESC");
            $a_response = array();
            $a_response['supplier'] = array();
            $a_response['message'] = '';
            $a_response['message_black'] = '';
            $a_response['message_color'] = '';

          
            echo "lastSuppliersIncident [" . json_encode($lastSuppliersIncident) . "]<br>" ;
            echo "lastSuppliersColorIncident [" . json_encode($lastSuppliersColorIncident) . "]<br>" ;
            if (empty($lastSuppliersIncident) and empty($lastSuppliersColorIncident))
            {
              
              $a_response['message'] = getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_SUGGEST_MIN);
              foreach ($a_suppliers as $key => $supplier_tmp)
              {
             
                $supplier=$supplier_tmp->Supplier;

                $a_TempResponse['supplier_id']        = $supplier->ID;
                $a_TempResponse['quantity_suggested'] = 0;
               
                switch($supplier->InputCartridgeType->ID)
                {
                  case 1:
                    $a_TempResponse['quantity']           = $quantityCyan;
                    break;
                  case 2:
                    $a_TempResponse['quantity']           = $quantityYellow;
                    break;
                  case 3:
                    $a_TempResponse['quantity']           = $quantityMagenta;
                    break;
                  case 4:
                    
                    $a_TempResponse['quantity']           = $quantityBlack;
                    break;
                  case 5:
                    $a_TempResponse['quantity']           = $quantityBlack;
                    break;
                  default:
                  $a_TempResponse['quantity']           = 0;
                }
                $a_TempResponse['toner_type']         = $supplier->InputCartridgeType->ID;
                $a_response['supplier'][]             = $a_TempResponse;
              }

              return $a_response;
            }
            else
            {
              
              $founded       = false;
              $itemSuggested = null;
              $a_colorItems  = array();


              $a_colorItems  =  $a_suppliers;
              //echo "estamos <br>" . json_encode($a_colorItems) . "<br>" ;
              //Inicio logica sugerido para Toner 
              if (!empty($lastSuppliersIncident) or !empty($lastSuppliersColorIncident))
              {
              
                $rendimientoColorReal = 0;
                foreach ($a_colorItems as $supplier_tmp)
                {
                  $supplier=$supplier_tmp->Supplier;
                  echo "estamos --------->> " . json_encode($supplier->TeoricYieldToner) . "<br>" ;
                  echo "DATOS " . json_encode($supplier) . "<br>" ;
                  
                  //Rendimiento real es igual a la suma de todos los rendimientos
                  if($supplier->InputCartridgeType->TonerType<>'Black')
                    {
                      $rendimientoColorReal += $supplier->TeoricYieldToner;
                     
                      
                    }
                    else
                    {
                      $rendimientoBNReal = $supplier->TeoricYieldToner; 
                    
                     
                    }
                }
              
                foreach ($a_colorItems as $supplier_tmp)
                { 
                  $supplier=$supplier_tmp->Supplier;
                      //Sugerido consumo
                     
                      if($supplier->InputCartridgeType->TonerType=='Black')
                      {
                       
                        $consumption    = $counterBN - $lastSuppliersIncident->CustomFields->c->cont1_hh;
                        $sugerido  = $this->Testcalculo_percent($consumption,$rendimientoBNReal,$supplier,$counterBN,$lastSuppliersIncident->CustomFields->c->cont1_hh);
                        echo $consumption  .'-' .json_encode($sugerido) . '<br>';
                      }
                      else
                      {
                       
                        $consumption    = $counterColor + $counterBN - $lastSuppliersIncident->CustomFields->c->cont2_hh - $lastSuppliersIncident->CustomFields->c->cont1_hh;
                        
                        
                        //$counterColor - $lastSuppliersIncident->CustomFields->c->cont2_hh;
                        $sugerido  = $this->Testcalculo_percent($consumption,$rendimientoColorReal,$supplier,$counterColor,$lastSuppliersIncident->CustomFields->c->cont2_hh);
                      }

                      $a_response['message_color'] =$a_response['message_color']  . '-<br>' . $sugerido['message_color'];
                     
                      $a_TempResponse['supplier_id']        = $supplier->ID ;
                      $a_TempResponse['quantity_suggested'] = $sugerido['quantity_suggested'];
                      switch($supplier->InputCartridgeType->ID)
                      {
                        case 1:
                          $a_TempResponse['quantity']           = $quantityCyan;
                          break;
                        case 2:
                          $a_TempResponse['quantity']           = $quantityYellow;
                          break;
                        case 3:
                          $a_TempResponse['quantity']           = $quantityMagenta;
                          break;
                        case 4:
                          $a_TempResponse['quantity']           = $quantityBlack;
                          break;
                        case 5:
                          $a_TempResponse['quantity']           = $quantityBlack;
                          $a_TempResponse['quantity_suggested']=0;
                          break;
                        default:
                        $a_TempResponse['quantity']           = 0;
                      }
                      
                      $a_TempResponse['toner_type']         = $sugerido['toner_type'];
                      $a_TempResponse['Consumption']        = $sugerido['Consumption'];
                    
                     
                      $a_response['supplier'][]             = $a_TempResponse;
                }
              }
              else
              {
                $a_response['message_color'] = getMessageBase(CUSTOM_MSG_SUPPLIER_MODEL_MIN_COLOR);

                //Todos los demas se marcan en 0
                foreach ($a_colorItems as $supplier)
                {
                  $a_TempResponse['supplier_id']        = $supplier->ID;
                  $a_TempResponse['quantity_suggested'] = 0;
                  $a_TempResponse['quantity']           = $quantityColor;
                  $a_TempResponse['toner_type']         = $supplier->InputCartridgeType->ID;
                  $a_TempResponse['Consumption']        = 0;
                  $a_response['supplier'][]             = $a_TempResponse;
                }
              }
              return $a_response;
            }
          }
          else
          {
            $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_NO_SUPPLY_TO_DEVICE), $productId);
            $this->error['numberID'] = 2;
            return false;
          }
        }
        else
        {
          $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_HH_NO_DEVICE), $asset->Serial);
          $this->error['numberID'] = 2;
          return false;
        }

      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_ERROR_VAR_VAR), $err->getCode(), $err->getMessage());
        $this->error['numberID'] = 1;
        return false;
      }
    }

    public function requestTicket($incidentId, $a_items)
    {
      try
      {
        RNCPHP\ConnectAPI::commit();
        $incident = RNCPHP\Incident::fetch($incidentId);
        foreach ($a_items as $item)
        {
          $line                   = RNCPHP\OP\OrderItems::fetch($item['id']);
          $line->QuantitySelected = $item['quantity_selected'];
          $line->Save();
        }
   
        $incident->StatusWithType->Status->ID = 178;   //enviado
        $incident->Save();
        return true;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        RNCPHP\ConnectAPI::rollback();
        $this->error['message']  = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_ERROR_VAR_VAR), $err->getCode(), $err->getMessage());
        $this->error['numberID'] = 1;
        return false;
      }
    }



    public function Testcalculo_percent($consumption,$rendimientoReal,$supplier)
    {
      $resp = array();
      if($supplier->TeoricYieldToner>0)
      {
        $preSuggested   = $consumption / $supplier->TeoricYieldToner;
      }
      else
      {
        $preSuggested =0;
      }
      $Consumption    = $preSuggested*100;
      //Porcentaje
      $percentage     = $supplier->Threshold / 100;
      //Sugerido
      $suggested      = $preSuggested + $percentage;
      $ceilSuggested  = round($suggested); /* Aproxima ah arroba sobre 0.5   */
      //echo "->[" . json_encode($supplier->TrueYieldToner) . "-" .   $consumption ."-" .   $Consumption ."]<br>";
         
            $rendimientoReal=$supplier->TeoricYieldToner;
       
            if ($consumption < 0)
            {
              $resp['message_color']  =  sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_CONSUMPTION_COLOR_NEGATIVE), $consumption, $lastSuppliersIncident->CustomFields->c->cont2_hh, $counterColor);
            
              if($supplier->TeoricYieldToner>0)
                {
                  $preSuggested   = $consumption / $supplier->TeoricYieldToner;
                }
                else
                {
                  $preSuggested =0;
                }
                $Consumption    = $preSuggested*100;
                //Porcentaje
                $percentage     = $supplier->Threshold / 100;
                //Sugerido
                $suggested      = $preSuggested + $percentage;

                $resp['supplier_id']        = $supplier->ID;
                $resp['quantity_suggested'] = 0;
                $resp['quantity']           = 0;
                $resp['toner_type']         = $supplier->InputCartridgeType->ID;
                $resp['Consumption']           = $Consumption;
              
              
            }
            else
            {
              
              echo "Rendimiento Real" . $rendimientoReal . "<br>";
              if ($rendimientoReal <= 0)
              {
                //Rendimiento no medible por lo que sugiere lo minimo
                //, $supplier->InputCartridgeType->TonerType
         
                $resp['message_color']  =  sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_COLOR_ITEM_MIN),$rendimientoReal);
                
                if($supplier->TeoricYieldToner>0)
                {
                  $preSuggested   = $consumption / $supplier->TeoricYieldToner;
                }
                else
                {
                  $preSuggested =0;
                }
                  $Consumption    = $preSuggested*100;
                  //Porcentaje
                  $percentage     = $supplier->Threshold / 100;
                  //Sugerido
                  $suggested      = $preSuggested + $percentage;
                  $resp['supplier_id']        = $supplier->ID;
                  $resp['quantity_suggested'] = 0;
                  $resp['quantity']           = 0;
                  $resp['toner_type']         = $supplier->InputCartridgeType->ID;
                  $resp['Consumption']           = $Consumption;
                  
                
              }
              else
              {
                //Pre sugerido
                $preSuggested   = $consumption / $rendimientoReal;
                //Sugerido redondeado hacia abajo
                //$ceilSuggested  = floor($preSuggested);


                $ceilSuggested  = round($preSuggested);


                if ($ceilSuggested > 0)
                {
                  $resp['message_color']  =  sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_COLOR_BACKSTORY), $ceilSuggested);
                 
                  if($supplier->TeoricYieldToner>0)
                  {
                    //$preSuggested   = $consumption / $supplier->TrueYieldToner;
                    $preSuggested   = $consumption / $supplier->TeoricYieldToner;
                    
                  }
                  else
                  {
                    $preSuggested =0;
                  }
                  $Consumption    = $preSuggested*100;
                  //Porcentaje
                  $percentage     = $supplier->Threshold / 100;
                  //Sugerido
                  $suggested      = $preSuggested + $percentage;
                  $ceilSuggested  = round($preSuggested);
                    $resp['supplier_id']        = $supplier->ID;
                    $resp['quantity_suggested'] = $ceilSuggested;
                    $resp['quantity']           = $quantityColor;
                    $resp['toner_type']         = $supplier->InputCartridgeType->ID;
                    $resp['Consumption']           = $Consumption;
                 
                  
                }
                else
                {
      
                  //$supplier->InputCartridgeType->TonerType,
                  $resp['message_color'] = sprintf(getMessageBase(CUSTOM_MSG_SUPPLY_MODEL_NEGATIVE_VALUE_COLOR_MIN), $ceilSuggested);
                
                  
                  if($supplier->TeoricYieldToner>0)
                  {
                    $preSuggested   = $consumption / $supplier->TeoricYieldToner;
                  }
                  else
                  {
                    $preSuggested =0;
                  }
                    $Consumption    = $preSuggested*100;
                    //Porcentaje
                    $percentage     = $supplier->Threshold / 100;
                    //Sugerido
                    $suggested      = $preSuggested + $percentage;

                    $resp['supplier_id']        = $supplier->ID;
                    $resp['quantity_suggested'] = 0;
                    $resp['quantity']           = $quantityColor;
                    $resp['toner_type']         = $supplier->InputCartridgeType->ID;
                    $resp['Consumption']           = $Consumption;
                  
                }
              }
            }

      return $resp;
      
    }

public function testsuggested()
{

    $obj_hh = RNCPHP\Asset::first("SerialNumber = '1789522'");
    $a_response_suggested = $this->TestgetSuggested($obj_hh,259772 , 0, 1, 0, 0, 0, 0,0,0);

    echo json_encode($a_response_suggested);
}


private function BuscaCreaContact($mail,$name,$phone,$phone2,$org_id)
{
  //echo  $mail . '-'.  $name . '-'.$phone. '-'.$phone2 . '<br>';
  $res = RNCPHP\ROQL::queryObject("SELECT Contact FROM Contact where  login='" . $mail . "'"  )->next();;

    if($contact = $res->next()) {
          //printf("Here is the contact ID: %d <br />", json_encode($contact->ID));
          $id_Contacto=$contact->ID;
    }
    else 
    {
      $record = explode(' ',$name);
      
      $contact = new RNCPHP\Contact();
      $contact->Login = $mail;
      $contact->Name = new RNCPHP\PersonName();
      $contact->Emails = new RNCPHP\EmailArray();
      $contact->Emails[0] = new RNCPHP\Email();
      $contact->Emails[0]->AddressType=new RNCPHP\NamedIDOptList();
      $contact->Emails[0]->AddressType->LookupName = "Correo electrónico - Principal";
      $contact->Emails[0]->Address = $mail;

      
      
      $contact->Name->First =$record[0] ;
      $contact->Name->Last = $record[1] ;
      
      //printf("Se Creara Contacto "  .$record[0] . "-" . $record[1] .  "<br>");
      //printf("Telefono "  .$phone .  "<br>");
      $telefono =str_replace(' ', '', $phone);
      $telefono =str_replace('+', '', $telefono);
      $telefono =str_replace('(', '', $telefono);
      $telefono =str_replace(')', '', $telefono);
      $telefono =str_replace('-', '', $telefono);
      $telefono=trim($telefono,  "+  \t\n\r\0\x0B");
      //printf("Telefono "  .$telefono .  "<br>");

      $contact->Phones = new RNCPHP\PhoneArray();
      $contact->Phones[0] = new RNCPHP\Phone();
      $contact->Phones[0]->PhoneType = new RNCPHP\NamedIDOptList();
      $contact->Phones[0]->PhoneType->LookupName = 'Teléfono de oficina';
      $contact->Phones[0]->Number =  $telefono;

     
      //printf("Telefono "  .$phone2 .  "<br>");
      $telefono =str_replace(' ', '', $phone2);
      $telefono =str_replace('+', '', $telefono);
      $telefono =str_replace('(', '', $telefono);
      $telefono =str_replace(')', '', $telefono);
      $telefono =str_replace('-', '', $telefono);
      $telefono=trim($telefono,  "+  \t\n\r\0\x0B");
      //printf("Telefono "  .$telefono .  "<br>");

      $contact->Phones[1] = new RNCPHP\Phone();
      $contact->Phones[1]->PhoneType = new RNCPHP\NamedIDOptList();
      $contact->Phones[1]->PhoneType->LookupName = 'Teléfono móvil';
      $contact->Phones[1]->Number =  $telefono;
      $contact->Organization = RNCPHP\Organization::fetch($org_id);

      $contact->save();
      $id_Contacto=$contact->ID;
    }
  
  return $id_Contacto;
}

private function searchContact($rut,$hh)
{
  $report_id = 102231 ;
  $filter_value= $rut;
  $filter_hh=$hh;
  $nro_referencias="";
  $id_azavala=19428;
  $id_Contacto=112972;  /** Contacto pro defecto Nube Print */

  if(!empty($rut))
  {
    $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
    $status_filter->Name = 'resource_id';
    $status_filter->Values = array( $filter_value  );
    $filters = new RNCPHP\AnalyticsReportSearchFilterArray;
    $filters[] = $status_filter;
    $status_filter= new RNCPHP\AnalyticsReportSearchFilter;
    $status_filter->Name = 'HH';
    $status_filter->Values = array( $filter_hh );
    $filters = new RNCPHP\AnalyticsReportSearchFilterArray;
    $filters[] = $status_filter;
    $ar= RNCPHP\AnalyticsReport::fetch( $report_id);
    $arr= $ar->run( 0, $filters );
  }
  
  
  if($arr)
  {
  for ( $i = $arr->count(); $i--; )
  {
     $row = $arr->next();
     if($row['id']==94586)
     {
      $id_Contacto==112972;
     }
     else
     {
      $id_Contacto=$row['id']; 
      break;
     }
     //$id_Contacto=94586;

    //echo "--->" . $id_Contacto . '<br>';
    
  }
  }
  if($id_Contacto==$id_azavala)
  {
    $id_Contacto=112972; /* Se asigna nuve print */
  }
  return $id_Contacto;
}


 public function CrearIncidenteInsumo()
 {

  $a_temp['errors'] = array();
  $a_lines          = array();
  $this->load->model("custom/ws/DatosHH");
  $json_data = file_get_contents('php://input');

/*
$json_data='
{
"SUBJECT" :"SOLICITUD DE INSUMOS  SDS TEST",
"RAZON":"113",
"HH": "2110734",
"TIPO_INCIDENTE": "24",
"SHIPPING_INSTRUCTIONS": "PRUEBA DE INSUMOS",
"Nombre": "rodrigo, Torrens",
"Correo": "rtorrensclerc@gmail.com",
"CONTADOR_BN":28475,
"CONTADOR_COLOR":0,
"BLACK":1,
"CYAN":0,
"MAGENTA":0,
"YELLOW":0
}';
*/

  //echo $json_data . '<br>';

  $data                       = json_decode($json_data);
  //echo json_encode($data). '<br>';
  $a_temp['errors']['status'] = 0;
  //$id_ticket='200520-000000';
  //$incident=RNCPHP\Incident::fetch($id_ticket);

  //$user=RNCPHP\Contact::fetch(104535);
  //echo json_encode($user->Emails[0]->Address);
  $users = RNCPHP\Contact::find("Login='" . $data->Correo . "'");


  //$user=$incident->PrimaryContact;
  $user = $users[0];
  //$this->sendResponse(json_encode($user->Organization));
  //echo json_encode($user) . '-' . $data->Correo;
  //echo json_encode($user);
  $a_address = array();
  if (!empty($user->Organization->ID)) {
   $a_directions = $this->Organization->getDirectionsByOrgId($user->Organization->ID);

   if (count($a_directions) > 0) {
    foreach ($a_directions as $direction) {
     $a_tempSelect['ID']   = $direction->ID;
     $a_tempSelect['name'] = $direction->dir_envio;
     $a_address[]          = $a_tempSelect;
    }
   }
  } else {
   //throw new \Exception("Su contacto no tiene organización asociada");
   $a_temp['errors']['error']  = "Nombre y Correo  " . $data->Correo . " no existe o no tiene organización asociada HH " . $data->HH;
   $a_temp['errors']['status'] = 17;
  }
  if ($a_temp['errors']['status'] == 0) {
   //echo json_encode($a_directions);
   //echo json_encode($data);
   $a_temp['hh']            = $data->HH;
   $a_temp['counter_black'] = $data->CONTADOR_BN;
   $a_temp['counter_color'] = $data->CONTADOR_COLOR;
   $a_temp['count_black']   = $data->BLACK;
   $a_temp['count_cyan']    = $data->CYAN;
   $a_temp['count_magenta'] = $data->MAGENTA;
   $a_temp['count_yellow']  = $data->YELLOW;
   $a_temp['SUBJECT']       = $data->SUBJECT;
   $a_temp['SHIPPING_INSTRUCTIONS']       = $data->SHIPPING_INSTRUCTIONS;
   



   if (!is_numeric($a_temp['hh'])) {
    $a_temp['errors']['error']  = "El valor de 'HH' no es numérico.  (" . $a_temp["hh"] . ")";
    $a_temp['errors']['status'] = 1;
    //echo json_encode($a_temp['errors']);
   }

   if (!is_numeric($a_temp['counter_black']) and !empty($a_temp['counter_black'])) {
    $a_temp['errors']['error']  = "El valor de 'contador 1' no es numérico.  (" . $a_temp["counter_black"] . ")";
    $a_temp['errors']['status'] = 2;
    $counter_black              = false;
    //echo json_encode($a_temp['errors']);
   } else {
    $counter_black = true;
   }

   if (!is_numeric($a_temp['counter_color']) and !empty($a_temp['counter_color'])) {
    $a_temp['errors']['error']  = "El valor de 'contador 2' no es numérico.  (" . $a_temp["counter_color"] . ")";
    $a_temp['errors']['status'] = 3;
    $counter_color              = false;
    //echo json_encode($a_temp['errors']);
   } else {
    $counter_color = true;
   }

   if (!is_numeric($a_temp['count_black']) and !empty($a_temp['count_black'])) {
    $a_temp['errors']['error']  = "El valor de 'Toner Negro' no es numérico.  (" . $a_temp["count_black"] . ")";
    $a_temp['errors']['status'] = 4;
    $count_black                = false;
    //echo json_encode($a_temp['errors']);
   } else {
    $count_black = true;
   }

   if (!is_numeric($a_temp['count_cyan']) and !empty($a_temp['count_cyan'])) {
    $a_temp['errors']['error']  = "El valor de 'Toner Cyan' no es numérico.  (" . $a_temp["count_cyan"] . ")";
    $a_temp['errors']['status'] = 5;
    $count_cyan                 = false;
    //echo json_encode($a_temp['errors']);
   } else {
    $count_cyan = true;
   }

   if (!is_numeric($a_temp['count_magenta']) and !empty($a_temp['count_magenta'])) {
    $a_temp['errors']['error']  = "El valor de 'Toner Magenta' no es numérico.  (" . $a_temp["count_magenta"] . ")";
    $a_temp['errors']['status'] = 6;
    $count_magenta              = false;
    //echo json_encode($a_temp['errors']);
   } else {
    $count_magenta = true;
   }

   if (!is_numeric($a_temp['count_yellow']) and !empty($a_temp['count_yellow'])) {
    $a_temp['errors']['error']  = "El valor de 'Toner Amarillo ' no es numérico.  (" . $a_temp["count_yellow"] . ")";
    $a_temp['errors']['status'] = 7;
    $count_yellow               = false;
    //echo json_encode($a_temp['errors']);
   } else {
    $count_yellow = true;
   }

   if ($counter_black === true && $counter_color === true) {
    $cc_black = (int)$a_temp['counter_black'];
    $cc_color = (int)$a_temp['counter_color'];

    if (($cc_black + $cc_color) < 1) {
     $a_temp['errors']['error']  = "Al menos uno de los contadores de B/N y Color deben ser mayores que cero.";
     $a_temp['errors']['status'] = 8;
     //echo json_encode($a_temp['errors']);
    }
   }

   if ($count_black === true && $count_cyan === true && $count_magenta === true && $count_yellow === true) {
    $c_black   = (int)$a_temp['count_black'];
    $c_cyan    = (int)$a_temp['count_cyan'];
    $c_magenta = (int)$a_temp['count_magenta'];
    $c_yellow  = (int)$a_temp['count_yellow'];
    if (($c_black + $c_cyan + $c_magenta + $c_yellow) < 1) {
     $a_temp['errors']['error']  = "Debe solicitar al menos un toner para continuar.";
     $a_temp['errors']['status'] = 9;
     //echo json_encode($a_temp['errors']);
    }
   }
   //echo 'HH' .$a_temp['hh'];
   $responseService = $this->DatosHH->getDatosHHInsumos($a_temp['hh']);

   //echo json_encode($responseService);

   if ($responseService === false) {
    // $a_temp['errors'][] = "HH no encontrada";
    $a_temp['errors']['error']  = $this->DatosHH->getLastError();
    $a_temp['errors']['status'] = 10;
    $a_errors[]                 = $a_temp;

   } else {
    $a_response_pre = json_decode($responseService, true);

    if (array_key_exists('respuesta', $a_response_pre) !== true) {
     $a_temp['errors']['error']  = "Respuesta no esperada desde servicio.";
     $a_temp['errors']['status'] = 11;
     //echo json_encode($a_temp['errors']);
     $a_errors[] = $a_temp;

    }

    $a_response = $a_response_pre["respuesta"];

    if ($a_response["resultado"] !== "OK") {
     $a_temp['errors']['error']  = "Número de HH no identificado en Dimacofi HH " . $data->HH;
     $a_temp['errors']['status'] = 12;
     //echo json_encode($a_temp['errors']);
    }

    // Logica para validar que pertenece a la organización
    $dirId = $a_response["Direccion"]["ID_direccion"];

    $c_org_id = $user->Organization->ID;
    //$this->sendResponse(json_encode($user->Organization).'-' .$c_org_id);

    $obj_dir = $this->Organization->getDirectionByEbsId($dirId);
    //$this->sendResponse($obj_dir->Organization->ID.'-' .$c_org_id);
    if ($obj_dir != false) {
     //echo "ID ORG Contacto ". $c_org_id ."ID Dirección ". $obj_dir->ID ." ID ORG HH ". $obj_dir->Organization->ID;
   /*  if ($obj_dir->Organization->ID !== $c_org_id) {
      $a_temp['errors']['error']  = "La HH no figura a la organización asociada a su contrato. HH " . $data->HH;
      $a_temp['errors']['status'] = 13;
      $a_temp['errors']['id']     = $c_org_id;
      $a_temp['errors']['dirId']  = $dirId;
*/
      $zzz=0;
      //echo json_encode($a_temp['errors']);

     }
     else {
     $a_temp['errors']['error']  = "La Dirección asociada no figura en el sistema, favor comunicarse con los administradores  HH " . $data->HH ;
     $a_temp['errors']['status'] = 14;
     $a_temp['errors']['id']     = $c_org_id;
     //echo json_encode($a_temp['errors']);
    }

    if (count($a_temp['errors']) > 0) {
     $a_errors[] = $a_temp;
    }

    //exitoso
    $a_temp['id_dir_selected'] = $dirId;
    $a_temp['info_service']    = $a_response;
    $a_no_errors[]             = $a_temp;

   }

   $response            = new \stdClass;
   $response->success   = true;
   $response->address   = $a_address;
   $response->errors    = $a_errors;
   $response->no_errors = $a_no_errors;
   if (count($a_errors) < 1) {
    $response->message = "Solicitud de insumos analizada sin errores.";

   } else {
    $response->message = "Solicitud de insumos analizada con errores, favor revisar la tabla.";

   }
   $asset                                      = RNCPHP\Asset::first( "SerialNumber = '".$data->HH ."'");
   $fincidents = RNCPHP\Incident::find("Disposition.ID = 24 and StatusWithType.Status.ID not in(104,148,149,2,196)  and Asset.ID = {$asset->ID}");
   
   if (count($fincidents) > 0)
   {
    $a_temp['errors']['error']  = "HH " . $data->HH .' tiene ticket paralelos ' . $fincidents[0]->ReferenceNumber;
    $a_temp['errors']['status'] = 15;
   }

  

   //TODO: Verificar HH existe, si no crearla
   //TODO: Verificar Relación de HH - Equipo- Insumos con Insumos, si no esta crearla
   $a_infohh['hh']        = $a_response["ID_HH"];
   $a_infohh['brand_hh']  = $a_response["Marca"];
   $a_infohh['model_hh']  = $a_response["Modelo"];
   $a_infohh['serial_hh'] = $a_response["Serie"];
   $inventoryItemId       = $a_response["inventory_item_id"];
   $a_suppliers           = $a_response["suppliers"];

   $obj_hh = RNCPHP\Asset::first("SerialNumber = '" . $a_infohh['hh'] . "'");

   //$obj_hh  = $this->Supplier->updateAsset($a_infohh, $obj_dir, $obj_contact, $inventoryItemId, $a_suppliers);


   if ($obj_hh !== false and $a_temp['errors']['status'] == 0) {
    $cont1_hh        = (int)$a_temp['counter_black'];
    $cont2_hh        = (int)$a_temp['counter_color'];
    $quantityBlack   = (int)$a_temp['count_black'];
    $quantityCyan    = (int)$a_temp['count_cyan'];
    $quantityYellow  = (int)$a_temp['count_yellow'];
    $quantityMagenta = (int)$a_temp['count_magenta'];
    $quantityColor   = $quantityCyan + $quantityYellow + $quantityMagenta;

    if ($obj_hh->CustomFields->DOS->Product->ID > 0) {

     $a_suppliers = RNCPHP\OP\SuppliersRelated::find("Product.ID = {$obj_hh->CustomFields->DOS->Product->ID} and (EnabledSupplierRequest = 1 or EnabledSupplierRequest is null)");
     //echo "Suppliers <br>" . json_encode($a_suppliers) . "<br>" ;

     //echo "Suppliers <br>" . json_encode($a_suppliers[0]->Product) . "<br>" ;
     //echo "Suppliers <br>" . json_encode($a_suppliers[1]->Product) . "<br>" ;
     $quantitySuppliers = count($a_suppliers);
     //echo "quantitySuppliers <br>" . $quantitySuppliers. "<br>" ;
     //$lastSuppliersIncident      = RNCPHP\Incident::first("Asset.ID = {$obj_hh->ID} and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont1_hh != 0 order by ClosedTime DESC");
     //$lastSuppliersColorIncident = RNCPHP\Incident::first("Asset.ID = {$obj_hh->ID} and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont2_hh != 0 order by ClosedTime DESC");
     //echo "lastSuppliersIncident <br>" . json_encode($lastSuppliersIncident) . "<br>" ;
     //echo "lastSuppliersColorIncident <br>" . json_encode($lastSuppliersColorIncident) . "<br>" ;

     //echo $quantityBlack .'-'. $quantityColor.'-'.$quantityCyan.'-'.$quantityYellow.'-'.$quantityMagenta ;
     $a_response_suggested = $this->Supplier->getSuggested($obj_hh, $cont1_hh, $cont2_hh, $quantityBlack, $quantityColor, $quantityCyan, $quantityYellow, $quantityMagenta,0,0);
     //echo "a_response_suggested <br>" . json_encode($a_response_suggested) . "<br>" ;
    

    } else {

     $a_temp['errors']['error']  = "No existe insumos relacionados con  HH " . $data->HH;
     $a_temp['errors']['status'] = 20;
     //echo json_encode($a_temp['errors']);
     $a_errors[] = $a_temp;
    }
   
    if ($a_response_suggested === false) {
      $a_temp['errors']['error']  = "No existe insumos relacionados con HH: " . $data->HH;
      $a_temp['errors']['status'] = 22;

    } else {
     $message             = $a_response_suggested['message'];
     $message_black       = $a_response_suggested['message_black'];
     $message_color       = $a_response_suggested['message_color'];
     $a_supplierSuggested = $a_response_suggested['supplier'];
    }
    if ($a_supplierSuggested > 0) {
     $isBlack   = 0;
     $isCyan    = 0;
     $isYellow  = 0;
     $isMagenta = 0;
     $isNot     = 0;

     foreach ($a_supplierSuggested as $supplier) {
      //TODO: Ronny: Intervenir para seter el valor correccto según tipo de toner
      // TODO: el valor toner_type viene vacio desde el objeto y por ende no se estan seteando los valores que debe tener cada equipo de manera
      //generica, en cuanto pasa por el switch le asigna a todos la misma cantidad que tiene si cualquiera con un valor no = 0 ya que entra al default del switch case
      //se debe intervenir - bien haciendo carga masiva al objeto en cuetion = solucion definitiva o quitando la funcionalidad temporalmente para que solo funcione con los que viene != 0
      //de igual forma se debe analizar el codigo a fondo para obtener otras posibilidades de intervencion en el desarrollo sin que sea tan invasiva
      $tonerTypeId = $supplier['toner_type'];
      //echo 'Tonner' .json_encode($supplier)  . '<br>';
      //echo "<br>";
      switch ($tonerTypeId) {
        case 1: //Cyan
          if($isCyan == 0)
          {
            $resultCL = $this->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $quantityCyan,$supplier['Consumption']);
            $isCyan   = 1;
          }
          break;
        case 2: //Yellow
          if($isYellow==0)
          {
            $resultCL = $this->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $quantityYellow,$supplier['Consumption']);
            $isYellow = 1;
          }
          break;
        case 3: //Magenta
          if($isMagenta==0)
          {
            $resultCL = $this->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $quantityMagenta,$supplier['Consumption']);
            $isMagenta = 1;
          }
          
          break;
        case 4: //Black

        
          if($isBlack==0)
          {
            $resultCL = $this->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $quantityBlack,$supplier['Consumption']);
            $isBlack  = 1;
          }
       
          break;
        default:
          if($isNot ==0)
          {
            //$resultCL = $this->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $supplier['quantity'],$supplier['Consumption']);
            $isNot = 1;
          }
       
        break;
      }
      //echo json_encode($resultCL);
      if ($resultCL === false) {
       \RightNow\Connect\v1_3\ConnectAPI::rollback();
       
       $a_temp['errors']['status'] = 23;
       $a_temp['errors']['error']  = "Error creando linea  " . $data->HH;

      } else {
       $a_lines[] = $resultCL;
      }
     }

     //echo "LINEAS <br>";
     //echo json_encode($a_lines);
     //echo "LINEAS <br>";

     $a_infohh['contador_bn']    = $cont1_hh;
     $a_infohh['contador_color'] = $cont2_hh;

     //Datos de HH
     $a_infohh['client_covenant']   = $a_response['Convenio'];
     $a_infohh['client_blocked']    = $a_response['Direccion']['Bloqueado'];
     $a_infohh['contract_type']     = $a_response['TipoContrato'];
     $a_infohh['sla_hh_rsn']        = $a_response['sla_hh_rsn'];
     $a_infohh['delfos']            = $a_response['delfos'];
     $a_infohh['machine_serial']    = $a_response['inventory_item_id'];
     $a_infohh['supplier_covenant'] = $a_response['convenio_insumos'];
     $a_infohh['brackets_covenant'] = $a_response['convenio_corchetes'];

     /* esto no deveria estar aqui,  para este caso , no hay padre* */
     //$incident=RNCPHP\Incident::fetch('200115-000030');
     $result = false;
     //Verificar que no existe ticket paralelo

     //echo "CREA <br>";
     $result = $this->Supplier->createTicketMassive($user->ID, $obj_hh, $a_infohh, $obj_dir, $incident->ID);

     if ($result === false) {
      $a_temp['errors']['error']  = "Ya tiene un ticket de Insumos en curso HH " . $data->HH;
      $a_temp['errors']['status'] = 21;
     } else {
      //echo json_encode($result->ID);
      $incidentId = $result->ID;
      $incidents_id .= "," . $incidentId;

      foreach ($a_lines as $lineId) {
       $resultAL = $this->Supplier->assocLineToIncident($incidentId, $lineId);

       if ($resultAL === false) {
        \RightNow\Connect\v1_3\ConnectAPI::rollback();
        $a_temp['errors']['error']  = "Error al esociar línea a incidente " . $data->HH;
      $a_temp['errors']['status'] = 24;
        
       }
      }
     
      $result->StatusWithType->Status->ID = 178;
      $result->CustomFields->c->supply_reason->ID=260;
      $result->Subject                    = $a_temp['SUBJECT'];
      if($result->CustomFields->c->shipping_instructions=='DESPACHO POR MONITOREO PROACTIVO')
      {
        $phone='';
        if($result->PrimaryContact->Phones[0])
        {
          $phone=$result->PrimaryContact->Phones[0]->RawNumber;
        }
  
        $name='';
        if($result->PrimaryContact->LookupName)
        {
          $name=$result->PrimaryContact->LookupName;
        }
        $result->CustomFields->c->shipping_instructions= $name . ' ' .  $phone . ' ' . $result->CustomFields->c->shipping_instructions;
      }
      else
      {
        $result->CustomFields->c->shipping_instructions=$a_temp['SHIPPING_INSTRUCTIONS'];
      }

      
      $result->save();
      

     }
    }
   }
  }
  //echo json_encode($result->ID);
  $inc = $result->ID;

  //echo json_encode($a_lines);
  //echo json_encode($a_infohh);
  if ($inc) {
   $incident = RNCPHP\Incident::fetch($inc);
  } else {
   $incident = null;
  }
  //$incident1=RNCPHP\Incident::fetch(572208);
  //echo  "<br>";
  //echo json_encode($a_temp['errors']) . "<br>";
  //echo  "<br>";
  if (!empty($incident) and $a_temp['errors']['status'] == 0) {
   $respuesta = array('status' => "OK", 'ref_no' => $incident->ReferenceNumber, 'ID' => $incident->ID);
  } else {

   $respuesta = array('status' => "NOOK", 'ref_no' => $incident->ReferenceNumber, 'ID' => $incident->ID, 'Code' => $a_temp['errors']['status'], 'error' => $a_temp['errors']['error']);
  }
  //echo json_encode($respuesta) . "<br>";
  $this->sendResponse(json_encode($respuesta));
  // echo json_encode($incident1,3);
 }

 public function CrearSolicitudInsumoM()
 {

  $json_data='
  {"insumos":[
              {
                "SUBJECT" :"SOLICITUD DE INSUMOS NBP",
                "RAZON":"285",
                "HH": "2110734",
                "TIPO_INCIDENTE": "24",
                "SHIPPING_INSTRUCTIONS": "DESPACHO POR MONITOREO PROACTIVO",
                "CONTADOR_BN":95204,
                "CONTADOR_COLOR":0,
                "BLACK":1,
                "CYAN":0,
                "MAGENTA":0,
                "YELLOW":0,
                "MAIL":"rtorrenscleqrc@gmail.com",
                "CONTACT":null,
                "PHONE":"+56 9  8812 9798",
                "PHONE2":"+56 (9) 9434 7757"}
              ]
    }
  ';

  $insumos=json_decode($json_data);
  
    foreach ($insumos->insumos as $value)
    {
      
        $this->CrearSolicitudInsumo($value);
    }

   
 }

 public function CrearSolicitudInsumo($param)
 {

  $a_temp['errors'] = array();
  $a_lines          = array();
  $this->load->model("custom/ws/DatosHH");
  $json_data = file_get_contents('php://input');

  
/*
  $json_data='
  {
                "SUBJECT" :"SOLICITUD DE INSUMOS NBP",
                "RAZON":"285",
                "HH": "2110734",
                "TIPO_INCIDENTE": "24",
                "SHIPPING_INSTRUCTIONS": "DESPACHO POR MONITOREO PROACTIVO",
                "CONTADOR_BN":168059,
                "CONTADOR_COLOR":0,
                "BLACK":1,
                "CYAN":0,
                "MAGENTA":0,
                "YELLOW":0,
                "MAIL":"rtorrens2000@dimacofil.com",
                "CONTACT":"Ignacio torres",
                "PHONE":"+56 9  8812 9798",
                "PHONE2":"+56 9 3357 2544"}
  ';
  */

  if(!$json_data)
  {
    $data=$param;
    
  }
  else
  {
    $data                       = json_decode($json_data);
  
  }
  $hh = RNCPHP\Asset::first("SerialNumber = '" .$data->HH . "'");

  //echo "$data->BLACK $data->CYAN $data->YELLOW $data->MAGENTA"  . "<br>";
  $a_temp['errors']['status'] = 0;
  //$id_ticket='200520-000000';
  //$incident=RNCPHP\Incident::fetch($yid_ticket);
  
  //$hh = RNCPHP\Asset::first("CustomFields.DOS.Serial_Number = '" . $data->HH ."'" );
  
 


  //$RutStatusSAI=$this->GeneralServices->getOrganizationStatusbyRut($hh->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut);
 

  //echo json_encode("CustomFields.c.id_hh ='"  .  $hh->SerialNumber . "'" );

  // Tea datios de HH para saber si esta bloqueado o NO
  //echo 'HH' .$a_temp['hh'];
  //$responseService = $this->DatosHH->getDatosHHInsumos($a_temp['hh']);



  $err=0;
  if($hh->SerialNumber)
  {
    //$array_obj= RNCPHP\Incident::find( "CustomFields.c.id_hh ="  .  $hh->SerialNumber . " and StatusWithType.Status.ID not in(2,149,148) and Disposition.ID=24"    );
    //$array_obj= RNCPHP\Incident::find( "CustomFields.c.id_hh ="  .  2019535 . " and StatusWithType.Status.ID not in(2,149,148) and Disposition.ID=24"    );
    $array_obj= RNCPHP\Incident::find( "CustomFields.c.id_hh=". $hh->SerialNumber ." and StatusWithType.Status.ID not in(2,149,148,196,104) and Disposition.ID=24 "    );

    foreach($array_obj as $key => $value)
    {
      //echo " Ticket Anteriores  $value->ReferenceNumber  <br>";
      
      $array_products=RNCPHP\OP\OrderItems::find("Incident.ID = " . $value->ID. " and QuantitySelected>=1");
      $status_actual='';
      $Ticket_actual='';
      foreach($array_products as $key=>$pr)
      {
        //echo " Producto  $pr->ID  <br>";
          
          switch( $pr->Product->InputCartridgeType->TonerType)
          {
            case "Black":
              
              if ($pr->QuantitySelected>=1)
              {
                
                $a_temp['errors']['error']  = "Ya Existe Ticket para Color  " . $item->Product->InputCartridgeType->TonerType ;
                $a_temp['errors']['status'] = 0;
                $data->BLACK=0;
                $status_actual=$value->StatusWithType->Status->LookupName;
                $Ticket_actual=$value->ReferenceNumber;
              }
              break;
            case "Yellow":
              if ($pr->QuantitySelected>=1)
              {
                $a_temp['errors']['error']  = "Ya Existe Ticket para Color  " . $item->Product->InputCartridgeType->TonerType ;
                $a_temp['errors']['status'] = 0;
                $data->YELLOW=0;
                $status_actual=$value->StatusWithType->Status->LookupName;
                $Ticket_actual=$value->ReferenceNumber;
              }
              break;
            case "Cyan":
              if ($pr->QuantitySelected>=1)
              {
                $a_temp['errors']['error']  = "Ya Existe Ticket para Color  " . $item->Product->InputCartridgeType->TonerType ;
                $a_temp['errors']['status'] = 0;
                $data->CYAN=0;
                $status_actual=$value->StatusWithType->Status->LookupName;
                $Ticket_actual=$value->ReferenceNumber;
              }
              break;
            case "Magenta":
              if ($pr->QuantitySelected>=1)
              {
                $a_temp['errors']['error']  = "Ya Existe Ticket para Color  " . $item->Product->InputCartridgeType->TonerType ;
                $a_temp['errors']['status'] = 0;
                $data->MAGENTA=0;
                $status_actual=$value->StatusWithType->Status->LookupName;
                $Ticket_actual=$value->ReferenceNumber;
              }
              break;

          }
          

      }
    }
  }
  else
  {
    $a_temp['errors']['error']  = "No Existe Serie " . $data->HH ;
    $a_temp['errors']['status'] = 22;
  }

  //echo $data->BLACK+$data->CYAN+$data->YELLOW+$data->MAGENTA  . "<-";
  if (($data->BLACK+$data->CYAN+$data->YELLOW+$data->MAGENTA) == 0)
  {
    $a_temp['errors']['error']  = "Ya Existe Ticket con Tonners solicitados  [". $Ticket_actual ."][" . $status_actual ."]" ;
        $a_temp['errors']['status'] = 20;
    $err=1;
  }
  
  //echo json_encode($hh->CustomFields->DOS->Direccion->Organization->ID);
  
  //$user=$incident->PrimaryContact;
  $user = $users[0];
  //$this->sendResponse(json_encode($user->Organization));
  //echo json_encode($user);
  //echo json_encode($user);
  $a_address = array();
  if (!empty($hh->CustomFields->DOS->Direccion->Organization->ID)) {
   $a_directions = $this->Organization->getDirectionsByOrgId($hh->CustomFields->DOS->Direccion->Organization->ID);

   if (count($a_directions) > 0) {
    foreach ($a_directions as $direction) {
     $a_tempSelect['ID']   = $direction->ID;
     $a_tempSelect['name'] = $direction->dir_envio;
     $a_address[]          = $a_tempSelect;
    }
   }
  } else {
   //throw new \Exception("Su contacto no tiene organización asociada");
   $a_temp['errors']['error']  = " No hay informacion para  HH ". $data->HH;
   $a_temp['errors']['status'] = 17;
  }
  if ($a_temp['errors']['status'] == 0) {
   //echo json_encode($a_directions);
   //echo json_encode($data);
   $a_temp['hh']            = $hh->SerialNumber ;
   $a_temp['counter_black'] = $data->CONTADOR_BN;
   $a_temp['counter_color'] = $data->CONTADOR_COLOR;
   $a_temp['count_black']   = $data->BLACK;
   $a_temp['count_cyan']    = $data->CYAN;
   $a_temp['count_magenta'] = $data->MAGENTA;
   $a_temp['count_yellow']  = $data->YELLOW;
   $a_temp['SUBJECT']       = $data->SUBJECT;
   $a_temp['RAZON']       =   $data->RAZON;
   $a_temp['SHIPPING_INSTRUCTIONS']       = $data->SHIPPING_INSTRUCTIONS;
   


   if (!is_numeric($a_temp['counter_black']) and !empty($a_temp['counter_black'])) {
    $a_temp['errors']['error']  = "El valor de 'contador 1' no es numérico.  (" . $a_temp["counter_black"] . ")";
    $a_temp['errors']['status'] = 2;
    $counter_black              = false;
    //echo json_encode($a_temp['errors']);
   } else {
    $counter_black = true;
   }

   if (!is_numeric($a_temp['counter_color']) and !empty($a_temp['counter_color'])) {
    $a_temp['errors']['error']  = "El valor de 'contador 2' no es numérico.  (" . $a_temp["counter_color"] . ")";
    $a_temp['errors']['status'] = 3;
    $counter_color              = false;
    //echo json_encode($a_temp['errors']);
   } else {
    $counter_color = true;
   }

   if (!is_numeric($a_temp['count_black']) and !empty($a_temp['count_black'])) {
    $a_temp['errors']['error']  = "El valor de 'Toner Negro' no es numérico.  (" . $a_temp["count_black"] . ")";
    $a_temp['errors']['status'] = 4;
    $count_black                = false;
    //echo json_encode($a_temp['errors']);
   } else {
    $count_black = true;
   }

   if (!is_numeric($a_temp['count_cyan']) and !empty($a_temp['count_cyan'])) {
    $a_temp['errors']['error']  = "El valor de 'Toner Cyan' no es numérico.  (" . $a_temp["count_cyan"] . ")";
    $a_temp['errors']['status'] = 5;
    $count_cyan                 = false;
    //echo json_encode($a_temp['errors']);
   } else {
    $count_cyan = true;
   }

   if (!is_numeric($a_temp['count_magenta']) and !empty($a_temp['count_magenta'])) {
    $a_temp['errors']['error']  = "El valor de 'Toner Magenta' no es numérico.  (" . $a_temp["count_magenta"] . ")";
    $a_temp['errors']['status'] = 6;
    $count_magenta              = false;
    //echo json_encode($a_temp['errors']);
   } else {
    $count_magenta = true;
   }

   if (!is_numeric($a_temp['count_yellow']) and !empty($a_temp['count_yellow'])) {
    $a_temp['errors']['error']  = "El valor de 'Toner Amarillo ' no es numérico.  (" . $a_temp["count_yellow"] . ")";
    $a_temp['errors']['status'] = 7;
    $count_yellow               = false;
    //echo json_encode($a_temp['errors']);
   } else {
    $count_yellow = true;
   }

   if ($counter_black === true && $counter_color === true) {
    $cc_black = (int)$a_temp['counter_black'];
    $cc_color = (int)$a_temp['counter_color'];

    if (($cc_black + $cc_color) < 1) {
     $a_temp['errors']['error']  = "Al menos uno de los contadores de B/N y Color deben ser mayores que cero.";
     $a_temp['errors']['status'] = 8;
     //echo json_encode($a_temp['errors']);
    }
   }

   if ($count_black === true && $count_cyan === true && $count_magenta === true && $count_yellow === true) {
    $c_black   = (int)$a_temp['count_black'];
    $c_cyan    = (int)$a_temp['count_cyan'];
    $c_magenta = (int)$a_temp['count_magenta'];
    $c_yellow  = (int)$a_temp['count_yellow'];
    if (($c_black + $c_cyan + $c_magenta + $c_yellow) < 1) {
     $a_temp['errors']['error']  = "Debe solicitar al menos un toner para continuar.";
     $a_temp['errors']['status'] = 9;
     //echo json_encode($a_temp['errors']);
    }
   }
   //echo 'HH' .$a_temp['hh'];
   $responseService = $this->DatosHH->getDatosHHInsumos($a_temp['hh']);

   //echo json_encode($responseService);
   //return;
   //$this->sendResponse(json_encode($responseService));

   if ($responseService === false) {
    // $a_temp['errors'][] = "HH no encontrada";
    $a_temp['errors']['error']  = $this->DatosHH->getLastError();
    $a_temp['errors']['status'] = 10;
    $a_errors[]                 = $a_temp;

   } else {
    $a_response_pre = json_decode($responseService, true);

    if (array_key_exists('respuesta', $a_response_pre) !== true) {
     $a_temp['errors']['error']  = "Respuesta no esperada desde servicio.";
     $a_temp['errors']['status'] = 11;
     //echo json_encode($a_temp['errors']);
     $a_errors[] = $a_temp;

    }

    $a_response = $a_response_pre["respuesta"];

    if ($a_response["resultado"] !== "OK") {
     $a_temp['errors']['error']  = "Número de HH no identificado en Dimacofi HH " . $data->HH;
     $a_temp['errors']['status'] = 12;
     //echo json_encode($a_temp['errors']);
    }

    /**  validar convenio_insumos  */

    $convenio_insumos = $a_response["convenio_insumos"];
    $convenio_corchetes = $a_response["convenio_corchetes"]; 
    $convenio_servicios = $a_response["convenio_servicios"];     
    if(!$convenio_insumos)
    {
      $a_temp['errors']['error']  = "HH" . $data->HH . " no tiene Convenio Insumos";
      $a_temp['errors']['status'] = 28;
    }
    
    $bloqueo = $a_response["Direccion"]["Bloqueado"];
    if($bloqueo)
    {
      $a_temp['errors']['error']  = "Direccion " . $a_response["Direccion"]["Direccion"] . "  HH " . $data->HH . " Bloqueado";
      $a_temp['errors']['status'] = 30;
    }


    // Logica para validar que pertenece a la organización
    $dirId = $a_response["Direccion"]["ID_direccion"];

    $c_org_id = $user->Organization->ID;
    //$this->sendResponse(json_encode($user->Organization).'-' .$c_org_id);

    $obj_dir = $this->Organization->getDirectionByEbsId($dirId);
    //$this->sendResponse($obj_dir->Organization->ID.'-' .$c_org_id);
    if ($obj_dir != false) {
     //echo "ID ORG Contacto ". $c_org_id ."ID Dirección ". $obj_dir->ID ." ID ORG HH ". $obj_dir->Organization->ID;
   /*  if ($obj_dir->Organization->ID !== $c_org_id) {
      $a_temp['errors']['error']  = "La HH no figura a la organización asociada a su contrato. HH " . $data->HH;
      $a_temp['errors']['status'] = 13;
      $a_temp['errors']['id']     = $c_org_id;
      $a_temp['errors']['dirId']  = $dirId;
*/
      $zzz=0;
      //echo json_encode($a_temp['errors']);

     }
     else {
     $a_temp['errors']['error']  = "La Dirección asociada no figura en el sistema, favor comunicarse con los administradores  HH " . $data->HH ;
     $a_temp['errors']['status'] = 14;
     $a_temp['errors']['id']     = $c_org_id;
     //echo json_encode($a_temp['errors']);
    }

    if (count($a_temp['errors']) > 0) {
     $a_errors[] = $a_temp;
    }

    //exitoso
    $a_temp['id_dir_selected'] = $dirId;
    $a_temp['info_service']    = $a_response;
    $a_no_errors[]             = $a_temp;

   }

   $response            = new \stdClass;
   $response->success   = true;
   $response->address   = $a_address;
   $response->errors    = $a_errors;
   $response->no_errors = $a_no_errors;
   if (count($a_errors) < 1) {
    $response->message = "Solicitud de insumos analizada sin errores.";

   } else {
    $response->message = "Solicitud de insumos analizada con errores, favor revisar la tabla.";

   }
   //echo json_encode($response);

   //TODO: Verificar HH existe, si no crearla
   //TODO: Verificar Relación de HH - Equipo- Insumos con Insumos, si no esta crearla
   $a_infohh['hh']        = $a_response["ID_HH"];
   $a_infohh['brand_hh']  = $a_response["Marca"];
   $a_infohh['model_hh']  = $a_response["Modelo"];
   $a_infohh['serial_hh'] = $a_response["Serie"];
   $inventoryItemId       = $a_response["inventory_item_id"];
   $a_suppliers           = $a_response["suppliers"];

   $obj_hh = RNCPHP\Asset::first("SerialNumber = '" . $a_infohh['hh'] . "'");

   //$obj_hh  = $this->Supplier->updateAsset($a_infohh, $obj_dir, $obj_contact, $inventoryItemId, $a_suppliers);

   if ($obj_hh !== false and $a_temp['errors']['status'] == 0) {
    $cont1_hh        = (int)$a_temp['counter_black'];
    $cont2_hh        = (int)$a_temp['counter_color'];
    $quantityBlack   = (int)$a_temp['count_black'];
    $quantityCyan    = (int)$a_temp['count_cyan'];
    $quantityYellow  = (int)$a_temp['count_yellow'];
    $quantityMagenta = (int)$a_temp['count_magenta'];
    $quantityColor   = $quantityCyan + $quantityYellow + $quantityMagenta;

    if ($obj_hh->CustomFields->DOS->Product->ID > 0) {

     $a_suppliers = RNCPHP\OP\SuppliersRelated::find("Product.ID = {$obj_hh->CustomFields->DOS->Product->ID} and (EnabledSupplierRequest = 1 or EnabledSupplierRequest is null) ");
     //echo "Suppliers <br>" . json_encode($a_suppliers) . "<br>" ;

     //echo "Suppliers <br>" . json_encode($a_suppliers[0]->Product) . "<br>" ;
     //echo "Suppliers <br>" . json_encode($a_suppliers[1]->Product) . "<br>" ;
     $quantitySuppliers = count($a_suppliers);
     //echo "quantitySuppliers <br>" . $quantitySuppliers. "<br>" ;
     //$lastSuppliersIncident      = RNCPHP\Incident::first("Asset.ID = {$obj_hh->ID} and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont1_hh != 0 order by ClosedTime DESC");
     //$lastSuppliersColorIncident = RNCPHP\Incident::first("Asset.ID = {$obj_hh->ID} and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont2_hh != 0 order by ClosedTime DESC");
     //echo "lastSuppliersIncident <br>" . json_encode($lastSuppliersIncident) . "<br>" ;
     //echo "lastSuppliersColorIncident <br>" . json_encode($lastSuppliersColorIncident) . "<br>" ;

     //echo $quantityBlack .'-'. $quantityColor.'-'.$quantityCyan.'-'.$quantityYellow.'-'.$quantityMagenta ;
     $a_response_suggested = $this->Supplier->getSuggested($obj_hh, $cont1_hh, $cont2_hh, $quantityBlack, $quantityColor, $quantityCyan, $quantityYellow, $quantityMagenta,0,0);
     //echo "a_response_suggested <br>" . json_encode($a_response_suggested) . "<br>" ;
    

    } else {

     $a_temp['errors']['error']  = "No existe insumos relacionados con  HH " . $data->HH;
     $a_temp['errors']['status'] = 20;
     //echo json_encode($a_temp['errors']);
     $a_errors[] = $a_temp;
    }

    if ($a_response_suggested === false) {
      $a_temp['errors']['error']  = "No existe insumos relacionados con HH: " . $data->HH;
      $a_temp['errors']['status'] = 22;

    } else {
     $message             = $a_response_suggested['message'];
     $message_black       = $a_response_suggested['message_black'];
     $message_color       = $a_response_suggested['message_color'];
     $a_supplierSuggested = $a_response_suggested['supplier'];
    }
    if ($a_supplierSuggested > 0) {
     $isBlack   = 0;
     $isCyan    = 0;
     $isYellow  = 0;
     $isMagenta = 0;
     $isNot     = 0;

     foreach ($a_supplierSuggested as $supplier) {
      //TODO: Ronny: Intervenir para seter el valor correccto según tipo de toner
      // TODO: el valor toner_type viene vacio desde el objeto y por ende no se estan seteando los valores que debe tener cada equipo de manera
      //generica, en cuanto pasa por el switch le asigna a todos la misma cantidad que tiene si cualquiera con un valor no = 0 ya que entra al default del switch case
      //se debe intervenir - bien haciendo carga masiva al objeto en cuetion = solucion definitiva o quitando la funcionalidad temporalmente para que solo funcione con los que viene != 0
      //de igual forma se debe analizar el codigo a fondo para obtener otras posibilidades de intervencion en el desarrollo sin que sea tan invasiva
      $tonerTypeId = $supplier['toner_type'];
      //echo 'Tonner' .json_encode($supplier)  . '<br>';
      //echo "<br>";
      switch ($tonerTypeId) {
        case 1: //Cyan
          if($isCyan == 0)
          {
            $resultCL = $this->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $quantityCyan,$supplier['Consumption']);
            $isCyan   = 1;
          }
          break;
        case 2: //Yellow
          if($isYellow==0)
          {
            $resultCL = $this->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $quantityYellow,$supplier['Consumption']);
            $isYellow = 1;
          }
          break;
        case 3: //Magenta
          if($isMagenta==0)
          {
            $resultCL = $this->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $quantityMagenta,$supplier['Consumption']);
            $isMagenta = 1;
          }
          
          break;
        case 4: //Black
          if($isBlack==0)
          {
            $resultCL = $this->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $quantityBlack,$supplier['Consumption']);
            $isBlack  = 1;
          }
       
          break;
        default:
          $a=1;
        break;
      }
      //echo json_encode($resultCL);
      if ($resultCL === false) {
        \RightNow\Connect\v1_3\ConnectAPI::rollback();
        
        $a_temp['errors']['status'] = 23;
        $a_temp['errors']['error']  = "Error creando linea  " . $data->HH;

      } else {
        $a_lines[] = $resultCL;
      }
    }

    //echo "LINEAS <br>";
    //echo json_encode($a_lines);
    //echo "LINEAS <br>";

     $a_infohh['contador_bn']    = $cont1_hh;
     $a_infohh['contador_color'] = $cont2_hh;

     //Datos de HH
     $a_infohh['client_covenant']   = $a_response['Convenio'];
     $a_infohh['client_blocked']    = $a_response['Direccion']['Bloqueado'];
     $a_infohh['contract_type']     = $a_response['TipoContrato'];
     $a_infohh['sla_hh_rsn']        = $a_response['sla_hh_rsn'];
     $a_infohh['delfos']            = $a_response['delfos'];
     $a_infohh['machine_serial']    = $a_response['inventory_item_id'];
     $a_infohh['supplier_covenant'] = $a_response['convenio_insumos'];
     $a_infohh['brackets_covenant'] = $a_response['convenio_corchetes'];

     /* esto no deveria estar aqui,  para este caso , no hay padre* */
     //$incident=RNCPHP\Incident::fetch('200115-000030');
     $result = false;
     //Verificar que no existe ticket paralelo
 
     //aqui debe buscar un contacto valido antes de crear el ticket

     /*  Aca deberia crera el contacto o buscarlo en caso que este en la data */
     if($data->MAIL && $data->CONTACT && $data->PHONE )
     {
     $id_Contacto=$this->BuscaCreaContact($data->MAIL,$data->CONTACT,$data->PHONE,$data->PHONE2,$hh->CustomFields->DOS->Direccion->Organization->ID); /* Busca el ultimo contacto */
     }
     else
     {
     $id_Contacto=$this->searchContact($a_response_pre['respuesta']['Rut'],$data->HH); /* Busca el ultimo contacto */
     }

     //echo "[" . json_encode($a_response_pre['respuesta']['Rut']). "]" . "[" . $data->HH . "]" ."[" . $id_Contacto . "]";
     //exit;
     
     //112972  NUBE PRINT
     //94586   rtorrensclerc@gmail.com
     //$result = $this->Supplier->createTicketMassive(112972, $obj_hh, $a_infohh, $obj_dir, $incident->ID);
     $result = $this->Supplier->createTicketMassive($id_Contacto, $obj_hh, $a_infohh, $obj_dir, $incident->ID);
     
     

     if ($result === false) {
      $a_temp['errors']['error']  = "Ya tiene un ticket de Insumos en curso HH " . $data->HH;
      $a_temp['errors']['status'] = 21;
     } else {
      //echo json_encode($result->ID);
      $incidentId = $result->ID;
      
      $incidents_id .= "," . $incidentId;
  
      foreach ($a_lines as $lineId) {
       $resultAL = $this->Supplier->assocLineToIncident($incidentId, $lineId);

       if ($resultAL === false) {
        \RightNow\Connect\v1_3\ConnectAPI::rollback();
        $a_temp['errors']['error']  = "Error al esociar línea a incidente " . $data->HH;
        $a_temp['errors']['status'] = 24;
        
       }
      }
     
      /** 178 estado Enviado */
      $result->StatusWithType->Status->ID = 1;
      $result->CustomFields->c->supply_reason->ID=$a_temp['RAZON'];
      $result->Subject                    = $a_temp['SUBJECT'];
      $result->CustomFields->c->shipping_instructions= $a_temp['SHIPPING_INSTRUCTIONS'];
      
      $result->save();
      

     }
    }
   }
  }
  //echo json_encode($result->ID);
  $inc = $result->ID;
  //$this->sendResponse(json_encode($inc));
 
  //echo json_encode($a_lines);
  //echo json_encode($a_infohh);
  if ($inc) {
   $incident = RNCPHP\Incident::fetch($inc);
   //$this->sendResponse($json_data);

   
   $respuesta = array('status' => "OK", 'ref_no' => $incident->ReferenceNumber, 'ID' => $incident->ID);

   self::insertPrivateNote($incident,json_encode($respuesta) );
  } else {
   $incident = null;
  }
  //$incident1=RNCPHP\Incident::fetch(572208);
  //echo  "<br>";
  //echo json_encode($a_temp['errors']) . "<br>";
  //echo  "<br>";
  if (!empty($incident) and $a_temp['errors']['status'] == 0) {
   $respuesta = array('status' => "OK", 'ref_no' => $incident->ReferenceNumber, 'ID' => $incident->ID);
  } else {

   $respuesta = array('status' => "NOOK", 'ref_no' => $incident->ReferenceNumber, 'ID' => $incident->ID, 'Code' => $a_temp['errors']['status'], 'error' => $a_temp['errors']['error']);
  }
 
  $this->sendResponse(json_encode($respuesta));
  // echo json_encode($incident1,3);
 }
 public function ConsultaTickets()
 {

  $json_data='
  {
    "SUBJECT": "SOLICITUD DE INSUMOS NBP",
    "RAZON": "285", 
    "HH": "3608544" ,
    "TIPO_INCIDENTE": "24",
    "SHIPPING_INSTRUCTIONS": "PRUEBA DE INSUMOS NUEBE PRINT", 
    "CONTADOR_BN": "28475",
    "CONTADOR_COLOR": "0", 
    "BLACK": "1", 
    "CYAN": "0", 
    "MAGENTA": "1", 
    "YELLOW": "0"
  }';

  $alarm=json_decode($json_data);
  
 
  $log_alarm='';
  $array_obj= RNCPHP\Incident::find( "CustomFields.c.id_hh=". 3608544 ." and Disposition.ID=24 and StatusWithType.Status.ID not in(2,149,148)");
  foreach($array_obj as $key=>$tk)
  {
    $array_products=RNCPHP\OP\OrderItems::find("Incident.ID = " . $tk->ID. " and QuantitySelected>=1");
    foreach($array_products as $key=>$pr)
    {
      //echo json_encode( $tk->ID . '-' .$pr->Product->InputCartridgeType->TonerType)  . " <br>";
      switch($pr->Product->InputCartridgeType->TonerType)
      {
        case 'Black':
          if($alarm->BLACK>=1)
          {
            $log_alarm =$log_alarm = " - Ya tiene un ticket de Insumos Black";
          }
          $alarm->BLACK=0;
          echo "BLACK 0 <br>";
          break;
        case 'Cyan':
          if($alarm->CYAN>=1)
          {
            $log_alarm =$log_alarm = " - Ya tiene un ticket de Insumos CYAN";
          }
          $alarm->CYAN=0;
          echo "CYAN 0 <br>";
          break;
        case 'Magenta':
          if($alarm->MAGENTA>=1)
          {
            $log_alarm =$log_alarm = " - Ya tiene un ticket de Insumos MAGENTA";
          }
          $alarm->MAGENTA=0;
          echo "MAGENTA 0 <br>";
          break;  
        case 'Yellow':
          if($alarm->YELLOW>=1)
          {
            $log_alarm =$log_alarm = " - Ya tiene un ticket de Insumos YELLOW";
          }
          $alarm->YELLOW=0;
          echo "YELLOW 0 <br>";
          break;        
      }
    }
  }
  if( ($alarm->BLACK+$alarm->CYAN+$alarm->MAGENTA+$alarm->YELLOW) >0)
  {
    echo "PEDIR $alarm->BLACK $alarm->CYAN $alarm->MAGENTA $alarm->YELLOW  - $log_alarm";
  }
  else
  {
    echo "NO PEDIR $alarm->BLACK $alarm->CYAN $alarm->MAGENTA $alarm->YELLOW - $log_alarm";
  }
  
 }

}
