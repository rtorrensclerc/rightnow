<?php
namespace Custom\Models;

use RightNow\Connect\v1_3 as RNCPHP;

class ConnectUrl extends \RightNow\Models\Base
{
  public $msgError = 'error';
  private $headers = '';

  /**
   *
   */
  public function __construct()
  {
    parent::__construct();
    if (!function_exists("\curl_init"))
    {
        \load_curl();
    }
    //load_curl();
  }


  public function geToken()
    {
        try 
        {
            //$url = "https://api.dimacofi.cl/token";
            $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
            $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
            $data           = array("grant_type" => "client_credentials");
            $consumerKey    = "yh8wgLIb4RLIHwQ868CIifi2EYca"; // Prod 
            $consumerSecret = "bfaZkjfdIWoEtiXoDbo4E_EPpAka"; // Prod
         
            $url_token=$cfg2->Value . '/token';

            $service = $this->CI->ConnectUrl->requestCURLByPost($url_token, $data, $consumerKey . ":" . $consumerSecret);

            if (is_bool($service)) 
            {
                $this->errorMessage = "Error obteniendo token Dimacofi " . $this->CI->ConnectUrl->getResponseError();
                return FALSE;
            } 
            else 
            {
                return $service;
            }
        } 
        catch (RNCPHP\ConnectAPIError $err) 
        {
            $this->errorMessage  = "Codigo : " . $err->getCode() . " " . $err->getMessage();
            return FALSE;
        }
    }
  /**
   *
   */


  public function requestGet($url, $a_headers = NULL)
  {
    $error = "";

    $ch = curl_init();

    if($a_headers && is_array($a_headers) && count($a_headers) > 0)
    {
      curl_setopt($ch, CURLOPT_HTTPHEADER, $a_headers);
    }

    // curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: location=development%7EYKVsn2SNfp90k_6feJl0mUSZQplYmUCZXJlQmUiZVJmfBxkHHyEbITUXLTc9JSE5LTMnOZ8Hnwc%21"));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_PROXY, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $data = curl_exec($ch);

    if (curl_errno($ch)) {
      $info           = curl_getinfo($ch);
      $this->msgError = curl_errno($ch)." ".curl_error($ch);

      curl_close($ch);
      return false;
    }

    if ($data != false) {
      $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      curl_close($ch);

      if ($statusCode != '200') {
        $this->msgError = 'No se pudo resolver la petición. Código de error: ' . $statusCode;
        return false;
      } else {
        return $data;
      }
    } else {
      curl_close($ch);
      $this->msgError = 'No se pudo resolver la petición';
      return false;
    }
  }

  /**
   *
   */
  public function requestPost($url, $postArray, $typeRequest = 'CURL')
  {
    switch ($typeRequest) {
      case 'CURL':
        return $this->requestCURLByPost($url, $postArray);
        break;
      case 'FileGetContent':
        return $this->requestFileGetContentByPost($url, $postArray);
        break;
      default:
        return $this->requestCURLByPost($url, $postArray);
        break;
    }
  }

  /**
   *
   */
  public function requestFileGetContentByPost($url, $postArray)
  {
    $headers = @get_headers($url);
    $statusCode = substr($headers[0], 9, 3);
    if ($statusCode != '200') {
      $this->msgError = 'No se pudo resolver la petición. Código de error: ' . $statusCode;
      return false;
    }

    if (is_array($postArray)) {
      $postString =  http_build_query($postArray);
    }

    $opts = array('http' =>
      array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' =>  $postString
      )
    );

    $context = stream_context_create($opts);
    $result  = file_get_contents($url, false, $context);

    return $result;
  }

  /**
   *
   */
  public function requestCURLByPost($url, $postArray, $userpass = null)
  {
    # Form data string
    if (is_array($postArray)) {
      $postString = http_build_query($postArray, '', '&');
    }

    //load_curl();
    $ch = curl_init($url);

    # Setting our options

    if (!empty($userpass)) {
      curl_setopt($ch, CURLOPT_USERPWD, $userpass);
    }

    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 500);
    //curl_setopt($ch, 156, 500);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_HEADER, 1);

    # Get the response
    $response = curl_exec($ch);

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    $this->headers = $headers;

    if (curl_errno($ch)) {
      $info = curl_getinfo($ch);
      $this->msgError = curl_error($ch);
      //$this->$msgError .='<br>Tiempo ' . $info['total_time'] . ' segundos en recibir la respuesta de la siguiente URL: ' . $info['url'];
      curl_close($ch);
      return false;
    }

    if ($response != false) {
      $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      if ($statusCode != '200') {
        $this->msgError = 'No se pudo resolver la petición. Código de error: ' . $statusCode;
        return false;
      } else {
        return $body;
      }
    } else {
      curl_close($ch);
      $this->msgError = 'Error, respuesta sin datos.';
      return false;
    }
  }

  /**
   *
   */
  public function requestCURLJsonRaw($url, $jsonDataEncoded, $tokenHeader = null)
  {
    //load_curl();
    $ch = curl_init($url);

    if (!empty($tokenHeader))
    {
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , "Authorization: Bearer ".$tokenHeader ));
    }
    else
    {
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    }

    // Tell cURL that we want to send a POST request.
    curl_setopt($ch, CURLOPT_POST, 1);

    // Attach our encoded JSON string to the POST fields.
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);

    // Set the content type to application/json

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);

    // Execute the request
    $data = curl_exec($ch);

    if (curl_errno($ch))
    {
      $info       = curl_getinfo($ch);
      $this->msgError = curl_errno($ch)." ".curl_error($ch);

      curl_close($ch);

      return false;
    }
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($data != false || $data != "")
    {
      curl_close($ch);

      if ($statusCode  != 200)
      {
        $this->msgError = 'No se pudo resolver la petición. Código de error: ' . $statusCode;

        return false;
      }
      else
      {
        return $data;
      }
    }
    else
    {
      curl_close($ch);
      $this->msgError   = "Respuesta sin datos. No es posible acceder a la url {$url}, código de estado {$statusCode}.";
      return false;
    }
  }


  public function requestCURLJsonRaw2($url, $jsonDataEncoded)
  {
    //load_curl();
    $ch = curl_init($url);

    
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    

    // Tell cURL that we want to send a POST request.
    curl_setopt($ch, CURLOPT_POST, 1);

    curl_setopt($ch, CURLOPT_ENCODING, "");
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

    // Attach our encoded JSON string to the POST fields.
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);

    // Set the content type to application/json
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);

    // Execute the request
    $data = curl_exec($ch);

    if (curl_errno($ch))
    {
      $info       = curl_getinfo($ch);
      $this->msgError = curl_errno($ch)." ".curl_error($ch);

      curl_close($ch);

      return false;
    }
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($data != false || $data != "")
    {
      curl_close($ch);

      if ($statusCode  != 200)
      {
        $this->msgError = 'No se pudo resolver la petición. Código de error: ' . $statusCode;

        return false;
      }
      else
      {
        return $data;
      }
    }
    else
    {
      curl_close($ch);
      $this->msgError   = "Respuesta sin datos. No es posible acceder a la url {$url}, código de estado {$statusCode}.";
      return false;
    }
  }

  public function getResponseError()
  {
    return $this->msgError;
  }

  public function getLastHeaders()
  {
    return $this->headers;
  }
}
