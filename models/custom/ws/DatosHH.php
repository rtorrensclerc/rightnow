<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_3 as RNCPHP;

class DatosHH extends \RightNow\Models\Base
{
    //public $error = '';
    public  $error          = array ('numberID' => null , 'message' => null);
    private $nro_referencia = '';
    private $url_ws         = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    // private $url_ws           = "http://190.14.56.27/public/rn_integracion/rntelejson.php"; //test

    function __construct()
    {
        parent::__construct();
        //\RightNow\Libraries\AbuseDetection::check();
        load_curl();
    }

    /**
    * Obtiene las del cliente mediante integración
    *
    * @param $rut {String} RUT de la organización
    */
    public function getHHsByOrganizationService($rut)
    {
      try
      {
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $array_post        = array('usuario' => 'Integer',
                                   'accion'  => 'getHHs',
                                   'datos'   => array('rut'=> $rut)
                                  );
        $json_data_post    = json_encode($array_post);
        $json_data_encoded = base64_encode($json_data_post);
        $postArray         = array ('data' => $json_data_encoded);
        //$result            = $this->requestCURLByPost($this->url_ws, $postArray);
        $result            = $this->requestCURLByPost($cfg->Value, $postArray);

        if ($result === false)
        {
          // Captura el error de CURL
          return false;
        }

        $arr_json = json_decode($result, true);

        if ((!array_key_exists('resultado', $arr_json) or (!array_key_exists('respuesta', $arr_json))))
        {
          $this->error['message']  = "ERROR: Estructura JSON No valida ".$result;
          return false;
        }

        switch ($arr_json['resultado'])
        {
          case 'true':
          case 'True':
            $contentResponse  = base64_decode($arr_json['respuesta']);
            return $contentResponse;
            break;
          case 'false':
            $this->error['message']  = "ERROR: Respuesta indica error ".$result;
            return false;
            break;
          default:
            $this->error['message']  = "Caso desconocido";
            return false;
            break;
        }
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }

    }

    public function getDatosHHInsumos($hh)
    {
      try
      {
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $array_post     = array('usuario' => 'appmind',
                                'accion'  => 'info_hh2',
                                'datos'   => array('id_hh'=> $hh)
                                );
        $json_data_post    = json_encode($array_post);
        $json_data_emcoded = base64_encode($json_data_post);
        $postArray         = array ('data' => $json_data_emcoded);
        //$result            = $this->requestCURLByPost($this->url_ws, $postArray);

        $result            = $this->requestCURLByPost($cfg->Value, $postArray);

        if ($result === false)
        {
          $this->error['message']  = "ERROR: Problemas de comunicación entre servicios.";
          //se captura el error de CURL
          return false;
        }

        $arr_json = json_decode($result, true);
        if ((!array_key_exists('resultado', $arr_json) or (!array_key_exists('respuesta', $arr_json))))
        {
          $this->error['message']  = "ERROR: Estructura JSON No valida ".$result;
          return false;
        }

        switch ($arr_json['resultado'])
        {
          case 'true':
            $contentResponse  = base64_decode($arr_json['respuesta']);
            return $contentResponse;
            break;
          case 'false':
            // $this->error['message']  = "ERROR: Respuesta indica error ".$result;
            $this->error['message']  = "HH no encontrado.";
            return false;
            break;
          default:
            $this->error['message']  = "Caso desconocido";
            return false;
            break;
        }


      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }

    }

    private function requestCURLByPost($url, $postArray)
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
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 1500);
      //curl_setopt($ch, CURLOPT_TIMEOUT, 4);
      # Get the response
      $response = curl_exec($ch);

      if(curl_errno($ch))
      {
        $info = curl_getinfo($ch);
        $this->error['message'] = curl_error($ch).'<br>Tiempo ' . $info['total_time'] . ' segundos en recibir la respuesta de la siguiente URL: ' . $info['url'];
        curl_close($ch);
        return false;
      }

      if ($response != false)
      {
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($statusCode != '200')
        {
          $this->error['message'] = 'No se pudo resolver la petición a la URL, codigo de Error: '. $statusCode;
          return false;
        }
        else
          return $response;
      }
      else
      {
        curl_close($ch);
        $this->error['message'] = 'No se pudo resolver la petición a la URL';
        return false;
      }
    }

    public function getLastError()
    {
      return $this->error['message'];
    }

    public function getNumberError()
    {
      return $this->error['numberID'];
    }

}
