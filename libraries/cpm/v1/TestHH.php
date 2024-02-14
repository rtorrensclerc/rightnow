<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;
use RightNow\Connect\v1_3 as RNCPHP;


require_once "ConnectUrl.php";

class TestHH
{
    CONST URL_GET_HH   = "http://190.14.56.27/dts/rn_integracion/rntelejson.php";
    
    static function HandleIncident($runMode, $action, $incident, $cycle)
    {
        if ($cycle !== 0) return;

        try
        {
          $id_hh = $incident->CustomFields->c->id_hh; //168665

          //Inicio - Lineas de testing
          self::insertPrivateNote($incident, "ID de HH ".$id_hh);
          //Fin - Lineas de testing

          $array_post     = array('usuario' => 'appmind',
                                  'accion' => 'info_hh',
                                  'datos'=> array('id_hh'=> $id_hh)
                                  );

          $json_data_post = json_encode($array_post);
          $json_data_post = base64_encode($json_data_post);

          $postArray = array ('data' => $json_data_post);
          $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
          $result = ConnectUrl::requestPost($cfg->Value, $postArray);

          if ($result != false)
          {
              $arr_json = json_decode($result, true);
              if ($arr_json != false)
              {
                  if ((array_key_exists('resultado', $arr_json) and (array_key_exists('respuesta', $arr_json)) ))
                  {
                      $respuesta  = base64_decode($arr_json['respuesta']);

                      switch ($arr_json['resultado'])
                      {
                          case "true":

                              //$json_hh = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                              $json_hh = $respuesta;
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
                              //$inc_hh                = new HHIncidentModel($incident);
                              $hh_result             = self::saveInfoHH($incident, $hh_marca, $hh_modelo, $hh_sla,$sla_hh_rsn, $hh_convenio, $hh_tipo_contrato ,$array_hh_contadores, $array_hh_direccion_id,$serie_hh,$numero_delfos);


                              self::insertPrivateNote($incident, $respuesta);

                              if ($hh_result == false)
                              {
                                $message      = "ERROR: ". $inc_hh->getLastError();
                                $bannerNumber = 3;
                              }
                              else
                              {
                                /*
                                $url2              = "http://soportedimacoficl.custhelp.com/cc/AjaxCustom/updateAsset";
                                $postArray2        = array("incidentId" =>  $incident->ID ,  "hh_marca" => $hh_marca, "hh_modelo" >= $hh_modelo,  "comtactId" =>  $incident->PrimaryContact->ID);
                                $result_asset       = ConnectUrl::requestPost($url2, $postArray2);
                                if ($result_asset  == false)
                                {
                                  $message = ConnectUrl::getResponseError();
                                  self::insertPrivateNote($incident, " Error ".$message );
                                }
                                else
                                {
                                  self::insertPrivateNote($incident, "Asset info : ".$result_asset);
                                }
                                */
                                $bannerNumber = 1;
                                $message      = "Los datos de HH han sido ingresados correctamente";
                              }

                              break;
                          case False:
                              $message      = "ERROR: Servicio responde con fallo ".PHP_EOL;
                              $bannerNumber = 3;
                              break;
                          default:
                              $message           = "ERROR: Respuesta fallida ".PHP_EOL;
                              //$json_hh = Blowfish::decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
                              $json_hh           = $respuesta;
                              $array_hh_data     = json_decode(utf8_encode($json_hh),true);
                              $message           .= "JSON: ". $array_hh_data['msg'];
                              $bannerNumber      = 3;
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
          else
          {
            $message = "ERROR: ".ConnectUrl::getResponseError();
            $bannerNumber = 3;
          }
          self::insertPrivateNote($incident, $message);
          self::insertBanner($incident, $bannerNumber);

        }
        catch (RNCPHP\ConnectAPIError  $e)
        {
             $message = "Error ".$e->getMessage();
             self::insertPrivateNote($incident, $message);
        }

    }

    static function saveInfoHH($incident, $marca, $modelo, $sla,$sla_rsn, $bool_convenio, $hh_tipo_contrato, $array_contadores, $array_direcciones,$serie_hh,$numero_delfos)
    {
        try
        {
            RNCPHP\ConnectAPI::commit();
            $incident->CustomFields->c->marca_hh           = $marca;
            $incident->CustomFields->c->modelo_hh          = $modelo;
            $incident->CustomFields->c->convenio           = (int) $bool_convenio;
            $incident->CustomFields->c->tipo_contrato      = $hh_tipo_contrato;
            $incident->CustomFields->c->sla_hh             = $sla;
            $incident->CustomFields->c->sla_hh_rsn         = $sla_rsn;
            $incident->CustomFields->c->cliente_bloqueado  = (int) $array_direcciones['Bloqueado'];
            $incident->CustomFields->c->serie_maq          = $serie_hh;
            $incident->CustomFields->c->numero_delfos      = $numero_delfos;
            $id_ebs_direccion                              = $array_direcciones['ID_direccion'];
            $array_Direccion_obj                           = RNCPHP\DOS\Direccion::find('d_id = '. $id_ebs_direccion);
            if (is_array($array_Direccion_obj) and is_object($array_Direccion_obj[0]))
                $incident->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];

            //Creando HH

            //$asset = RNCPHP\Asset::first( "SerialNumber = '".$incident->CustomFields->c->id_hh."'");
            /*
            $hh = $incident->CustomFields->c->id_hh;
            //$asset = RNCPHP\Asset::find("SerialNumber = '{$hh}'");
            $asset = RNCPHP\Asset::fetch(21880);
            if (!is_object($asset))
            {
              $ObjAsset               = new RNCPHP\Asset;
              $nameHH                 = $hh."-".$marca."-".$modelo;
              $ObjAsset->Name         = substr($nameHH, 0, 80);
              $ObjAsset->Contact      = $incident->PrimaryContact;
              $ObjAsset->Product      = 2;
              $ObjAsset->SerialNumber = $hh;
              $ObjAsset->save(RNCPHP\RNObject::SuppressAll);
              $incident->Asset = $ObjAsset;
            }
            else
            {
              $incident->Asset = $asset;
            }
            */

            $incident->save(RNCPHP\RNObject::SuppressAll);

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

    static function updateAsset($incident)
    {
        try
        {
            $asset = RNCPHP\Asset::first( "SerialNumber = '".$incident->CustomFields->c->id_hh."'");
            if (empty($asset))
            {
                $asset               = new RNCPHP\Asset;
                $nameHH              = $incident->CustomFields->c->id_hh."-".$incident->CustomFields->c->marca_hh."-".$incident->CustomFields->c->modelo_hh;
                $asset->Name         = substr($nameHH, 0, 80);
                $asset->Contact      = $incident->PrimaryContact;
                $asset->Product      = 2;
                $asset->SerialNumber = $incident->CustomFields->c->id_hh;
                $asset->save(RNCPHP\RNObject::SuppressAll);

            }
            $asset->CustomFields->DOS->Direccion =  $incident->CustomFields->DOS->Direccion;
            $asset->save(RNCPHP\RNObject::SuppressAll);
            $incident->Asset = $asset;
            $incident->save(RNCPHP\RNObject::SuppressAll);

        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $error = "Problema al generar activo | Codigo : ".$err->getCode()." ".$err->getMessage();
            self::insertPrivateNote($incident, $error);
            return false;
        }
    }

    static function saveCounters($incident, $array_counters)
    {
      if (is_array($array_counters))
      {
        try
        {
          foreach ($array_counters as $counter)
          {
            //Contadores
            $count_id               = $counter['ID'];
            $count_tipo             = $counter['Tipo'];
            $count_valor            = $counter['Valor'];
            $contador               = new RNCPHP\DOS\Contador();
            $contador->ContadorID   = $count_id;
            $contador->Valor        = $count_valor;
            $contador->Incident     = $incident;
            $contador->TipoContador = RNCPHP\DOS\TipoContador::fetch($counter['Tipo']);
            $contador->Asset        = $incident->Asset;
            $contador->save(RNCPHP\RNObject::SuppressAll);
          }
          return true;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
          $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
          return false;
        }
      }
      else
      {
        $this->error = "Estructura no Valida en los contadores";
        return false;
      }
    }

    static function insertPrivateNote($incident, $textoNP)
    {
        try
        {
          $incident->Threads                   = new RNCPHP\ThreadArray();
          $incident->Threads[0]                = new RNCPHP\Thread();
          $incident->Threads[0]->EntryType     = new RNCPHP\NamedIDOptList();
          $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
          $incident->Threads[0]->Text          = $textoNP;
          $incident->Save(RNCPHP\RNObject::SuppressAll);
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
          $incident->Threads                   = new RNCPHP\ThreadArray();
          $incident->Threads[0]                = new RNCPHP\Thread();
          $incident->Threads[0]->EntryType     = new RNCPHP\NamedIDOptList();
          $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
          $incident->Threads[0]->Text          = "Error" . $err->getMessage();
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
            $texto = "HH no pudo ser asignada";

        $incident->Banner->Text = $texto;
        $incident->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
        $incident->Save(RNCPHP\RNObject::SuppressAll);

    }

}
