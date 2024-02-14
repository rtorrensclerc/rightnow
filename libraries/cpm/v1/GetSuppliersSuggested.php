<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;
use RightNow\Connect\v1_3 as RNCPHP;


class GetSuppliersSuggested
{
    static function HandleIncident($runMode, $action, $incident, $cycle)
    {
        
        if ($cycle !== 0) return;
        $bannerNumber = 0;
        //self::insertPrivateNote($incident->ID, 'Ingresando a GetSuppliersSuggested ');

        $a_response_suggested =  self::getSuggested1($incident, 'TS');
        
        //self::insertPrivateNote($incident->ID, 'Saliendo a GetSuppliersSuggested ' . json_encode($a_response_suggested));

        return;

    }
    static function getSuggested1($incident,$msg)
    {   
        try
        {
          $iscolor = array();
          $i=0;
          for ($i=0;$i<=24;$i++)
          {
            $iscolor[]=0;
          }
        $obj_hh = RNCPHP\Asset::first("SerialNumber = '" . $incident->CustomFields->c->id_hh . "'");

    
        
  
        $a_response_suggested = self::getSuggested($incident->ID,$obj_hh, $incident->CustomFields->c->cont1_hh, $incident->CustomFields->c->cont2_hh, 0, 0, 0, 0, 0);
        //self::insertPrivateNote($incident->ID,json_encode( $a_response_suggested ) . ' a_response_suggested', 3);
      
        if ($a_response_suggested === false) {
   
            //$message = "Error obteniendo sugeridos, se sugiere lo minimo ". $this->CI->Supplier->getLastError();
            \RightNow\Connect\v1_3\ConnectAPI::rollback();
            throw new \Exception("Error obteniendo sugeridos, se sugiere lo mínimo " );
           } else {

            $message             = $a_response_suggested['message'];
            $message_black       = $a_response_suggested['message_black'];
            $message_color       = $a_response_suggested['message_color'];
            $a_supplierSuggested = $a_response_suggested['supplier'];
           
           }
           //self::insertPrivateNote($incident->ID, json_encode($a_supplierSuggested), 3);
           $incident->save(RNCPHP\RNObject::SuppressAll);
           
            if ($a_supplierSuggested > 0) {
    
                $isBlack   = 0;
                $isCyan    = 0;
                $isYellow  = 0;
                $isMagenta = 0;
                $isGray    = 0;
                $isMatteBlack = 0;


                $isSinClasificacion = 0;
                $isNot     = 0;
                
                $incident->save(RNCPHP\RNObject::SuppressAll);
                foreach ($a_supplierSuggested as $supplier) {
                    //TODO: Ronny: Intervenir para seter el valor correccto según tipo de toner
                    // TODO: el valor toner_type viene vacio desde el objeto y por ende no se estan seteando los valores que debe tener cada equipo de manera
                    //generica, en cuanto pasa por el switch le asigna a todos la misma cantidad que tiene si cualquiera con un valor no = 0 ya que entra al default del switch case
                    //se debe intervenir - bien haciendo carga masiva al objeto en cuetion = solucion definitiva o quitando la funcionalidad temporalmente para que solo funcione con los que viene != 0
                    //de igual forma se debe analizar el codigo a fondo para obtener otras posibilidades de intervencion en el desarrollo sin que sea tan invasiva
                    $tonerTypeId = $supplier['toner_type'];
       

                    if($iscolor[$tonerTypeId]==0)
                    {
                      $resultCL = self::createLine($supplier['supplier_id'], $supplier['quantity_suggested'], 0,$supplier["Consumption"]);
                      $iscolor[$tonerTypeId]   = 1;
                    }
                    /*
                    switch ($tonerTypeId) {
                        case 1: //Cyan
                        //echo "Black  isCyan " . $isCyan . "<br>";
                        //echo "Black  quantityCyan " . $quantityCyan . "<br>";
                        
                        if($isCyan==0)
                        {

                          $resultCL = self::createLine($supplier['supplier_id'], $supplier['quantity_suggested'], 0,$supplier["Consumption"]);
                          $isCyan   = 1;
                        }
                
                        break;
                        case 2: //Yellow
                        //echo "Yellow <br>";
                        if($isYellow==0)
                        {
                          $resultCL = self::createLine($supplier['supplier_id'], $supplier['quantity_suggested'], 0,$supplier["Consumption"]);
                          $isYellow = 1;
                        }
        
                        break;
                        case 3: //Magenta
                        //echo "Magenta <br>";
                        if($isMagenta==0)
                        {
                          $resultCL  = self::createLine($supplier['supplier_id'], $supplier['quantity_suggested'], 0,$supplier["Consumption"]);
                          $isMagenta = 1;
                        }
            
                        break;
                        case 4: //Black
                        //echo "Black  isBlack " . $isBlack . "<br>";
                        //echo "Black  quantityBlack " . $supplier["Consumption"] . "<br>";
                        if($isBlack ==0)
                        {
    
                          $resultCL = self::createLine($supplier['supplier_id'], $supplier['quantity_suggested'], 0,$supplier["Consumption"]);
                   
                          $isBlack  = 1;
                        }
                        
                        break;
                        case 5: //Black
                          //echo "Black  isBlack " . $isBlack . "<br>";
                          //echo "Black  quantityBlack " . $supplier["Consumption"] . "<br>";
                          if($isSinClasificacion ==0)
                          {
                            $resultCL = self::createLine($supplier['supplier_id'],0, 0,$supplier["Consumption"]);
                            $isSinClasificacion  = 1;
                          }
                          
                          break;
                        case 18: //Gray
                          //echo "Black  isBlack " . $isBlack . "<br>";
                          //echo "Black  quantityBlack " . $supplier["Consumption"] . "<br>";
                          if($isGray ==0)
                          {
      
                            $resultCL = self::createLine($supplier['supplier_id'], $supplier['quantity_suggested'], 0,$supplier["Consumption"]);
                      
                            $isGray  = 1;
                          }
                          
                          break;
                        case 23: //MatteBlack
                          //echo "Black  isBlack " . $isBlack . "<br>";
                          //echo "Black  quantityBlack " . $supplier["Consumption"] . "<br>";
                          if($isMatteBlack ==0)
                          {
      
                            $resultCL = self::createLine($supplier['supplier_id'], $supplier['quantity_suggested'], 0,$supplier["Consumption"]);
                      
                            $isMatteBlack  = 1;
                          }
                          
                          break;  
                        default:
                        //echo "isNot <br>";
                        //self::insertPrivateNote($incident->ID, "DEFECTO osea nada", 3);
                        // $resultCL = $this->Supplier->createLine($supplier['supplier_id'], $supplier['quantity_suggested'],  $supplier['quantity']);
                        $isNot = 1;
                
                        break;
                     }
                     */
                        //echo json_encode($resultCL);
                        if ($resultCL === false) {
                            \RightNow\Connect\v1_3\ConnectAPI::rollback();
                           
                        } else {
                            $a_lines[] = $resultCL;
                        }
                    }

                }
             
                foreach ($a_lines as $lineId) {
                    $resultAL = self::assocLineToIncident($incident->ID, $lineId);
                    //self::insertPrivateNote($incident->ID,$incident->ID .'-'. json_encode($lineId) . ' a_lines', 3);
                    if ($resultAL === false) {
                     \RightNow\Connect\v1_3\ConnectAPI::rollback();
                     //self::insertPrivateNote($incident->ID, "Roll", 3);
                     throw new \Exception("Error al esociar línea a incidente " );
                    }
                   }
                   
                   $incident->save(RNCPHP\RNObject::SuppressAll);
            }
        catch (Exception $err ){


            $incident->Subject='TEST '.$err->getMessage();
           
            $incident->save(RNCPHP\RNObject::SuppressAll);
        }
    }

    static function calculo_percent($i,$consumption,$rendimientoReal,$supplier,$Actual,$Ultimo)
    {
      $resp = array();
     
      if($supplier->TeoricYieldToner>0)
      {
        $preSuggested   = $consumption / $supplier->TeoricYieldToner;
        //self::insertPrivateNote($i, 'PRESUGGESTED  ->'.json_encode($preSuggested) . '- '. $supplier->TrueYieldToner .'-' .$consumption, 3);
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
      $ceilSuggested  = round($suggested);
      $rendimientoReal=$supplier->TeoricYieldToner;
      //echo "->[" . json_encode($supplier->TrueYieldToner) . "-" .   $consumption ."-" .   $Consumption ."]<br>";
         
            if ($consumption < 0)
            {
                //CUSTOM_MSG_SUPPLY_MODEL_CONSUMPTION_COLOR_NEGATIVE
                $msg = RNCPHP\MessageBase::fetch( 1000255 );
              $resp['message_color']  =  sprintf($msg->Value , $consumption,$Actual, $Ultimo);
              self::insertPrivateNote($i,$resp['message_color'] , 3);


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
              

              if ($rendimientoReal <= 0)
              {
                //Rendimiento no medible por lo que sugiere lo minimo
                //, $supplier->InputCartridgeType->TonerType
                //CUSTOM_MSG_SUPPLY_MODEL_COLOR_ITEM_MIN
                $msg = RNCPHP\MessageBase::fetch( 1000256 );
                $resp['message_color']  =  sprintf($msg->Value,$rendimientoReal);
                //self::insertPrivateNote($i,$resp['message_color'] , 3);
                
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
                $preSuggested   = $consumption / $supplier->TeoricYieldToner;

                //Sugerido redondeado hacia abajo
                //$ceilSuggested  = floor($preSuggested);
                $ceilSuggested  = round($preSuggested);
                //self::insertPrivateNote($i,"ceilSuggested [" .  $ceilSuggested  . "]["  .$consumption. "][" .$rendimientoReal.  "]["  .$preSuggested . "]", 3);
                if ($ceilSuggested > 0)
                {
                    //CUSTOM_MSG_SUPPLY_MODEL_COLOR_BACKSTORY
                    $msg = RNCPHP\MessageBase::fetch( 1000257 );
                  $resp['message_color']  =  sprintf($msg->Value, $ceilSuggested);
                  //self::insertPrivateNote($i,$resp['message_color'] , 3);
                 
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
                    $resp['quantity_suggested'] = $ceilSuggested;
                    $resp['quantity']           = $quantityColor;
                    $resp['toner_type']         = $supplier->InputCartridgeType->ID;
                    $resp['Consumption']           = $Consumption;
                 
                  
                }
                else
                {
                  //$supplier->InputCartridgeType->TonerType,
                  // CUSTOM_MSG_SUPPLY_MODEL_NEGATIVE_VALUE_COLOR_MIN
                  $msg = RNCPHP\MessageBase::fetch( 1000258 );
                  $resp['message_color']  =  sprintf($msg->Value, $ceilSuggested);
                
                  //self::insertPrivateNote($i,$resp['message_color'] , 3);
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
    /*
    public function Busca_stock($a_suppliers,$i)
    {
       $cfg2 = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
      self::insertPrivateNote($i,'--->'.$cfg2->Value , 3);
      
      $array_post = array(
        "usuario" => "appmind",
        "accion"  => "info_hh",
        "datos"   => array(
            "id_hh" => '3048555'
        )
    );

      $json_data_post = json_encode($array_post);
      $json_data_post2 = base64_encode($json_data_post);                  
      $postArray = array("data" => $json_data_post2);
      $result = ConnectUrl::requestPost('http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php', $postArray);

      return ($result);
    }
*/
    static function getSuggested($i,$asset, $cont1_hh, $cont2_hh, $quantityBlack, $quantityColor,$quantityCyan, $quantityYellow, $quantityMagenta)
    {
      $error = array();
      try
      {
        //$product = RNCPHP\DOS\Product::fetch($asset->CustomFields->DOS->Product->ID); //Se Obtiene el objeto producto(Equipo), asociado a la HH
        if (is_object ($asset->CustomFields->DOS->Product))
        {
          //Se verfica que exista un producto
          $productId =   $asset->CustomFields->DOS->Product->ID;

          if (empty($productId))
          {
              //CUSTOM_MSG_SUPPLY_MODEL_HH_WITHOUT_DEVICE
              
 
            $error['message']  = sprintf(getMessageBase(1000251), $asset->ID);
            self::insertPrivateNote($i,$error['message'] , 3);
            $error['numberID'] = 2;
            return false;
          }

          $counterBN         = $cont1_hh;
          $counterColor      = $cont2_hh;
         

          //Se buscan los Insumos asociados al Equipo
          // TODO:rtorrens , debemos buscar una manera de no usar 2 o mas items del mismo tipo, por ejemplo. solo elegir el mas popular o 
          // el items con mas stock. 
          $a_suppliers       = RNCPHP\OP\SuppliersRelated::find("Product.ID ={$productId} and (EnabledSupplierRequest = 1 or EnabledSupplierRequest is null)");
    
  

          $quantitySuppliers = count($a_suppliers);
          if ($quantitySuppliers > 0)
          {
            //if($i)
            //$a_HH = self::Busca_stock($a_suppliers);
           
            $obj_incident = RNCPHP\Incident::fetch( $i);
           
        /*    if($obj_incident->PrimaryContact->Emails[0]->Address=='rtorrensclerc@gmail.com')
            {
              self::insertPrivateNote($i,'TEST->' . json_encode($obj_incident->PrimaryContact->Emails[0]->Address) , 3);  
              //$respuesta=self::Busca_stock($a_suppliers,$i);
             
            );
            $array_post = array(
              "usuario" => "appmind",
              "accion"  => "info_hh",
              "datos"   => array(
                  "id_hh" => '3048555'
              )
              $json_data_post = json_encode($array_post);
              $json_data_post2 = base64_encode($json_data_post);                  
              $postArray = array("data" => $json_data_post2);
              $result = ConnectUrl::requestPost('http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php', $postArray);
        
              self::insertPrivateNote($i,'TEST2->' . json_encode($respuesta) , 3); 
            }
            */
            //Se buscan las ultimas solicitudes creadas para la HH en particular
            $lastSuppliersIncident      = RNCPHP\Incident::first("Asset.ID = {$asset->ID} and StatusWithType.Status.ID IN ( 2) and Disposition.ID = 24 and CustomFields.c.cont1_hh != 0 order by ClosedTime DESC");
            //$lastSuppliersIncident      = RNCPHP\Incident::first("Asset.ID = {$asset->ID} and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont1_hh != 0 order by ClosedTime DESC");
            $lastSuppliersColorIncident = RNCPHP\Incident::first("Asset.ID = {$asset->ID} and StatusWithType.Status.ID IN ( 2) and Disposition.ID = 24 and CustomFields.c.cont2_hh != 0 order by ClosedTime DESC");
            //$lastSuppliersColorIncident = RNCPHP\Incident::first("Asset.ID = {$asset->ID} and StatusWithType.Status.ID = 2 and Disposition.ID = 24 and CustomFields.c.cont2_hh != 0 order by ClosedTime DESC");
            $a_response = array();
            $a_response['supplier'] = array();
            $a_response['message'] = '';
            $a_response['message_black'] = '';
            $a_response['message_color'] = '';

           
            if (empty($lastSuppliersIncident) and empty($lastSuppliersColorIncident))
            {
                //CUSTOM_MSG_SUPPLIER_MODEL_SUGGEST_MIN      
                $msg = RNCPHP\MessageBase::fetch( 1000097 );
                $a_response['message'] =  sprintf($msg->Value);           
                //self::insertPrivateNote($i,$a_response['message'] , 3);    
          
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
                  case 7:
                  case 8:
                  case 9:
                  case 10:
                  case 11:
                  case 12:
                  case 13:
                  case 14:
                  case 15:
                  case 16:
                  case 17:
                  case 18:
                  case 19:
                  case 20:
                  case 21:
                  case 22:    
                  case 23:
                    $a_TempResponse['quantity']           = $quantityBlack;
                    break;
                  default:
                  $a_TempResponse['quantity']           = 0;
                }
                $a_TempResponse['toner_type']         = $supplier->InputCartridgeType->ID;
                $a_response['supplier'][]             = $a_TempResponse;
              }
              //self::insertPrivateNote($i,json_encode($a_response), 3);    
              return $a_response;
            }
            else
            {
             
              $founded       = false;
              $itemSuggested = null;
              $a_colorItems  = array();


              $a_colorItems  =  $a_suppliers;
                
              //Inicio logica sugerido para Toner 
              if (!empty($lastSuppliersIncident) or !empty($lastSuppliersColorIncident))
              {
              
                $rendimientoColorReal = 0;
                foreach ($a_colorItems as $supplier_tmp)
                {
                    $supplier=$supplier_tmp->Supplier;
                    //self::insertPrivateNote($i, '2->'.json_encode($supplier), 3);
                  //Rendimiento real es igual a la suma de todos los rendimientos
                  if($supplier->InputCartridgeType->TonerType<>'Black')
                    {
                      
                      $rendimientoColorReal += $supplier->TeoricYieldToner;
                      //self::insertPrivateNote($i, '1-COLOR' . $supplier->InputCartridgeType->TonerType, 3);
                    }
                    else
                    {
                      $rendimientoColorReal += $supplier->TeoricYieldToner;
                      $rendimientoBNReal = $supplier->TeoricYieldToner; 
                      //self::insertPrivateNote($i, '2-NEGRO' . $supplier->InputCartridgeType->TonerType, 3);
                    }
                }
              
                foreach ($a_colorItems as $supplier_tmp)
                { 
                    $supplier=$supplier_tmp->Supplier;
                      //Sugerido consumo
                      
                      
                      if($supplier->InputCartridgeType->TonerType=='Black')
                      {
                        $consumption    = $counterBN - $lastSuppliersIncident->CustomFields->c->cont1_hh;
                        
                        $sugerido  = self::calculo_percent($i,$consumption,$rendimientoBNReal,$supplier,$counterBN,$lastSuppliersIncident->CustomFields->c->cont1_hh);
                      }
                      else
                      {

                        $consumption    = $counterColor + $counterBN - $lastSuppliersIncident->CustomFields->c->cont2_hh - $lastSuppliersIncident->CustomFields->c->cont1_hh;
                        //self::insertPrivateNote($i, 'CONSUMO COLOR  ->'.json_encode($consumption) . '- '. $counterColor .'-' .$lastSuppliersIncident->CustomFields->c->cont2_hh, 3);
                        $sugerido  = self::calculo_percent($i,$consumption,$rendimientoColorReal,$supplier,$counterColor,$lastSuppliersIncident->CustomFields->c->cont2_hh);
                      }

                      
                      $a_response['message_color'] =$a_response['message_color']  . '-<br>' . $sugerido['message_color'];

                      

                      
                      if($supplier->Enabled)
                      { 
                        switch($supplier->InputCartridgeType->ID)
                        {
                          case 1:
                          case 2:
                          case 3:
                          case 4:
                          case 7:
                          case 8:
                          case 9:
                          case 10:
                          case 11:
                          case 12:
                          case 13:
                          case 14:
                          case 15:
                          case 16:
                          case 17:
                          case 18:
                          case 19:
                          case 20:
                          case 21:
                          case 22:    
                          case 23:
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
                }
                
              }
              else
              {
                //CUSTOM_MSG_SUPPLIER_MODEL_MIN_COLOR
                $msg = RNCPHP\MessageBase::fetch( 1000099 );
                $a_response['message_color'] =  sprintf($msg->Value);
                //self::insertPrivateNote($i,$a_response['message'] , 3);
                //Todos los demas se marcan en 0
                foreach ($a_colorItems as $supplier)
                {
                    $supplier=$supplier_tmp->Supplier;
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
              //CUSTOM_MSG_SUPPLY_MODEL_NO_SUPPLY_TO_DEVICE
            $error['message']  = sprintf(getMessageBase(1000259), $productId);
            self::insertPrivateNote($i,$error['message'], 3);
            $error['numberID'] = 2;
            return false;
          }
        }
        else
        {
            //CUSTOM_MSG_SUPPLY_MODEL_HH_NO_DEVICE
          $error['message']  = sprintf(getMessageBase(1000260), $asset->Serial);
          self::insertPrivateNote($i,$error['message'], 3);
          $error['numberID'] = 2;
          return false;
        }

      }
      catch (RNCPHP\ConnectAPIError $err )
      {
          //
        $error['message']  = sprintf(getMessageBase(1000238), $err->getCode(), $err->getMessage());
        self::insertPrivateNote($i,$error['message'], 3);
        $error['numberID'] = 1;
        return false;
      }
    }
    static function insertPrivateNote($id, $comments, $entry_type_id = NULL)
    {
      try
      {
        $incident                                      = RNCPHP\Incident::fetch($id);
        $incident->Threads                             = new RNCPHP\ThreadArray();
        $incident->Threads[0]                          = new RNCPHP\Thread();
        $incident->Threads[0]->EntryType               = new RNCPHP\NamedIDOptList();
        if($entry_type_id)
          $incident->Threads[0]->EntryType->ID           = $entry_type_id;
        else
          $incident->Threads[0]->EntryType->ID           = 8; // 1: nota privada
        $incident->Threads[0]->Text                    = $comments;
        $incident->Save(RNCPHP\RNObject::SuppressAll);
        return true;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $error['message']  = "Nota Privada : ".$err->getCode()." ".$err->getMessage();
        $error['numberID'] = 1;
        return false;
      }
    }
    static  function createLine($supplierId, $quantitySuggested, $quantity,$Consumption)
    {
      try
      {
        $supplier                     = RNCPHP\OP\Product::fetch($supplierId);
        $orderitem                    = new RNCPHP\OP\OrderItems();
        $orderitem->QuantitySuggested = $quantitySuggested;
        $orderitem->QuantitySelected  = $quantity;
        $orderitem->Consumption       = $Consumption;
        $orderitem->IsSuggested       = true;
        $orderitem->Product           = $supplier;
        $orderitem->temp_stock        = $supplier->last_stock;
        //$orderitem->Incident          = $incident;
        $orderitem->Save();
        return $orderitem->ID;
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        $msg = RNCPHP\MessageBase::fetch( 1000238 );
        return sprintf($msg->Value, $err->getCode(), $err->getMessage());
      }
    }
    static  function assocLineToIncident($incidentId, $lineId)
    {
    //self::insertPrivateNote($incidentId, 'LINEA'. json_encode($lineId), 3);
      try
      {
        $item           = RNCPHP\OP\OrderItems::fetch($lineId);
        $incident       = RNCPHP\Incident::fetch($incidentId);
        $item->Incident = $incident;
        $item->Save();
       // self::insertPrivateNote($incidentId,$incidentId .'-'. json_encode($lineId) . ' assocLineToIncident', 3);
        return true;
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        $msg = RNCPHP\MessageBase::fetch( 1000238 );
        return sprintf($msg->Value, $err->getCode(), $err->getMessage());

      }
    }

}
