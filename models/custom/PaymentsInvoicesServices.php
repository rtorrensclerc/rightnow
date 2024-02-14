<?php
namespace Custom\Models;
use RightNow\Connect\v1_3 as RNCPHP;


class PaymentsInvoicesServices extends \RightNow\Models\Base
{
    public  $error          = array ('numberID' => null , 'message' => null);
    function __construct()
    {
        parent::__construct();
        $this->CI->load->model("custom/ConnectUrl");
        $this->CI->load->model("custom/Contact");
    }


    /**
    * Obtiene el detalle agrupado de las facturas.
    *
    * @param string     $rut  Que representa el rut de la organización a la cual pertenece el contacto.
    * @param integer    $contract_number Representa el número de contrato.
    *
    * @return JSON
    */
    public function getDetailInvoices($rut, $contract_number)
    {
        try
        {
            //$url = "http://api-test.dimacofi.cl/sucursalVirtual/consulta/consumos/resumen"; //Test
            // $url = "https://api.dimacofi.cl/sucursalVirtual/consulta/consumos/resumen"; //producción
            $url = "https://api.dimacofi.cl/CustomerDataInfo/DetallesAgrupadoFacturas"; // Mejora
            $this->CI->load->model('custom/ConnectUrl');

            /* $jsonData = new \stdClass();
            $jsonData->rut = $rut;
            $jsonData->contract_number = $contract_number; 
            $jsonDataEncoded = json_encode($jsonData); */

            $a_request = array(
                "rut"           => $rut,
                "q_meses"       => "6",
                "ct_reference"  => $contract_number
            );

            $jsonDataEncoded = json_encode($a_request);

            $service = $this->CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded);

            if(is_bool($service))
            {
                $this->error["message"] = $this->CI->ConnectUrl->getResponseError();
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

    /**
    * Servicio que obtiene la información del consumo de los últimos 6 meses
    * y el detalle del consumo de cada uno de ellos.
    *
    * @param string     $rut  Que representa el rut de la organización a la cual pertenece el contacto.
    * @param integer    $contract_number Representa el número de contrato.
    *
    * @return JSON
    */
    public function getLastConsumptionsMonths($rut, $contract_number)
    {
        try
        {
            //$url = "http://api-test.dimacofi.cl/sucursalVirtual/consulta/consumos/resumen"; //Test
            // $url = "https://api.dimacofi.cl/sucursalVirtual/consulta/consumos/resumen"; //producción
            $url = "https://api.dimacofi.cl/CustomerDataInfo/CustomerSummaryBills"; // Mejora
            // $CI =& get_instance();
            $this->CI->load->model('custom/ConnectUrl');

            /* $jsonData = new \stdClass();
            $jsonData->rut = $rut;
            $jsonData->contract_number = $contract_number; 
            $jsonDataEncoded = json_encode($jsonData); */

            $a_request = array(
                "rut"           => $rut,
                "q_meses"       => "5",
                "ct_reference"  => $contract_number
            );

            $jsonDataEncoded = json_encode($a_request);

            $service = $this->CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded);

            if(is_bool($service))
            {
                $this->error["message"] = $this->CI->ConnectUrl->getResponseError();
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


    /**
    * Servicio que obtiene la información de una factura
    * y el detalle de sus consumos asociados.
    *
    * @param string     $rut  Que representa el rut de la organización a la cual pertenece el contacto.
    * @param integer    $contract_number Representa el número de contrato.
    * @param integer    $invoice_number Que representa el número de factura.
    *
    * @return JSON
    */
    public function getLastConsumptionsLines($rut, $contract_number, $invoice_number)
    {
        try
        {
            //$url = "http://api-test.dimacofi.cl/sucursalVirtual/consulta/factura/detalle"; //test
            $url = "https://api.dimacofi.cl/sucursalVirtual/consulta/factura/detalle";
            $CI =& get_instance();
            $CI->load->model('custom/ConnectUrl');

            $jsonData = new \stdClass();
            $jsonData->rut = $rut;
            $jsonData->contract_number = $contract_number;
            $jsonData->invoice_number = $invoice_number;
            $jsonDataEncoded = json_encode($jsonData);

            $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded);

            if(is_bool($service))
            {
                $this->error["message"] = $CI->ConnectUrl->getResponseError();
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




    /**
    * Servicio que obtiene los pagos y facturas de los últimos 6 meses.
    *
    * @param string     $rut  Que representa el rut de la organización a la cual pertenece el contacto.
    * @param integer    $contract_number Representa el número de contrato.
    *
    * @return JSON
    */
    public function getLastSixInvoices($rut, $contract_number)
    {
        try
        {
            //$url = "http://api-test.dimacofi.cl/sucursalVirtual/consulta/documentos"; //test
            $url = "https://api.dimacofi.cl/sucursalVirtual/consulta/documentos"; //prod
            $CI =& get_instance();
            $CI->load->model('custom/ConnectUrl');

            $jsonData = new \stdClass();
            $jsonData->rut = $rut;
            $jsonData->contract_number = $contract_number;
            $jsonDataEncoded = json_encode($jsonData);

            $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded);
    
            if(is_bool($service))
            {
                $this->error['message'] = $CI->ConnectUrl->getResponseError();
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




    /**
    * Servicio que obtiene los contratos de una organización.
    *
    * @param string     $rut  Que representa el rut de la organización a la cual pertenece el contacto.
    *
    * @return JSON
    */
    public function getAllContractsByRut($rut)
    {
        try
        {
            //$url = "http://api-test.dimacofi.cl/sucursalVirtual/consulta/contratos"; //Test
            //$url = "https://api.dimacofi.cl/sucursalVirtual/consulta/contratos"; //prod
            
            
            
            $url = "https://api.dimacofi.cl/sucursalVirtual/getContractsH"; //prod
            
            $CI =& get_instance();
            $CI->load->model('custom/ConnectUrl');

            $jsonData = new \stdClass();
            $jsonData->rut = $rut;
            $jsonDataEncoded = json_encode($jsonData);

            $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded);

            if(is_bool($service))
            {
                $this->error['message'] = $CI->ConnectUrl->getResponseError();
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


    /**
    * Servicio que obtiene los contratos de Holding
    *
    * @param string     $rut  Que representa el rut de la organización a la cual pertenece el contacto.
    *
    * @return JSON
    */
    public function getAllContractsByRutH($rut)
    {
        try
        {
            //$url = "http://api-test.dimacofi.cl/sucursalVirtual/consulta/contratos"; //Test
            //$url = "https://api.dimacofi.cl/sucursalVirtual/consulta/contratos"; //prod
            $url = "https://api.dimacofi.cl/sucursalVirtual/getContractsH"; //prod
            
            $CI =& get_instance();
            $CI->load->model('custom/ConnectUrl');

            $jsonData = new \stdClass();
            $jsonData->rut = $rut;
            $jsonDataEncoded = json_encode($jsonData);

            $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded);

            if(is_bool($service))
            {
                $this->error['message'] = $CI->ConnectUrl->getResponseError();
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

    /**
    * Servicio que obtiene la url de web pay.
    *
    * @param string     $rut  Que representa el rut de la organización a la cual pertenece el contacto.
    *
    * @return JSON
    */
    public function initTransaction($invoice_number, $amount)
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

              $token = $a_jsonToken["access_token"];
              // echo $token;
              $url   = "https://api.dimacofi.cl/wp/initTransaction";
              $CI    =&get_instance();
              $CI->load->model('custom/ConnectUrl');

              $jsonData                                                    = new \stdClass();
              $jsonData->initTransaction                                   = new \stdClass();
              $jsonData->initTransaction->wSTransactionType                = "TR_NORMAL_WS";
              $jsonData->initTransaction->commerceId                       = "";
              $jsonData->initTransaction->buyOrder                         = "";
              $jsonData->initTransaction->sessionId                        = "";
              $jsonData->initTransaction->returnURL                        = "https://soportedimacoficl.custhelp.com/cc/Transactions/validTransaction"; //TODO CAMBIAR POR URL DINAMICA
              $jsonData->initTransaction->finalURL                         = "https://soportedimacoficl.custhelp.com/cc/Transactions/sucess"; //TODO CAMBIAR POR URL DINAMICA
              $jsonData->initTransaction->transactionDetails               = new \stdClass();
              $jsonData->initTransaction->transactionDetails->amount       = round($amount);
              // $jsonData->initTransaction->transactionDetails->commerceCode = "597020000540";
              $jsonData->initTransaction->transactionDetails->commerceCode = "597032782698";
              $jsonData->initTransaction->transactionDetails->buyOrder     = $invoice_number;

              $jsonDataEncoded = json_encode($jsonData);

              $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded, $token);

              if(is_bool($service))
              {
                  $this->error['message'] = "Error obtenido valores webpay ".$CI->ConnectUrl->getResponseError();
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

    public function initTransaction2($invoice_number, $amount)
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

              $token = $a_jsonToken["access_token"];
              // echo $token;
              $url   = "https://api.dimacofi.cl/sucursalVirtual/transaction";
              $CI    =&get_instance();
              $CI->load->model('custom/ConnectUrl');

              
              $service = $CI->ConnectUrl->requestCURLJsonRaw($url, '');

              
              if(is_bool($service))
              {
                  $this->error['message'] = "Error obtenido valores webpay ".$CI->ConnectUrl->getResponseError();
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
          $url = "https://api.dimacofi.cl/token";
          $CI =& get_instance();
          $CI->load->model('custom/ConnectUrl');

          $data           = array("grant_type" => "client_credentials");
          // $consumerKey    = "TPP7XVCdWtQ8AMbQzpUuRu_a3bEa";
          $consumerKey    = "Lew2akNsSYkM9j92eQvU50_BfFEa"; //Lew2akNsSYkM9j92eQvU50_BfFEa
          //$consumerSecret = "awZfmg7Q3mgeKzCn6YRMQh_LRBIa";
          $consumerSecret = "uP1Q_Coeio8w_nytC_MuTBfENhga";

          $service = $CI->ConnectUrl->requestCURLByPost($url, $data, $consumerKey.":".$consumerSecret);

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
/**
    * Servicio que obtiene datos de Factura DTE
    *
    * @param string     $ numero de factura
    *
    * @return JSON
    */
    public function getDTEInvoice($trx_number)
    {
        try
        {
            
            $url = "https://api.dimacofi.cl/apiCloudMD/SearchDTEInvoice"; //prod
            


            $CI =& get_instance();
            $CI->load->model('custom/ConnectUrl');

            $jsonToken = $this->getToken();
            if ($jsonToken !== false)
            {
              $a_jsonToken = json_decode($jsonToken, true);
              if (empty($a_jsonToken["access_token"]))
              {
                $this->error['message'] = "Json de token invalido ".$jsonToken;
                return false;
              }
  
              $token = $a_jsonToken["access_token"];
  


                $jsonData = new \stdClass();
                $jsonData->trx_number = $trx_number;
                $jsonDataEncoded = json_encode($jsonData);

                $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded, $token);

                if(is_bool($service))
                {
                    $this->error['message'] = $CI->ConnectUrl->getResponseError();
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
    /**
    * Servicio para obtener el resultado de la transacción desde webpay
    *
    * @param void
    *
    * @return JSON
    */
    public function getTransactionResult($token_ws)
    {
      try
      {
          $url = "https://api.dimacofi.cl/wp/1.0.0/getTransactionResult";
          $CI =& get_instance();
          $CI->load->model('custom/ConnectUrl');

          $jsonToken = $this->getToken();
          if ($jsonToken !== false)
          {
            $a_jsonToken = json_decode($jsonToken, true);
            if (empty($a_jsonToken["access_token"]))
            {
              $this->error['message'] = "Json de token invalido ".$jsonToken;
              return false;
            }

            $token = $a_jsonToken["access_token"];

            // echo "token wp ".$token_ws."<br>";
            // echo "token dimacofi ".$token."<br>";

            $jsonData                                                     = new \stdClass();
            $jsonData->getTransactionResult                               = new \stdClass();
            $jsonData->getTransactionResult->tokenInput                   = $token_ws;
            $jsonData->getTransactionResult->source                       = "SV";
            $jsonData->getTransactionResult->reference                    = "";


            $jsonDataEncoded = json_encode($jsonData);

            //exit();

            $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded, $token);

            if(is_bool($service))
            {
                $this->error['message'] = "Error obteniendo datos de pago webpay ".$CI->ConnectUrl->getResponseError();
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

  /*
    * Johan Rujano
    * Crea en mensaje en la cola del WSO2
    */
    public function createReceipt($data){

      
        $url = "https://api.dimacofi.cl/wp/1.0.0/getTransactionResult";
        $CI =& get_instance();
        $CI->load->model('custom/ConnectUrl');
  
        $jsonToken = $this->getToken();
        if ($jsonToken !== false)
        {
          $a_jsonToken = json_decode($jsonToken, true);
          if (empty($a_jsonToken["access_token"]))
          {
            $this->error['message'] = "Json de token invalido ".$jsonToken;
            return false;
          }
  
          $token = $a_jsonToken["access_token"];
  
          $service2 = $CI->ConnectUrl->requestCURLJsonRaw("https://api.dimacofi.cl/wp/1.0.0/createReceipt", $data, $token);
  
        }
      }
  
  
    /**
    * Servicio para avisar dar conocimiento a weppay de la recepción de los pagos
    *
    * @param void
    *
    * @return JSON
    */
    public function acknowledgeTransaction($token_ws)
    {
      try
      {
          $url = "https://api.dimacofi.cl/wp/1.0.0/acknowledgeTransaction";
          $CI =& get_instance();
          $CI->load->model('custom/ConnectUrl');

          $jsonToken = $this->getToken();
          if ($jsonToken !== false)
          {
            $a_jsonToken = json_decode($jsonToken, true);
            if (empty($a_jsonToken["access_token"]))
            {
              $this->error['message'] = "Json de token invalido ".$jsonToken;
              return false;
            }

            $token = $a_jsonToken["access_token"];

            // echo "token wp ".$token_ws."<br>";
            // echo "token dimacofi ".$token."<br>";

            $jsonData                                                     = new \stdClass();
            $jsonData->acknowledgeTransaction                               = new \stdClass();
            $jsonData->acknowledgeTransaction->tokenInput                   = $token_ws;
            $jsonData->acknowledgeTransaction->source                       = "SV";
            $jsonData->acknowledgeTransaction->reference                    = "";


            $jsonDataEncoded = json_encode($jsonData);

            //exit();

            $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded, $token);

            if(is_bool($service))
            {
                $this->error['message'] = "[acknowledgeTransaction] Error obteniendo respuesta de webpay ".$CI->ConnectUrl->getResponseError();
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

    public function SearchInvoice($rut,$invoice_number,$invoice_rut,$invoice_contrato,$invoice_from,$invoice_to)
    {
        
        
        
        try{
            $url = "https://api.dimacofi.cl/sucursalVirtual/SearchRutInvoice";
     
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
            $a_request->rut = $rut;
            $a_request->invoice_number = $invoice_number;
            $a_request->invoice_rut = $invoice_rut;
            $a_request->invoice_contrato = "". $invoice_contrato . "";
            $a_request->invoice_from = $invoice_from;
            $a_request->invoice_to = $invoice_to;
            $jsonDataEncoded = json_encode($a_request);
          
            $CI =& get_instance();
            $CI->load->model('custom/ConnectUrl');

            //$jsonDataEncoded = json_encode($a_request);

          
            $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded);
    
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


    public function SearchInvoiceDetail($data,$contactId)
    {
        
        $CI = get_instance();
           
        $contact = $CI->Contact->get($contactId);
        $rut=$contact->Organization->CustomFields->c->rut;
        
        try{
            $url = "https://api.dimacofi.cl/sucursalVirtual/consulta/factura/detalle";
     
            $a_request = array(
                
                "rut"=> $data["rut"],
                "contract_number"=> $data["contract_number"] ,
                "invoice_number"=>  $data["invoice_number"] 
            );
            
            $CI =& get_instance();
            $CI->load->model('custom/ConnectUrl');

            $jsonDataEncoded = json_encode($a_request);
            
            $service = $CI->ConnectUrl->requestCURLJsonRaw($url, $jsonDataEncoded);
    
          

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

    public function getLastError()
    {
      return $this->error['message'];
    }





}