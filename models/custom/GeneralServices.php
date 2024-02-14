<?php

namespace Custom\Models;

use RightNow\Connect\v1_3 as RNCPHP;

class GeneralServices extends \RightNow\Models\Base
{
    private $errorMessage = "";
    private $errorCode    = 0;

    function __construct()
    {
        parent::__construct();
        $this->CI->load->model("custom/ConnectUrl");
        $this->CI->load->model("custom/Contact");
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
            //$url = "https://api.dimacofi.cl/token";
            $consumerKey    = "";
            $consumerSecret = "";
            $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
            $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
            $data           = array("grant_type" => "client_credentials");
         
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
    public function getTokenTest()
    {
        try 
        {
            //$url = "https://api.dimacofi.cl/token";
            $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
            $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
            $data           = array("grant_type" => "client_credentials");
            $consumerKey    = "gaIIMLvsZM6tMv7G6WgDeAdDb7Ma"; // TEST 
            $consumerSecret = "5cTKPPLY2mCsiR23jvwB63j446ka"; // TEST
         
            //$url_token=$cfg2->Value . '/token';
            $url_token="https://api.dimacofi.cl:8290/token";
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

    public function getLastError()
    {
        return $this->errorMessage;
    }

    public function getLastErrorCode()
    {
        return $this->errorCode;
    }

    public function getListHH($contact_id)
    {
        try
        {
            $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
            $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

            $CI = get_instance();
            $obj_info_contact= $CI->session->getSessionData('info_contact');
            $contact = RNCPHP\Contact::fetch($contact_id);

            if($contact === FALSE)
                throw new \Exception($this->CI->Contact->getLastError(), 1);
            elseif (empty($contact->Organization)) 
                throw new \Exception("El contacto no tiene empresa asociada.", 1);

            // Se obtendrá un token
            //$jsonToken = $this->getToken();
            $jsonToken = $this->getTokenTest();
            if($jsonToken === FALSE)
                return FALSE;

            
            $a_jsonToken = json_decode($jsonToken, TRUE);

            if (empty($a_jsonToken["access_token"]))
                throw new \Exception("Json de token inválido {$jsonToken}", 1);

            $token = $a_jsonToken["access_token"];
            // $url   = "https://api.dimacofi.cl/apiEBS/GetListRutHH"; // Antiguo Servicio
            //$url = "https://api.dimacofi.cl/apiCloudMD/GetListRutHH";
            // Servicio Nuevo Muti RUT
            // 20200820 RTC
            //$url =$cfg2->Value . '/apiCloudMD/GetListMRutHH'; 
            $url = "https://api.dimacofi.cl:8290/apiCloudMD/GetListMRutHH";
            //$url = "https://api.dimacofi.cl/apiCloudMD/GetListMRutHH";
            // $org_rut   = "18870549-9";

            $organization = RNCPHP\Organization::fetch($obj_info_contact['Org_id']);

            //$org_rut = $contact->Organization->CustomFields->c->rut;
            $org_rut = $organization->CustomFields->c->rut;

            $a_request = array(
                "RUT" => $org_rut
            );

         
            $json_request = json_encode($a_request);
            $response     = $this->CI->ConnectUrl->requestCURLJsonRaw($url, $json_request, $token);
           /*  $response = '{
                "List": {
                    "data": [
                        {
                            "rut": "76570350-6",
                            "contrato_id": "28055",
                            "hh": "1745528",
                            "serial": "W862LB00296",
                            "marca": "IKON",
                            "nombre": "RICOH AFICIO  MP6002 - IKON"
                        },
                        {
                            "rut": "76570350-6",
                            "contrato_id": "28055",
                            "hh": "1745528",
                            "serial": "W862LB00297",
                            "marca": "IKON",
                            "nombre": "RICOH AFICIO  MP6002 - IKON"
                        },
                        {
                            "rut": "76570350-6",
                            "contrato_id": "28055",
                            "hh": "1745528",
                            "serial": "W862LB00299",
                            "marca": "IKON",
                            "nombre": "RICOH AFICIO  MP6002 - IKON"
                        },
                        {
                            "rut": "76570350-6",
                            "contrato_id": "28055",
                            "hh": "28677",
                            "serial": "W862LB00297",
                            "marca": "RICOH",
                            "nombre": "RICOH AFICIO  MP6002 - IKON"
                        },
                        {
                            "rut": "76570350-6",
                            "contrato_id": "28055",
                            "hh": "1745538",
                            "serial": "W862LB00299",
                            "marca": "RICOH",
                            "nombre": "RICOH AFICIO  MP6002 - IKON"
                        },
                        {
                            "rut": "76570350-6",
                            "contrato_id": "40386",
                            "hh": "29487",
                            "serial": "33050730",
                            "marca": "RISO",
                            "nombre": "PRINTER HC-5000 RISO (N)"
                        }
                    ]
                }
              }'; */

            // Error response : {"List":""}
            
            if($response === FALSE)
                throw new \Exception("Error obteniendo listado de HH para el empresa con rut {$org_rut}. {$this->CI->ConnectUrl->getResponseError()}", 2);
            
            $a_response = json_decode($response, TRUE);

            if(!empty($a_response["List"]))
            {
                // Código parche que soluciona el problema de cuando $a_response["List"]["data"] trae un solo elemento
               
                if(!array_key_exists(0, $a_response["List"]["data"]))
                {
                    $a_response["List"]["data"] = array($a_response["List"]["data"]);
                    
                }
                // Comentar código anterior cuando Rodrigo Torrens arregle el servicio.


                return $a_response["List"];
            }
            else
                throw new \Exception("La empresa con rut {$org_rut} no tiene HH disponibles. JSON: {$response}", 3);
        }
        catch(\Exception $e)
        {
            $this->errorMessage = $e->getMessage();
            $this->errorCode    = $e->getCode();
            return FALSE;
        }
    }
    public function getOrganizationStatus($contact_id)
    {
        //$contact = $this->CI->Contact->get($contact_id);
        $contact = RNCPHP\Contact::fetch($contact_id);
        
        $org_rut = $contact->Organization->CustomFields->c->rut;

        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

        //Obtengo el Token
        $jsonToken = $this->getToken();
        if($jsonToken === FALSE)
            return FALSE;

        // Se encapsula token como array
        $a_jsonToken = json_decode($jsonToken, TRUE);

        if (empty($a_jsonToken["access_token"]))
            throw new \Exception("Json de token inválido {$jsonToken}", 1);
    
        $token = $a_jsonToken["access_token"];
        //$url   = "https://api-test.dimacofi.cl/apiEBS/GetListRutHH";
        //$url = "https://api.dimacofi.cl/apiCloudMD/GetMultiRutStatus";
        //$url = "https://api.dimacofi.cl/apiCloudMD/getRutStatusSAI";
        $url=$cfg2->Value . '/apiCloudMD/getRutStatusSAI';
        
        // $org_rut   = "18870549-9";
        $org_rut = $contact->Organization->CustomFields->c->rut;

        $a_request = array(
            "RUT" => $org_rut
        );


        $json_request = json_encode($a_request);
        $response     = $this->CI->ConnectUrl->requestCURLJsonRaw($url, $json_request, $token); 
        $a_response=json_decode($response);
        
        return $a_response;
    }


    public function getOrganizationStatusHHS($hhs)
    {
   
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

        //Obtengo el Token
        $jsonToken = $this->getToken();
        if($jsonToken === FALSE)
            return FALSE;

        // Se encapsula token como array
        $a_jsonToken = json_decode($jsonToken, TRUE);

        if (empty($a_jsonToken["access_token"]))
            throw new \Exception("Json de token inválido {$jsonToken}", 1);
    
        $token = $a_jsonToken["access_token"];
        //$url   = "https://api-test.dimacofi.cl/apiEBS/GetListRutHH";
        //$url = "https://api.dimacofi.cl/apiCloudMD/GetMultiRutStatus";
        //$url = "https://api.dimacofi.cl/apiCloudMD/getRutStatusSAI";
        $url=$cfg2->Value . '/apiCloudMD/getMultiHHInfo';
     

        $a_request = $hhs;


        $json_request = json_encode($a_request);
        $response     = $this->CI->ConnectUrl->requestCURLJsonRaw($url, $json_request, $token); 
        $a_response=json_decode($response,true);
        return $a_response;
    }


    public function getOrganizationStatusbyRut($rut)
    {
       
        
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

        //Obtengo el Token
        $jsonToken = $this->getToken();
        if($jsonToken === FALSE)
            return FALSE;

        // Se encapsula token como array
        $a_jsonToken = json_decode($jsonToken, TRUE);

        if (empty($a_jsonToken["access_token"]))
            throw new \Exception("Json de token inválido {$jsonToken}", 1);
    
        $token = $a_jsonToken["access_token"];
        //$url   = "https://api-test.dimacofi.cl/apiEBS/GetListRutHH";
        //$url = "https://api.dimacofi.cl/apiCloudMD/GetMultiRutStatus";
        $url = $cfg2->Value."/apiCloudMD/getRutStatusSAI";

        $a_request = array(
            "RUT" => $rut
        );


        $json_request = json_encode($a_request);
        $response     = $this->CI->ConnectUrl->requestCURLJsonRaw($url, $json_request, $token); 
        $token = $a_jsonToken["access_token"];
        $a_response=json_decode($response);
        return $a_response;
    }

    public function getHoldingListHH($contact_id,$rut,$HH)
    {
        try
        {
            $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
            $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

            $CI = get_instance();
           
            $contact = $this->CI->Contact->get($contact_id);

            if($contact === FALSE)
                throw new \Exception($this->CI->Contact->getLastError(), 1);
            elseif (empty($contact->Organization)) 
                throw new \Exception("El contacto no tiene empresa asociada.", 1);

            // Se obtendrá un token
            $jsonToken = $this->getToken();
            if($jsonToken === FALSE)
                return FALSE;

            
            $a_jsonToken = json_decode($jsonToken, TRUE);

            if (empty($a_jsonToken["access_token"]))
                throw new \Exception("Json de token inválido {$jsonToken}", 1);

            $token = $a_jsonToken["access_token"];
            // $url   = "https://api.dimacofi.cl/apiEBS/GetListRutHH"; // Antiguo Servicio
            $url = $cfg2->Value . "/apiCloudMD/getHoldingListHH";
        

            $a_request = array(
                "RUT" => $rut,
                "HH" => $HH
                
            );


            $json_request = json_encode($a_request);
            $response     = $this->CI->ConnectUrl->requestCURLJsonRaw($url, $json_request, $token);
          
            // Error response : {"List":""}
            
            if($response === FALSE)
                throw new \Exception("Error obteniendo listado de HH para el empresa con rut {$rut}. {$this->CI->ConnectUrl->getResponseError()}", 2);
            
            $a_response = json_decode($response, TRUE);

            if(!empty($a_response["List"]))
            {
                // Código parche que soluciona el problema de cuando $a_response["List"]["data"] trae un solo elemento
                if(!array_key_exists(0, $a_response["List"]["data"]))
                {
                    $a_response["List"]["data"] = array($a_response["List"]["data"]);
                }
                // Comentar código anterior cuando Rodrigo Torrens arregle el servicio.


                return $a_response["List"];
            }
            else
                throw new \Exception("HH no existe en su organización . JSON: {$response}", 3);
        }
        catch(\Exception $e)
        {
            $this->errorMessage = $e->getMessage();
            $this->errorCode    = $e->getCode();
            return FALSE;
        }
    }

    public function CreateSpecialService($HH,$id_tecnico)
    {
        try
        {
            
               
            // $url   = "https://api.dimacofi.cl/apiEBS/GetListRutHH"; // Antiguo Servicio
            $url = "https://soportedimacoficl.custhelp.com/cc/Tickets/CreateSpecialTicket";

            $json_request = '{"datos":{"hh":"' . $HH . '","id_tecnico":"' . $id_tecnico . '"}}';
         
            $response     = $this->CI->ConnectUrl->requestCURLJsonRaw($url, $json_request);
          
            
            if($response === FALSE)
                throw new \Exception("Error Generando Ticket HH "  , 2);
            
            $a_response = json_decode($response, TRUE);
            return $a_response;
          
        }
        catch(\Exception $e)
        {
            $this->errorMessage = $e->getMessage();
            $this->errorCode    = $e->getCode();
            return FALSE;
        }
    }

    public function GetContracts($rut)
    {
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

        //Obtengo el Token
        $jsonToken = $this->getToken();
        if($jsonToken === FALSE)
            return FALSE;

        // Se encapsula token como array
        $a_jsonToken = json_decode($jsonToken, TRUE);

        if (empty($a_jsonToken["access_token"]))
            throw new \Exception("Json de token inválido {$jsonToken}", 1);
    
        $token = $a_jsonToken["access_token"];
        //$url   = "https://api-test.dimacofi.cl/apiEBS/GetListRutHH";
        //$url = "https://api.dimacofi.cl/apiCloudMD/GetMultiRutStatus";
        //$url = "https://api.dimacofi.cl/apiCloudMD/getRutStatusSAI";
        $url=$cfg2->Value . '/CustomerDataInfo/CustomerContracts';
     

        $a_request = '{"rut":"' . $rut . '"}';


        $json_request = json_encode($a_request);
        $response     = $this->CI->ConnectUrl->requestCURLJsonRaw($url, $a_request, $token); 
        $a_response=json_decode($response,true);
        return $a_response;
    }


    public function SearchTrx($trx_rut,$trx_from,$trx_to,$trx_hh,$serie)
    {
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

        
        $jsonToken = $this->getToken();
        if($jsonToken === FALSE)
            return FALSE;

        // Se encapsula token como array
        $a_jsonToken = json_decode($jsonToken, TRUE);

        if (empty($a_jsonToken["access_token"]))
            throw new \Exception("Json de token inválido {$jsonToken}", 1);
    
        $token = $a_jsonToken["access_token"];
        try{
            $url = $cfg2->Value."/CustomerDataInfo/BuscaInfoContaNbp";
     
           /* $a_request = array(
                "rut" => $rut,
                "invoice_number"=>  $invoice_number ,
                "invoice_rut"=> $invoice_rut,
                "invoice_contrato"=> $invoice_contrato,
                "invoice_from"=> $invoice_from ,
                "invoice_to"=> $invoice_to
            );
*/
            $a_request = new \stdClass();
            $a_request->rut = $trx_rut;
            $a_request->fecha_ini = $trx_from;
            $a_request->fecha_fin = $trx_to;
            $a_request->hh = $trx_hh;
            $a_request->serie =  $serie;

    
            $jsonDataEncoded = json_encode($a_request);
         
            $CI =& get_instance();
            $CI->load->model('custom/ConnectUrl');

            //$jsonDataEncoded = json_encode($a_request);

            //echo '-----> ' . $jsonDataEncoded;
            $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded, $token);
            
            /*$test='{
                "result": true,
                "response": {
                "message": "Factura obtenida exitosamente.",
                "lines":[{"rut":"60506000-5","amount":"38220584","contrat":"56016","ammount_remaining":"37697360","customer_trx_id":"11679209","trx_number":"528481","due_date":"30\/09\/2020","customer_id":"4291","trx_date":"31\/08\/2020"},{"rut":"60506000-5","amount":"44745423","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11578252","trx_number":"525604","due_date":"30\/08\/2020","customer_id":"4291","trx_date":"31\/07\/2020"},{"rut":"60506000-5","amount":"38465693","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11511211","trx_number":"517957","due_date":"30\/07\/2020","customer_id":"4291","trx_date":"30\/06\/2020"},{"rut":"60506000-5","amount":"30680894","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11465205","trx_number":"513178","due_date":"28\/06\/2020","customer_id":"4291","trx_date":"29\/05\/2020"},{"rut":"60506000-5","amount":"39497925","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11425909","trx_number":"509813","due_date":"30\/05\/2020","customer_id":"4291","trx_date":"30\/04\/2020"},{"rut":"60506000-5","amount":"29643559","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11353262","trx_number":"504602","due_date":"30\/04\/2020","customer_id":"4291","trx_date":"31\/03\/2020"},{"rut":"60506000-5","amount":"50216169","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11314237","trx_number":"501282","due_date":"29\/03\/2020","customer_id":"4291","trx_date":"28\/02\/2020"},{"rut":"60506000-5","amount":"43865026","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11220203","trx_number":"493966","due_date":"01\/03\/2020","customer_id":"4291","trx_date":"31\/01\/2020"},{"rut":"60506000-5","amount":"37896093","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10908215","trx_number":"488171","due_date":"30\/01\/2020","customer_id":"4291","trx_date":"31\/12\/2019"},{"rut":"60506000-5","amount":"45376497","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10873813","trx_number":"483481","due_date":"29\/12\/2019","customer_id":"4291","trx_date":"29\/11\/2019"},{"rut":"60506000-5","amount":"42681170","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10829192","trx_number":"478126","due_date":"30\/11\/2019","customer_id":"4291","trx_date":"31\/10\/2019"},{"rut":"60506000-5","amount":"39425779","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10786172","trx_number":"460936","due_date":"30\/10\/2019","customer_id":"4291","trx_date":"30\/09\/2019"},{"rut":"60506000-5","amount":"38263398","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10749068","trx_number":"455403","due_date":"29\/09\/2019","customer_id":"4291","trx_date":"30\/08\/2019"},{"rut":"60506000-5","amount":"42895997","contrat":"56016","ammount_remaining":"0","customer_trx_id":"9502691","trx_number":"445709","due_date":"30\/08\/2019","customer_id":"4291","trx_date":"31\/07\/2019"},{"rut":"60506000-5","amount":"72493880","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7875203","trx_number":"440406","due_date":"28\/07\/2019","customer_id":"4291","trx_date":"28\/06\/2019"},{"rut":"60506000-5","amount":"38114390","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7635810","trx_number":"434522","due_date":"30\/06\/2019","customer_id":"4291","trx_date":"31\/05\/2019"},{"rut":"60506000-5","amount":"44835205","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7615432","trx_number":"424410","due_date":"30\/05\/2019","customer_id":"4291","trx_date":"30\/04\/2019"},{"rut":"60506000-5","amount":"32836709","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7585448","trx_number":"418072","due_date":"28\/04\/2019","customer_id":"4291","trx_date":"29\/03\/2019"},{"rut":"60506000-5","amount":"35622350","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7539216","trx_number":"367287","due_date":"30\/03\/2019","customer_id":"4291","trx_date":"28\/02\/2019"},{"rut":"60506000-5","amount":"32740368","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7504762","trx_number":"362430","due_date":"02\/03\/2019","customer_id":"4291","trx_date":"31\/01\/2019"},{"rut":"60506000-5","amount":"55242236","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7465205","trx_number":"356896","due_date":"30\/01\/2019","customer_id":"4291","trx_date":"31\/12\/2018"}]
                }
                }';
    
            return json_decode($servicet);
            */

            if(is_bool($service))
            {
                $this->error['message'] = $CI->ConnectUrl->getResponseError();
                return false;
            }
            else
            {
                return json_decode($service);
            }

        }
        catch (RNCPHP\ConnectAPIError $err )
        {
            $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }

    }


    public function SearchTrxHist($trx_rut,$trx_from,$trx_to,$trx_hh,$serie)
    {
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

        
        $jsonToken = $this->getToken();
        if($jsonToken === FALSE)
            return FALSE;

        // Se encapsula token como array
        $a_jsonToken = json_decode($jsonToken, TRUE);

        if (empty($a_jsonToken["access_token"]))
            throw new \Exception("Json de token inválido {$jsonToken}", 1);
    
        $token = $a_jsonToken["access_token"];
        try{
            $url = $cfg2->Value."/CustomerDataInfo/BuscaInfoContaNbpHist";
     
           /* $a_request = array(
                "rut" => $rut,
                "invoice_number"=>  $invoice_number ,
                "invoice_rut"=> $invoice_rut,
                "invoice_contrato"=> $invoice_contrato,
                "invoice_from"=> $invoice_from ,
                "invoice_to"=> $invoice_to
            );
*/
            $a_request = new \stdClass();
            $a_request->rut = $trx_rut;
            $a_request->fecha_ini = $trx_from;
            $a_request->fecha_fin = $trx_to;
            $a_request->hh = $trx_hh;
            $a_request->serie =  $serie;

    
            $jsonDataEncoded = json_encode($a_request);
         
            $CI =& get_instance();
            $CI->load->model('custom/ConnectUrl');

            //$jsonDataEncoded = json_encode($a_request);

            //echo '-----> ' . $jsonDataEncoded;
            //echo '-----> ' . $url;
            $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded, $token);
            
            /*$test='{
                "result": true,
                "response": {
                "message": "Factura obtenida exitosamente.",
                "lines":[{"rut":"60506000-5","amount":"38220584","contrat":"56016","ammount_remaining":"37697360","customer_trx_id":"11679209","trx_number":"528481","due_date":"30\/09\/2020","customer_id":"4291","trx_date":"31\/08\/2020"},{"rut":"60506000-5","amount":"44745423","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11578252","trx_number":"525604","due_date":"30\/08\/2020","customer_id":"4291","trx_date":"31\/07\/2020"},{"rut":"60506000-5","amount":"38465693","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11511211","trx_number":"517957","due_date":"30\/07\/2020","customer_id":"4291","trx_date":"30\/06\/2020"},{"rut":"60506000-5","amount":"30680894","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11465205","trx_number":"513178","due_date":"28\/06\/2020","customer_id":"4291","trx_date":"29\/05\/2020"},{"rut":"60506000-5","amount":"39497925","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11425909","trx_number":"509813","due_date":"30\/05\/2020","customer_id":"4291","trx_date":"30\/04\/2020"},{"rut":"60506000-5","amount":"29643559","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11353262","trx_number":"504602","due_date":"30\/04\/2020","customer_id":"4291","trx_date":"31\/03\/2020"},{"rut":"60506000-5","amount":"50216169","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11314237","trx_number":"501282","due_date":"29\/03\/2020","customer_id":"4291","trx_date":"28\/02\/2020"},{"rut":"60506000-5","amount":"43865026","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11220203","trx_number":"493966","due_date":"01\/03\/2020","customer_id":"4291","trx_date":"31\/01\/2020"},{"rut":"60506000-5","amount":"37896093","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10908215","trx_number":"488171","due_date":"30\/01\/2020","customer_id":"4291","trx_date":"31\/12\/2019"},{"rut":"60506000-5","amount":"45376497","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10873813","trx_number":"483481","due_date":"29\/12\/2019","customer_id":"4291","trx_date":"29\/11\/2019"},{"rut":"60506000-5","amount":"42681170","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10829192","trx_number":"478126","due_date":"30\/11\/2019","customer_id":"4291","trx_date":"31\/10\/2019"},{"rut":"60506000-5","amount":"39425779","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10786172","trx_number":"460936","due_date":"30\/10\/2019","customer_id":"4291","trx_date":"30\/09\/2019"},{"rut":"60506000-5","amount":"38263398","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10749068","trx_number":"455403","due_date":"29\/09\/2019","customer_id":"4291","trx_date":"30\/08\/2019"},{"rut":"60506000-5","amount":"42895997","contrat":"56016","ammount_remaining":"0","customer_trx_id":"9502691","trx_number":"445709","due_date":"30\/08\/2019","customer_id":"4291","trx_date":"31\/07\/2019"},{"rut":"60506000-5","amount":"72493880","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7875203","trx_number":"440406","due_date":"28\/07\/2019","customer_id":"4291","trx_date":"28\/06\/2019"},{"rut":"60506000-5","amount":"38114390","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7635810","trx_number":"434522","due_date":"30\/06\/2019","customer_id":"4291","trx_date":"31\/05\/2019"},{"rut":"60506000-5","amount":"44835205","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7615432","trx_number":"424410","due_date":"30\/05\/2019","customer_id":"4291","trx_date":"30\/04\/2019"},{"rut":"60506000-5","amount":"32836709","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7585448","trx_number":"418072","due_date":"28\/04\/2019","customer_id":"4291","trx_date":"29\/03\/2019"},{"rut":"60506000-5","amount":"35622350","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7539216","trx_number":"367287","due_date":"30\/03\/2019","customer_id":"4291","trx_date":"28\/02\/2019"},{"rut":"60506000-5","amount":"32740368","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7504762","trx_number":"362430","due_date":"02\/03\/2019","customer_id":"4291","trx_date":"31\/01\/2019"},{"rut":"60506000-5","amount":"55242236","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7465205","trx_number":"356896","due_date":"30\/01\/2019","customer_id":"4291","trx_date":"31\/12\/2018"}]
                }
                }';
    
            return json_decode($servicet);
            */

            if(is_bool($service))
            {
                $this->error['message'] = $CI->ConnectUrl->getResponseError();
                return false;
            }
            else
            {
                return json_decode($service);
            }

        }
        catch (RNCPHP\ConnectAPIError $err )
        {
            $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }

    }

    public function BuscaInfoNivelesNbp($trx_rut,$trx_hh,$serie)
    {
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

        
        $jsonToken = $this->getToken();
        if($jsonToken === FALSE)
            return FALSE;

        // Se encapsula token como array
        $a_jsonToken = json_decode($jsonToken, TRUE);

        if (empty($a_jsonToken["access_token"]))
            throw new \Exception("Json de token inválido {$jsonToken}", 1);
    
        $token = $a_jsonToken["access_token"];
        try{
            $url = $cfg2->Value."/CustomerDataInfo/BuscaInfoNivelesNbp";
     
           /* $a_request = array(
                "rut" => $rut,
                "invoice_number"=>  $invoice_number ,
                "invoice_rut"=> $invoice_rut,
                "invoice_contrato"=> $invoice_contrato,
                "invoice_from"=> $invoice_from ,
                "invoice_to"=> $invoice_to
            );
*/
            $a_request = new \stdClass();
            $a_request->rut = $trx_rut;
            $a_request->hh = $trx_hh;
            $a_request->serie =  $serie;

    
            $jsonDataEncoded = json_encode($a_request);
         
            $CI =& get_instance();
            $CI->load->model('custom/ConnectUrl');

            //$jsonDataEncoded = json_encode($a_request);

            //echo '-----> ' . $jsonDataEncoded;
            $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded, $token);
            
            /*$test='{
                "result": true,
                "response": {
                "message": "Factura obtenida exitosamente.",
                "lines":[{"rut":"60506000-5","amount":"38220584","contrat":"56016","ammount_remaining":"37697360","customer_trx_id":"11679209","trx_number":"528481","due_date":"30\/09\/2020","customer_id":"4291","trx_date":"31\/08\/2020"},{"rut":"60506000-5","amount":"44745423","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11578252","trx_number":"525604","due_date":"30\/08\/2020","customer_id":"4291","trx_date":"31\/07\/2020"},{"rut":"60506000-5","amount":"38465693","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11511211","trx_number":"517957","due_date":"30\/07\/2020","customer_id":"4291","trx_date":"30\/06\/2020"},{"rut":"60506000-5","amount":"30680894","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11465205","trx_number":"513178","due_date":"28\/06\/2020","customer_id":"4291","trx_date":"29\/05\/2020"},{"rut":"60506000-5","amount":"39497925","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11425909","trx_number":"509813","due_date":"30\/05\/2020","customer_id":"4291","trx_date":"30\/04\/2020"},{"rut":"60506000-5","amount":"29643559","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11353262","trx_number":"504602","due_date":"30\/04\/2020","customer_id":"4291","trx_date":"31\/03\/2020"},{"rut":"60506000-5","amount":"50216169","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11314237","trx_number":"501282","due_date":"29\/03\/2020","customer_id":"4291","trx_date":"28\/02\/2020"},{"rut":"60506000-5","amount":"43865026","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11220203","trx_number":"493966","due_date":"01\/03\/2020","customer_id":"4291","trx_date":"31\/01\/2020"},{"rut":"60506000-5","amount":"37896093","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10908215","trx_number":"488171","due_date":"30\/01\/2020","customer_id":"4291","trx_date":"31\/12\/2019"},{"rut":"60506000-5","amount":"45376497","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10873813","trx_number":"483481","due_date":"29\/12\/2019","customer_id":"4291","trx_date":"29\/11\/2019"},{"rut":"60506000-5","amount":"42681170","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10829192","trx_number":"478126","due_date":"30\/11\/2019","customer_id":"4291","trx_date":"31\/10\/2019"},{"rut":"60506000-5","amount":"39425779","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10786172","trx_number":"460936","due_date":"30\/10\/2019","customer_id":"4291","trx_date":"30\/09\/2019"},{"rut":"60506000-5","amount":"38263398","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10749068","trx_number":"455403","due_date":"29\/09\/2019","customer_id":"4291","trx_date":"30\/08\/2019"},{"rut":"60506000-5","amount":"42895997","contrat":"56016","ammount_remaining":"0","customer_trx_id":"9502691","trx_number":"445709","due_date":"30\/08\/2019","customer_id":"4291","trx_date":"31\/07\/2019"},{"rut":"60506000-5","amount":"72493880","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7875203","trx_number":"440406","due_date":"28\/07\/2019","customer_id":"4291","trx_date":"28\/06\/2019"},{"rut":"60506000-5","amount":"38114390","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7635810","trx_number":"434522","due_date":"30\/06\/2019","customer_id":"4291","trx_date":"31\/05\/2019"},{"rut":"60506000-5","amount":"44835205","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7615432","trx_number":"424410","due_date":"30\/05\/2019","customer_id":"4291","trx_date":"30\/04\/2019"},{"rut":"60506000-5","amount":"32836709","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7585448","trx_number":"418072","due_date":"28\/04\/2019","customer_id":"4291","trx_date":"29\/03\/2019"},{"rut":"60506000-5","amount":"35622350","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7539216","trx_number":"367287","due_date":"30\/03\/2019","customer_id":"4291","trx_date":"28\/02\/2019"},{"rut":"60506000-5","amount":"32740368","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7504762","trx_number":"362430","due_date":"02\/03\/2019","customer_id":"4291","trx_date":"31\/01\/2019"},{"rut":"60506000-5","amount":"55242236","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7465205","trx_number":"356896","due_date":"30\/01\/2019","customer_id":"4291","trx_date":"31\/12\/2018"}]
                }
                }';
    
            return json_decode($servicet);
            */
            //echo $service ;

            if(is_bool($service))
            {
                $this->error['message'] = $CI->ConnectUrl->getResponseError();
                return false;
            }
            else
            {
                return json_decode($service);
            }

        }
        catch (RNCPHP\ConnectAPIError $err )
        {
            $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }

    }

    public function BuscaInfoHHNbp($trx_rut,$trx_hh,$serie,$status)
    {
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

        
        $jsonToken = $this->getToken();
        if($jsonToken === FALSE)
            return FALSE;

        // Se encapsula token como array
        $a_jsonToken = json_decode($jsonToken, TRUE);

        if (empty($a_jsonToken["access_token"]))
            throw new \Exception("Json de token inválido {$jsonToken}", 1);
    
        $token = $a_jsonToken["access_token"];
        try{
            $url = $cfg2->Value."/CustomerDataInfo/BuscaInfoHHNbpV2";
     
           /* $a_request = array(
                "rut" => $rut,
                "invoice_number"=>  $invoice_number ,
                "invoice_rut"=> $invoice_rut,
                "invoice_contrato"=> $invoice_contrato,
                "invoice_from"=> $invoice_from ,
                "invoice_to"=> $invoice_to
            );
*/
            $a_request = new \stdClass();
            $a_request->rut = $trx_rut;
            $a_request->hh = $trx_hh;
            $a_request->serie =  $serie;
            $a_request->status =  $status;
    
            $jsonDataEncoded = json_encode($a_request);
         
            $CI =& get_instance();
            $CI->load->model('custom/ConnectUrl');

            //$jsonDataEncoded = json_encode($a_request);

            //echo json_encode($a_request);
            $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded, $token);
           
            /*$test='{
                "result": true,
                "response": {
                "message": "Factura obtenida exitosamente.",
                "lines":[{"rut":"60506000-5","amount":"38220584","contrat":"56016","ammount_remaining":"37697360","customer_trx_id":"11679209","trx_number":"528481","due_date":"30\/09\/2020","customer_id":"4291","trx_date":"31\/08\/2020"},{"rut":"60506000-5","amount":"44745423","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11578252","trx_number":"525604","due_date":"30\/08\/2020","customer_id":"4291","trx_date":"31\/07\/2020"},{"rut":"60506000-5","amount":"38465693","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11511211","trx_number":"517957","due_date":"30\/07\/2020","customer_id":"4291","trx_date":"30\/06\/2020"},{"rut":"60506000-5","amount":"30680894","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11465205","trx_number":"513178","due_date":"28\/06\/2020","customer_id":"4291","trx_date":"29\/05\/2020"},{"rut":"60506000-5","amount":"39497925","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11425909","trx_number":"509813","due_date":"30\/05\/2020","customer_id":"4291","trx_date":"30\/04\/2020"},{"rut":"60506000-5","amount":"29643559","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11353262","trx_number":"504602","due_date":"30\/04\/2020","customer_id":"4291","trx_date":"31\/03\/2020"},{"rut":"60506000-5","amount":"50216169","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11314237","trx_number":"501282","due_date":"29\/03\/2020","customer_id":"4291","trx_date":"28\/02\/2020"},{"rut":"60506000-5","amount":"43865026","contrat":"56016","ammount_remaining":"0","customer_trx_id":"11220203","trx_number":"493966","due_date":"01\/03\/2020","customer_id":"4291","trx_date":"31\/01\/2020"},{"rut":"60506000-5","amount":"37896093","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10908215","trx_number":"488171","due_date":"30\/01\/2020","customer_id":"4291","trx_date":"31\/12\/2019"},{"rut":"60506000-5","amount":"45376497","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10873813","trx_number":"483481","due_date":"29\/12\/2019","customer_id":"4291","trx_date":"29\/11\/2019"},{"rut":"60506000-5","amount":"42681170","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10829192","trx_number":"478126","due_date":"30\/11\/2019","customer_id":"4291","trx_date":"31\/10\/2019"},{"rut":"60506000-5","amount":"39425779","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10786172","trx_number":"460936","due_date":"30\/10\/2019","customer_id":"4291","trx_date":"30\/09\/2019"},{"rut":"60506000-5","amount":"38263398","contrat":"56016","ammount_remaining":"0","customer_trx_id":"10749068","trx_number":"455403","due_date":"29\/09\/2019","customer_id":"4291","trx_date":"30\/08\/2019"},{"rut":"60506000-5","amount":"42895997","contrat":"56016","ammount_remaining":"0","customer_trx_id":"9502691","trx_number":"445709","due_date":"30\/08\/2019","customer_id":"4291","trx_date":"31\/07\/2019"},{"rut":"60506000-5","amount":"72493880","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7875203","trx_number":"440406","due_date":"28\/07\/2019","customer_id":"4291","trx_date":"28\/06\/2019"},{"rut":"60506000-5","amount":"38114390","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7635810","trx_number":"434522","due_date":"30\/06\/2019","customer_id":"4291","trx_date":"31\/05\/2019"},{"rut":"60506000-5","amount":"44835205","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7615432","trx_number":"424410","due_date":"30\/05\/2019","customer_id":"4291","trx_date":"30\/04\/2019"},{"rut":"60506000-5","amount":"32836709","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7585448","trx_number":"418072","due_date":"28\/04\/2019","customer_id":"4291","trx_date":"29\/03\/2019"},{"rut":"60506000-5","amount":"35622350","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7539216","trx_number":"367287","due_date":"30\/03\/2019","customer_id":"4291","trx_date":"28\/02\/2019"},{"rut":"60506000-5","amount":"32740368","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7504762","trx_number":"362430","due_date":"02\/03\/2019","customer_id":"4291","trx_date":"31\/01\/2019"},{"rut":"60506000-5","amount":"55242236","contrat":"56016","ammount_remaining":"0","customer_trx_id":"7465205","trx_number":"356896","due_date":"30\/01\/2019","customer_id":"4291","trx_date":"31\/12\/2018"}]
                }
                }';
    
            return json_decode($servicet);
            */
            //echo $service ;

            if(is_bool($service))
            {
                $this->error['message'] = $CI->ConnectUrl->getResponseError();
                return false;
            }
            else
            {
                return json_decode($service);
            }

        }
        catch (RNCPHP\ConnectAPIError $err )
        {
            $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }

    }
    public function BuscaReporte($a_request)
    {
        $filters               = new RNCPHP\AnalyticsReportSearchFilterArray;
        //invoca un reporte
      /*
        $data=json_decode($filtro);
        //return $data->rut;
        $report_id             = $id_reporte;
        $filter_value          = 'filter';
        //logMessage("  filter_value " .     $filter_value);
        $status_filter         = new RNCPHP\AnalyticsReportSearchFilter;
        $status_filter->Name   = 'cuentaID';
        $status_filter->Values = array($data->rut);
        $filters               = new RNCPHP\AnalyticsReportSearchFilterArray;
        $filters[]             = $status_filter;
        $ar                    = RNCPHP\AnalyticsReport::fetch($report_id);
        $arr                   = $ar->run( 0, $filters );
  

        $filters               = new RNCPHP\AnalyticsReportSearchFilterArray;
        */
        $report_id             = $a_request->id_reporte;
       
        
        $CI             = get_instance();
        $accountValues  = $CI->session->getProfile('info_contact');
        $ContactData = $this->getOrganizationStatus($accountValues->contactID);
       
       
        //echo count($ContactData->Ruts->List->data) . '-' . $accountValues->contactID;
        if(is_array($ContactData->Ruts->List->data))
        {
          $ruts="";
          for($i=0;$i<count($ContactData->Ruts->List->data); $i++)
          {
            $ruts= $ruts . "'" . $ContactData->Ruts->List->data[$i]->rut_cliente ."'," ;
            //echo $ruts .'<br>';
            
          }
          $ruts=$ruts . "''";
        }
        else
        {
          $ruts= "'" .$ContactData->Ruts->List->data->rut_cliente ."'";
        }
       
        $filter_value          = $ruts;
        //logMessage("  filter_value " .     $filter_value);
        $status_filter         = new RNCPHP\AnalyticsReportSearchFilter;
        $status_filter->Name   = 'cuentaID';
        $status_filter->Values = array($filter_value);
        $filters[]             = $status_filter;


      /*
        //logMessage("  filter_value " .     $filter_value);
        $status_filter         = new RNCPHP\AnalyticsReportSearchFilter;
        $status_filter->Name   = 'disp';
        $status_filter->Values = array(24);
        $filters[]             = $status_filter;*/




        $filter_value          = "Ingresado";
        //logMessage("  filter_value " .     $filter_value);
        $status_filter         = new RNCPHP\AnalyticsReportSearchFilter;
        $status_filter->Name   = 'estado';

        /*
        2 - Cerrado
        1 - Ingresado
        8 - Actualizado
        3 - En espera
        102 - Solicitud de Evaluación de Crédito
        103 - Aprobado por Crédito
        104 - Rechazado por Crédito
        105 - Solicitud de Cotización
        106 - Confección de Cotización
        107 - Cotización Enviada
        108 - Cotización Aceptada
        110 - Cotización Cerrada Sin Respuesta
        146 - Cotización Rechazada
        195 - Problemas de Entrega
        111 - Por Despachar
        140 - Despachado
        120 - Despachado Express
        112 - Despacho Entregado
        142 - Despacho no Entregado
        147 - Despacho cerraro
        113 - Por Retirar
        109 - Retiro en ruta
        114 - Contador Inválido
        115 - Cliente Bloqueado
        116 - Cliente No Bloqueado
        117 - Solicitud de Visita Técnico
        118 - Visita Técnico
        119 - Derivado a asistencia remota
        143 - Trabajando
        121 - Pendiente de Aviso a Cliente
        122 - Solicitud de Repuestos
        123 - Confección de Presupuesto
        124 - Presupuesto Aprobado
        125 - Presupuesto Rechazado
        126 - Solicitud de Soporte 2° Nivel
        127 - Finalizado por 2° Nivel
        128 - No Resuelve por 2° Nivel
        129 - Información Validada
        130 - Reparación Técnico Finaliza
        131 - Solicitud de Repuestos
        132 - Selección de Equipo Usado
        133 - Derivado a Taller
        134 - Solicitud información HH
        135 - HH Asignada
        136 - Solicitud de Preparación
        137 - HH en Preparación
        138 - HH Preparado
        139 - HH Control de calidad
        141 - HH Rechazada por Control de calidad
        144 - Recibido Sin Confirmación
        145 - Recibido Con Confirmación
        148 - Cerrado Por Usuario
        149 - Cancelado
        150 - Ejecucion de Presupuesto
        151 - Por Despachar Canibal
        152 - Despachado Canibal
        153 - Despachado Entregado Canibal
        155 - En Busqueda Repuesto Canibal
        156 - Despacho Canibal a Logistica
        157 - Repuesto No Abastecido
        158 - Por Buscar Canibal
        159 - Cotizacion Rechazada Equipo Convenio
        160 - Reinstalacion
        161 - Visita Aceptada
        162 - Visita Técnico Asignado
        163 - Visita Técnico En ruta
        164 - Visita a Re-agendar
        165 - Visita Técnico Trabajando
        166 - Visita Finalizada
        167 - PRE SOLICITUD DE REPUESTOS
        168 - Visita con Solicitud de 2° Nivel
        169 - Visita Técnico En Desarme
        170 - Visita Técnico Maquina Desarmada
        171 - Visita CARGO Presupuesto
        172 - Visita con Solicitud de REPUESTO
        173 - Visita Solicitud Repuesto Canibal
        174 - Repuesto en Aprobacion
        175 - Supervisión
        176 - Enviada a OM
        177 - Aprobación Despacho
        178 - Enviado
        181 - Proceso Cotización
        185 - Evaluación Convenio
        186 - Esperando Informe
        187 - Evaluación Comercial
        188 - Espera Evaluación Comercial
        189 - Pendiente Informe Técnico
        190 - Evaluación Comercial Rechazada
        191 - Evaluación Comercial Aceptada
        193 - Informe Técnico OK
        196 - Retenido por Supervisión
        197 - Esperando Pago de Visita
        */
        $status_filter->Values = $a_request->status_values;
   
        
        $filters[]             = $status_filter;
       
        $ar                    = RNCPHP\AnalyticsReport::fetch($report_id);
        $arr                   = $ar->run( 0, $filters );
        
        for ( $i = $arr->count(); $i--; )
        {
          $row = $arr->next();
         
          if(in_array($row['disp_id'], $a_request->tipo_soporte,true))
         {
            $array_response['Tickets'][] = $row;
            
          }
        }
        $response = json_encode($array_response);


  
  
        return json_decode($response);
    }

    public function getAgreementPriceitems($suppliers)
    {

        $CI =& get_instance();
        $CI->load->model('custom/ConnectUrl');

        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL

         // Se obtendrá un token
         $jsonToken = $this->getTokenTest();
         if($jsonToken === FALSE)
             return FALSE;

         
         $a_jsonToken = json_decode($jsonToken, TRUE);

         if (empty($a_jsonToken["access_token"]))
             throw new \Exception("Json de token inválido {$jsonToken}", 1);

         $token = $a_jsonToken["access_token"];
        $url = $cfg2->Value.":8290/apiCloudMD/getAgreementPriceitems";
        
        $response=$CI->ConnectUrl->requestCURLJsonRaw($url, $suppliers, $token); 
        $a_response     = json_decode($response);

        // Se obtendrá un token
        $jsonToken = $this->getToken();
        if($jsonToken === FALSE)
            return FALSE;


        $a_jsonToken = json_decode($jsonToken, TRUE);

        if (empty($a_jsonToken["access_token"]))
            throw new \Exception("Json de token inválido {$jsonToken}", 1);

        $token = $a_jsonToken["access_token"];
        $jsonDataEncoded='{}';
        $response=$CI->ConnectUrl->requestCURLJsonRaw($cfg2->Value ."/apiCloudMD/getUSDValue", $jsonDataEncoded, $token);
        $dolar=json_decode($response);

        //$a_response=$dolar->dolar->values->CONVERSION_RATE ;
        //$a_response->dolar=$dolar->dolar->values->CONVERSION_RATE;
        $a_response->dolar=$dolar->dolar->values->CONVERSION_RATE;;
        return $a_response;

        return $a_response;
    }
}
