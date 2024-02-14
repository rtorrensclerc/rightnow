<?php
/**
 * Skeleton Incident cpm handler.
 */
namespace Custom\Libraries\CPM\v1;
use RightNow\Connect\v1_3 as RNCPHP;

require_once "Labels.php";
require_once "Blowfish.php";
require_once "ConnectUrl.php";
class UpdateItemCodeReplaceHH
{
    static function HandleIncident($runMode, $action, $incident, $cycle)
    {
        if ($cycle !== 0) return;
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        try
        {
          //self::insertPrivateNote($incident, "LLegando a UpdateItemCodeReplaceHH [" .$incident->CustomFields->c->hh_replacement."]" );
          if($incident->CustomFields->c->hh_replacement<1)
          {
            //self::insertPrivateNote($incident, "LLegando a UpdateItemCodeReplaceHH-----" );
            $incident->CustomFields->c->predictiondata='NOHH';
            $incident->StatusWithType->Status->ID                  = 1 ;  //  Ingresado
            $incident->Save();
            return;
          }
          
          if($incident->CustomFields->c->hh_replacement==$incident->CustomFields->c->id_hh )
          {
            //self::insertPrivateNote($incident, "HH igual a Reemplazo" );
            $incident->CustomFields->c->predictiondata='SAMEHH';
            $incident->StatusWithType->Status->ID                  = 1 ;  //  Ingresado
            $incident->Save();
            return;
          }
          
          
          $array_post = array(
            'usuario' => 'appmind',
            'accion'  => 'info_hh2',
            'datos'   => array(
                'id_hh'   => $incident->CustomFields->c->hh_replacement
            )
          );


          $json_data_post = json_encode($array_post);
          $json_data_post = base64_encode($json_data_post);
          //self::insertPrivateNote($incident, $json_data_post );
          //self::insertPrivateNote($incident, $cfg->Value );
          
          //self::insertPrivateNote($incident, "JSON enviado : " . $json_data_post);
          
          
          $postArray      = array('data' => $json_data_post);
          
          $result         = ConnectUrl::requestPost($cfg->Value, $postArray);
          //self::insertPrivateNote($incident, $result );
          //self::insertPrivateNote($incident, "[" .json_encode($result)."]" );
          
          if ($result != FALSE)
          {
              $arr_json = json_decode($result, TRUE);
              if ($arr_json != false)
              {
                  if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json))))
                  {
                      $respuesta  = base64_decode($arr_json['respuesta']);

                      switch ($arr_json['resultado'])
                      {
                          case "true":

                              $array_hh_data = json_decode(utf8_encode($respuesta), TRUE);
                              $array_hh_data           = $array_hh_data['respuesta'];

                              if (!is_array($array_hh_data))
                              {
                                  $message = "ERROR: Estructura JSON encriptado No valida " . PHP_EOL;
                                  $message .= "JSON: " . $json_hh;
                                  $bannerNumber = 3;
                                  break;
                              }
                              //self::insertPrivateNote($incident, "[" .$array_hh_data['Nombre Cliente']."]" );
                              if ($array_hh_data['Nombre Cliente']=='Org. Dimacofi S.A.') 
                              {
                       
                                //self::insertPrivateNote($incident, "HH esta disponible");
                                $incident->CustomFields->c->predictiondata='ok';
                                $incident->StatusWithType->Status->ID                  = 1 ;  //  Ingresado
                                $incident->Save();
                                return;
                              }
                              else
                              {
                                //self::insertPrivateNote($incident, "HH Ya esta en Cliente" );
                                $incident->CustomFields->c->predictiondata='HHCLI';
                                $incident->StatusWithType->Status->ID                  = 1 ;  //  Ingresado
                                $incident->Save();
                                return;
                              }
                              
                              $hh_marca                = $array_hh_data['Marca'];
                              $hh_sla                  = $array_hh_data['SLA'];
                              $sla_hh_rsn              = $array_hh_data['RSN'];
                              $hh_modelo               = $array_hh_data['Modelo'];
                              $hh_convenio             = $array_hh_data['Convenio'];
                              $array_hh_contadores     = $array_hh_data['Contadores'];
                              $array_hh_direccion_id   = $array_hh_data['Direccion'];
                              $hh_tipo_contrato        = $array_hh_data['TipoContrato'];
                              $serie_hh                = $array_hh_data['Serie'];
                              $numero_delfos           = $array_hh_data['delfos'];
                              $bool_convenio_insumos   = $array_hh_data['convenio_insumos'];
                              $bool_convenio_corchetes = $array_hh_data['convenio_corchetes'];
                              $inventoryItemId         = $array_hh_data['inventory_item_id'];
                              $codeItem                = $array_hh_data['code_item'];
                              $a_suppliers             = $array_hh_data['suppliers'];
                              $a_suppliers_full        = $array_hh_data['suppliers_full'];
                              $Rut                     = $array_hh_data['Rut'];
                              $contract_number       = $array_hh_data['contract_number'];
                              $solution_type         = $array_hh_data['solution_type'];
                              $sub_type              = $array_hh_data['sub_type'];

                              break;


                          case "false":
                              $message      = "ERROR: Servicio responde con fallo " . PHP_EOL;
                              $bannerNumber = 3;
                              break;
                          default:
                              $message       = "ERROR: Respuesta fallida " . PHP_EOL;
                  
                              $array_hh_data = json_decode(utf8_encode($respuesta), true);
                              $message      .= "JSON: " . $array_hh_data['msg'];
                              $bannerNumber  = 3;
                              break;
                      }
                  }
                  else
                  {
                      $message      = "ERROR: Estructura JSON No valida " . PHP_EOL;
                      $message     .= "JSON: " . $result;
                      $bannerNumber = 3;
                  }
              }
              else
              {
                  $message      = "ERROR: Problema en la decodificación del JSON " . PHP_EOL . "Respuesta: " . $result . PHP_EOL;
                  $bannerNumber = 3;
              }
          }
          
         
          $hh = RNCPHP\Asset::first("SerialNumber = '" . $incident->CustomFields->c->hh_replacement ."'" );


          //self::insertPrivateNote($incident, "1 [" .json_encode($hh)."]" );
          
          if( !is_null($hh))
          {

            $incident->CustomFields->c->predictiondata='ok';
          }
          else
          {
            $incident->CustomFields->c->predictiondata='NOHH';
          }
          $incident->StatusWithType->Status->ID                  = 1 ;  //  Ingresado
          $incident->Save();
        }
        catch (RNCPHP\ConnectAPIError $err )
        {
           $message = "Error ".$e->getMessage();
           self::insertPrivateNote($incident, "Error Query: ".$message);

        }


    }

    static function insertPrivateNote($incident, $textoNP)
    {
        try
        {

           
          $incident->Threads = new RNCPHP\ThreadArray();
          $incident->Threads[0] = new RNCPHP\Thread();
          $incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
          $incident->Threads[0]->EntryType->ID = 1; // Used the ID here. See the Thread object for definition
          $incident->Threads[0]->Text = $textoNP;
          $incident->Save(RNCPHP\RNObject::SuppressAll);
        }
        catch (RNCPHP\ConnectAPIError $err)
        {
          $incident->CustomFields->c->shipping_instructions=$incident->CustomFields->c->shipping_instructions . 'Error';
        
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            return FALSE;
        }
    }

}
