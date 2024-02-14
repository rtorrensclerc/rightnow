<?php
/*
 * Given a CPHP object, appends a string to the specified field and saves the
 * object.
 */
namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;

class ConnectUrl
{
    public static $msgError        = 'error';
    //public static $url_token       = "https://api.dimacofi.cl/token";
    public static $total_time_curl = 0;

    function __construct()
    {
    }

    static function geTokenInfo()
    {
      $data           = array("grant_type" => "client_credentials");
      $consumerKey    = "yh8wgLIb4RLIHwQ868CIifi2EYca"; // Prod 
      $consumerSecret = "bfaZkjfdIWoEtiXoDbo4E_EPpAka"; // Prod

      $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
      $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
      $url_token=$cfg2->Value . '/token';
      $jsonToken   = self::requestCURLByPost($url_token, $data, $consumerKey.":".$consumerSecret);
      $a_jsonToken = json_decode($jsonToken, true);

      if (is_array($a_jsonToken))
      {
        $tokenHeader = $a_jsonToken["access_token"];
        if (!empty($tokenHeader))
          return $tokenHeader;
        else
        {
          self::$msgError = "No se encontró estructura token ". $jsonToken;
          return false;
        }
      }
      else
      {
        return false;
      }
    }


    static function geToken2()
    {
      $data           = array("grant_type" => "client_credentials");
      $consumerKey    = "Lew2akNsSYkM9j92eQvU50_BfFEa"; // Prod 
      $consumerSecret = "uP1Q_Coeio8w_nytC_MuTBfENhga"; // Prod

      $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
      $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
      $url_token=$cfg2->Value . '/token';
      $jsonToken   = self::requestCURLByPost($url_token, $data, $consumerKey.":".$consumerSecret);
      $a_jsonToken = json_decode($jsonToken, true);

      if (is_array($a_jsonToken))
      {
        $tokenHeader = $a_jsonToken["access_token"];
        if (!empty($tokenHeader))
          return $tokenHeader;
        else
        {
          self::$msgError = "No se encontró estructura token ". $jsonToken;
          return false;
        }
      }
      else
      {
        return false;
      }
    }

    static function geToken()
    {
      $data           = array("grant_type" => "client_credentials");
      $consumerKey    = "";
      $consumerSecret = "";
      $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
      $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
      if (strstr($cfg2->Value,"8290"))
      {
          $consumerKey    = "gaIIMLvsZM6tMv7G6WgDeAdDb7Ma"; // TEST 
          $consumerSecret = "5cTKPPLY2mCsiR23jvwB63j446ka"; // TEST
      }
      else
      {
          $consumerKey    = "yh8wgLIb4RLIHwQ868CIifi2EYca"; // Prod 
          $consumerSecret = "bfaZkjfdIWoEtiXoDbo4E_EPpAka"; // Prod
      }

      $url_token=$cfg2->Value . '/token';

      $jsonToken   = self::requestCURLByPost($url_token, $data, $consumerKey.":".$consumerSecret);
      $a_jsonToken = json_decode($jsonToken, true);

      if (is_array($a_jsonToken))
      {
        $tokenHeader = $a_jsonToken["access_token"];
        if (!empty($tokenHeader))
          return $tokenHeader;
        else
        {
          self::$msgError = "No se encontró estructura token ". $jsonToken;
          return false;
        }
      }
      else
      {
        return false;
      }

    }

    static function geTokenTest()
    {
      $data           = array("grant_type" => "client_credentials");
      $consumerKey    = "gaIIMLvsZM6tMv7G6WgDeAdDb7Ma"; // TEST   
      $consumerSecret = "5cTKPPLY2mCsiR23jvwB63j446ka"; // TEST
      $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
      $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

      $url_token = "https://api.dimacofi.cl:8290/token";
      $jsonToken   = self::requestCURLByPost($url_token, $data, $consumerKey.":".$consumerSecret);
      $a_jsonToken = json_decode($jsonToken, true);

      if (is_array($a_jsonToken))
      {
        $tokenHeader = $a_jsonToken["access_token"];
        if (!empty($tokenHeader))
          return $tokenHeader;
        else
        {
          self::$msgError = "No se encontró estructura token ". $jsonToken;
          return false;
        }
      }
      else
      {
        return false;
      }

    }
    static function requestPost($url, $postArray, $typeRequest ='CURL')
    {
        switch ($typeRequest) {
            case 'CURL':
                return self::requestCURLByPost($url, $postArray);
                break;
            case 'FileGetContent':
                return self::requestFileGetContentByPost($url, $postArray);
                break;
            default:
                return self::requestCURLByPost($url, $postArray);
                break;
        }
    }

    static function requestFileGetContentByPost($url, $postArray)
    {

        $headers = @get_headers($url);
        $statusCode = substr($headers[0], 9, 3);
        if($statusCode != '200'){
            self::$msgError = 'No se pudo resolver la petición a la URL, codigo de Error: '. $statusCode;
            return false;
        }

        if (is_array($postArray))
            $postString =  http_build_query($postArray);

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' =>  $postString
            )
        );

        $context  = stream_context_create($opts);
        $result   = file_get_contents($url, false, $context);



        return $result;
    }

    static function requestCURLByPost($url, $postArray, $userpass = null)
    {
        # Form data string
        if (is_array($postArray))
            $postString = http_build_query($postArray, '', '&');

        if (!function_exists("\curl_init"))
        {
          load_curl();
        }

        $ch = curl_init($url);

        if (!empty($userpass)) {
          curl_setopt($ch, CURLOPT_USERPWD, $userpass);
        }
        # Setting our options

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 500);
        //curl_setopt($ch, 156, 500);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        # Get the response
        $response              = curl_exec($ch);
        $info                  = curl_getinfo($ch);
        self::$total_time_curl = $info['total_time'];

        if(curl_errno($ch))
        {
            $info = curl_getinfo($ch);
            self::$msgError = curl_error($ch);
            //self::$msgError .='<br>Tiempo ' . $info['total_time'] . ' segundos en recibir la respuesta de la siguiente URL: ' . $info['url'];
            curl_close($ch);
            return false;
        }

        if ($response != false)
        {
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($statusCode != '200')
            {
                self::$msgError = 'No se pudo resolver la petición a la URL, codigo de Error: '. $statusCode;
                return false;
            }
            else
                return $response;
        }
        else
        {
            curl_close($ch);
            self::$msgError = 'No se pudo resolver la petición a la URL';
            return false;
        }
    }

    static function requestCURLJsonRaw($url, $jsonDataEncoded, $tokenHeader = null)
    {

      if (!function_exists("\curl_init"))
      {
        load_curl();
      }

      $ch = curl_init($url);


      if (!empty($tokenHeader))
      {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , "Authorization: Bearer ".$tokenHeader ));
        // return $tokenHeader;
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
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);

      // Execute the request
      $response = curl_exec($ch);

      if(curl_errno($ch))
      {
          $info = curl_getinfo($ch);
          self::$msgError = curl_error($ch);
          //self::$msgError .='<br>Tiempo ' . $info['total_time'] . ' segundos en recibir la respuesta de la siguiente URL: ' . $info['url'];
          curl_close($ch);
          return false;
      }

      if ($response != false)
      {
          $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);
          if (!in_array($statusCode, array('200', '201')))
          {
              self::$msgError = 'No se pudo resolver la petición a la URL, codigo de Error: '. $statusCode." data ".$response;
              return false;
          }
          else
              return $response;
      }
      else
      {
          curl_close($ch);
          self::$msgError = 'No se pudo resolver la petición a la URL';
          return false;
      }
    }

      static function requestCURLJsonRawDesarrollo($url, $jsonDataEncoded, $timeout = null, $developmentFlag = false)
    {
        if (!function_exists("\curl_init"))
        {
            load_curl();
        }

        $ch = curl_init($url);

        // Tell cURL that we want to send a POST request.
        curl_setopt($ch, CURLOPT_POST, 1);

        // Attach our encoded JSON string to the POST fields.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);

        // Coookie Development
        if ($developmentFlag === true)
        curl_setopt($ch, CURLOPT_COOKIE, 'location=development%7EZlV5RHFDelN4ODV2b3JKX0kwRDNyQ1JDREhoQllkRXdBaEJuc2JySlFmS0ZmdHphZEdhY0kyeUJlT1JsbUdXQlcwRkV6d3hwR2ZqM2pxQzdIa2d5cE1XS1VVYU45RzIzTjNUN35qMk10N0V0TkJ3dG5VYkx4VE93WUpDaVR3Qm1STmludWFuQWNqeUJHRTJnbHcxZjluV09ERX5oQUo5TjlD');

        // Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,
            CURLOPT_SSL_VERIFYPEER,
            false
        );

        if ($timeout)
        {
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        }
        else
        {
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        }

        // Execute the request
        $data = curl_exec($ch);

        if (curl_errno($ch))
        {
            $info           = curl_getinfo($ch);
            self::$msgError = curl_errno($ch) . " " . curl_error($ch);
            curl_close($ch);
            return false;
        }

        if ($data != false)
        {
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($statusCode != '200')
            {
                self::$msgError = "No se pudo resolver la petición a la URL {$url}, código de Error: " . $statusCode;
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
            self::$msgError = "No se pudo resolver la petición a la URL " . $url;
            return false;
        }
    }



    static function requestCURLJsonRaw2($url, $jsonDataEncoded, $tokenHeader = null)
    {

      if (!function_exists("\curl_init"))
      {
        load_curl();
      }

      $ch = curl_init($url);


      if (!empty($tokenHeader))
      {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , "Authorization: Bearer ".$tokenHeader ));
        // return $tokenHeader;
      }
      else
      {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
      }

      // Tell cURL that we want to send a POST request.
      curl_setopt($ch, CURLOPT_POST, 1);
      # Form data string
      if (is_array($postArray))
      $postString = http_build_query($postArray, '', '&');

      // Attach our encoded JSON string to the POST fields.
      curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);

      // Set the content type to application/json

      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);

      // Execute the request
      $response = curl_exec($ch);

      if(curl_errno($ch))
      {
          $info = curl_getinfo($ch);
          self::$msgError = curl_error($ch);
          //self::$msgError .='<br>Tiempo ' . $info['total_time'] . ' segundos en recibir la respuesta de la siguiente URL: ' . $info['url'];
          curl_close($ch);
          return false;
      }

      if ($response != false)
      {
          $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);
          if (!in_array($statusCode, array('200', '201')))
          {
              self::$msgError = 'No se pudo resolver la petición a la URL, codigo de Error: '. $statusCode." data ".$response;
              return false;
          }
          else
              return $response;
      }
      else
      {
          curl_close($ch);
          self::$msgError = 'No se pudo resolver la petición a la URL';
          return false;
      }
    }

    static function requestGet($url, $user = null, $passwd = null, $timeout = 2)
    {
      load_curl();
      $ch = curl_init();

      if(!empty($user) && !empty($passwd))
      {
         curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
         curl_setopt($ch, CURLOPT_USERPWD, "{$user}:{$passwd}");
      }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');

      $data = curl_exec($ch);

      if (curl_errno($ch))
      {
          $info               = curl_getinfo($ch);
          self::$msgError     = curl_errno( $ch )." ".curl_error($ch);
          curl_close($ch);
          return false;
      }

      if ($data != false)
      {
          $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);
          if ($statusCode != 200)
          {
            if($statusCode === 401)
                self::$msgError = "Error en la autenticación, favor verifique usuario y contraseña.";
            else
                self::$msgError = 'No se pudo resolver la petición a la URL, codigo de Error: '. $statusCode;
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
          self::$msgError = 'No se pudo resolver la petición a la URL';
          return false;
      }

    }

    static function getResponseError ()
    {
        return self::$msgError;
    }

    static function getResponseTime()
    {
        return self::$total_time_curl;
    }

}
