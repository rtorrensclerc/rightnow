<?php

/**
 * Skeleton Opportunity cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_2 as RNCPHP;

require_once "Labels.php";
require_once "ConnectUrl.php";


class SendPPTOToSAIHandler
{


    static function HandleOpportunity($runMode, $action, $opportunity, $cycle)
    {

        $j=0;
        $i=0;
        $Numero_Factura='';
        $Numero_Presupuesto='';
        $LineNumber=0;
        $Correlativo=1;
        $lineas = array();
        $files = array();
        //if ($cycle !== 0) return;

        $opportunity = RNCPHP\Opportunity::fetch($opportunity->ID);
        //self::insertPrivateNote($opportunity, "oportunidad -< " . $opportunity->ID );

     
        $opportunity->Save(RNCPHP\RNObject::SuppressAll);
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

        $RETIRA_ENVIA='';
        if($opportunity->CustomFields->c->send_pick_up)
        {
          $RETIRA_ENVIA='RETIRA ';
        }
        else
        {
          $RETIRA_ENVIA='ENVIAR a ';
        }

        try
        {
          //"instrucciones_envio"=> $op->PrimaryContact->Contact->Name->First." ".$op->PrimaryContact->Contact->Name->Last . ' ' . $op->PrimaryContact->Contact->Emails[0]->Address . ' ' . $op->PrimaryContact->Contact->Phones[0]->Number;

          $a_orderItems          = RNCPHP\OP\OrderItems::find("Opportunity.ID ='{$opportunity->ID}'");
          foreach ($a_orderItems as $item)
          {

            $array_linea = array(
              "tipo_transaccion_linea_id"=> 22,
              "sub_rubro_id"=> 62,
              "cantidad"=> $item->QuantitySelected,
              "delfos"=>  $item->Product->CodeItem ,
              "precio"=> round($item->UnitTempSellPrice),
              "party_site_id"=> $opportunity->CustomFields->OP->DireccionEnvio->d_id,
              "instrucciones_envio"=> $RETIRA_ENVIA . $opportunity->PrimaryContact->Contact->Name->First." ".$opportunity->PrimaryContact->Contact->Name->Last . ' ' . $opportunity->PrimaryContact->Contact->Emails[0]->Address . ' ' . $opportunity->PrimaryContact->Contact->Phones[0]->Number,
              "nombre_contacto"=> $opportunity->PrimaryContact->Contact->LookupName,
              "email_contacto"=> $opportunity->PrimaryContact->Contact->Emails[0]->Address,
              "telefono_contacto"=> $opportunity->PrimaryContact->Contact->Phones[0]->RawNumber
            );
            $lineas[] = $array_linea;
          }
           // self::insertPrivateNote($opportunity, "json enviado:Lineas" . $LineNumber . "-Facturas" . count($a_list_items)  );
            $numero_facturas=count($a_list_items);


          //self::insertPrivateNote($opportunity, "FILES: ". count($opportunity->FileAttachments));
          //self::insertPrivateNote($opportunity, "LINEAS :" . json_encode($lineas) );
          
          $url =$cfg2->Value . '/cloudsai/filedata'; 
          for ($i = 0; $i < count($opportunity->FileAttachments); $i++) {
            $postArray = array(
              "id_ptto" => $opportunity->ID,
              "id_file" => $opportunity->FileAttachments[$i]->ID,
              "filaname"=> $opportunity->FileAttachments[$i]->FileName
            );
            //self::insertPrivateNote($opportunity, "FILES: [". $i . "] :" . json_encode($postArray));
            $json_request=json_encode($postArray);
            $result         = ConnectUrl::requestCURLJsonRaw($url, $json_request,null);
            $filedata=json_decode($result );
            $file =array(
              "filename"=> $opportunity->FileAttachments[$i]->FileName,
              "content"=> $filedata->data ,
              "comentarios"=> $opportunity->FileAttachments[$i]->FileName
            );
            $files[]=$file;
            
          }
          
          switch($opportunity->CustomFields->c->payment_conditions->ID)
          {
            case 72: // Contado
              $forma_de_pago=21;
              $condiciones_de_pago=6;
              break;
            case 71: // 30 Dias
              $forma_de_pago=21;
              $condiciones_de_pago=7;
              
              break;
            default:
              $forma_de_pago=21;
              $condiciones_de_pago=6;
              break; 
          }
          //self::insertPrivateNote($opportunity, "fin-->"  . json_encode($files));
   
          $a_request = array( 
            "party_id" => $opportunity->Organization->CustomFields->c->id_cliente,
            "party_site_id" => $opportunity->CustomFields->OP->Direccion->d_id,
            "tipo_transaccion_id" => 12, /* va en Duro   Venta de Materiales e Repuestos*/ 
            "vendedor_id" => $opportunity->AssignedToAccount->CustomFields->c->resource_id,
            "condiciones_de_pago_id" => $condiciones_de_pago,
            "forma_de_pago_id" => $forma_de_pago,
            "numero_oc" => $opportunity->CustomFields->c->oc_number,
            "comentarios" => $RETIRA_ENVIA . $opportunity->PrimaryContact->Contact->Name->First." ".$opportunity->PrimaryContact->Contact->Name->Last . ' ' . $opportunity->PrimaryContact->Contact->Emails[0]->Address . ' ' . $opportunity->PrimaryContact->Contact->Phones[0]->Number,
            "aprobacion" => false,
            "referencia_externa" =>"RNPTTO-".$opportunity->ID,
            "lineas" => $lineas ,
            "files"=> $files,
            "id_ptto"=> $opportunity->ID,
            "url_callback_rn"=>"https://api.dimacofi.cl/cloudsai/mq/sairn",
            "usuario"=>$opportunity->AssignedToAccount->Login
          );

    
    $json_request = json_encode($a_request);
   // self::insertPrivateNote($opportunity, "json enviado: ". $json_request);
    
            $jsonToken = ConnectUrl::geToken();
            if($jsonToken === FALSE)
                return FALSE;
    
            // Se encapsula token como array
            //$a_jsonToken = json_decode($jsonToken, TRUE);
    
            //if (empty($a_jsonToken["access_token"]))
            //    throw new \Exception("Json de token inválido {$jsonToken}", 1);
        
            $token = $jsonToken;
            

              //$json_data_post = Blowfish::encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
              //$json_data_post = base64_encode($oasai);
              //$postArray      = array ('data' => $json_data_post);
              //self::insertPrivateNote($opportunity, "json : ". $json_request);
              $url =$cfg2->Value . '/cloudsai/mq/oasai'; 
              $result         = ConnectUrl::requestCURLJsonRaw($url, $json_request,$token);

            

              self::insertPrivateNote($opportunity, "json devuelto: ". $result . '-' . ConnectUrl::getResponseTime());
              //self::insertPrivateNote($opportunity, "json devuelto2: ". ConnectUrl::getResponseError());
              
              if ($result != false) // No hubo error en el servicio de actualización de precios
              {
                $arr_json  = json_decode($result, true);


                //No fallo el JSON Decode
                if ($arr_json != false)
                {
                  if ((array_key_exists('result', $arr_json) and (array_key_exists('result', $arr_json)) ))
                  {
                   
                    switch ($arr_json['result'])
                    {
                      case "OK":
                            $message                                    = "PTTO  Enviado con exito !!";
                          break;
                    }
                    self::insertPrivateNote($opportunity, $message);
                  }
                  else
                  {
                    $message = "ERROR: Estructura JSON No valida, no se encontro 'resultado' ni 'respuesta' ". PHP_EOL;
                    self::insertPrivateNote($opportunity, $message);
                    $opportunity->save();
                  }
                }
                else
                {
                  $message = "ERROR: Problema en la decodificación del JSON ".PHP_EOL."Respuesta: ".$result.PHP_EOL;
                  self::insertPrivateNote($opportunity, $message);
                  $opportunity->save();
                }
              }
              else
              {
                  //$message = "ERROR-->: ".ConnectUrl::getResponseError();
                  //self::insertPrivateNote($opportunity, $message);

                  $opportunity->save();
              }
          }
            catch (RNCPHP\ConnectAPIError $err )
            {
             // $message = "Error ".$e->getMessage();
              //self::insertPrivateNote($opportunity, "Error Query: ".$message);
              //self::insertBanner($opportunity, $bannerNumber);
              //$opportunity->save();
            }


    }

    static function insertPrivateNote($opportunity, $textoNP)
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

    static function insertBanner($opportunity, $typeBanner, $texto = '')
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

}
