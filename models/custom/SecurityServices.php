<?php
namespace Custom\Models;
use RightNow\Connect\v1_3 as RNCPHP;

class SecurityServices extends \RightNow\Models\Base
{
    public $error = array ('numberID' => null , 'message' => null);
    public $url   = "https://api-test.dimacofi.cl/token"; //TEST
    //public $url   = "https://api.dimacofi.cl/token"; //PROD

    function __construct()
    {
        parent::__construct();
    }


    /**
    * Servicio que token de dimacofi
    *
    * @param void
    *
    * @return JSON
    */
    public function getToken()
    {
      try
      {
          $CI =& get_instance();
          $CI->load->model('custom/ConnectUrl');

          $data           = array("grant_type" => "client_credentials");
          $consumerKey    = "0p3Tjnwh_PE4vEKgjnMkOdoEfDIa";
          //$consumerKey    = "Lew2akNsSYkM9j92eQvU50_BfFEa"; //Lew2akNsSYkM9j92eQvU50_BfFEa
          $consumerSecret = "tc_IU8drl8L5_MugEeG7yXY65yEa";
          //$consumerSecret = "uP1Q_Coeio8w_nytC_MuTBfENhga";

          $service = $CI->ConnectUrl->requestCURLByPost($this->url, $data, $consumerKey.":".$consumerSecret);

          if(is_bool($service))
          {
              $this->error['message'] = "Error obteniendo token Dimacofi ".$CI->ConnectUrl->getResponseError();
              return false;
          }
          else
          {
              return $service;
          }

      }
      catch (RNCPHP\ConnectAPIError $err )
      {
          $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
          return false;
      }
    }


    public function loadLead($info_lead, $infoContact)
    {
      try
      {
        $jsonToken = $this->getToken();
        if ($jsonToken !== false)
        {
          $a_jsonToken = json_decode($jsonToken, true);
          if (empty($a_jsonToken["access_token"]))
          {

            $this->error['message'] = "Json de token invalido ".$jsonToken;
            return false;
          }

          echo $a_jsonToken["access_token"]."<br>";
          $token                   = $a_jsonToken["access_token"];
          //Información del lead
          $a_json['title']          = $info_lead['title'];
          $a_json['description']    = $info_lead['description'];
          $a_json['assignedArea']   = $info_lead['assignedArea'];
          $a_json['bussinessType']  = $info_lead['bussinessType'];

          //información de contacto
          $a_json['contactName']    = $infoContact['contactName'];
          $a_json['contactMail']    = $infoContact['contactMail'];
          $a_json['contactPhone']   = $infoContact['contactPhone'];
          $a_json['customerName']   = $infoContact['customerName'];
          $a_json['customerNumber'] = $infoContact['customerNumber'];
          $a_json['ownerLead']      = 0;
          $a_json['channelType']    = $infoContact['channelType'];
          $a_json['stateName']      = $infoContact['stateName'];
          $a_json['cityName']       = $infoContact['cityName'];

          $jsonDataEncoded = json_encode($a_json);

          $url     = "https://api-test.dimacofi.cl/crm/1.0.1/salesApi/resources/leads";
          $CI      =&get_instance();
          $CI->load->model('custom/ConnectUrl');


          //echo $jsonDataEncoded;
          $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded, $token);

          if(is_bool($service))
          {
              $this->error['message'] = "Error Servicio de Lead ".$CI->ConnectUrl->getResponseError();
              return false;
          }
          else
          {
              return $service;
          }
        }

      }
      catch (RNCPHP\ConnectAPIError $err )
      {
          $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
          return false;
      }
    }


    public function getLastError()
    {
      return $this->error['message'];
    }

}
