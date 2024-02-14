<?php
/* 
 * Given a CPHP object, appends a string to the specified field and saves the
 * object.
 */
namespace Custom\Libraries;

use RightNow\Connect\v1_2 as RNCPHP;

class ConnectUrl
{
    public static $msgError = 'error';
    
    function __construct()
    {
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

    static function requestCURLByPost($url, $postArray)
    {
        # Form data string
        if (is_array($postArray))
            $postString = http_build_query($postArray, '', '&');

        load_curl();
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
            self::$msgError = curl_error($ch).'<br>Tiempo ' . $info['total_time'] . ' segundos en recibir la respuesta de la siguiente URL: ' . $info['url'];
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

    static function getResponseError ()
    {
        return self::$msgError;
    }
    
}