<?php
namespace Custom\Controllers;
use RightNow\Connect\v1_2 as RNCPHP;
class Transactions extends \RightNow\Controllers\Base
{
    public $error;
    public $rut_org;
    public function __construct()
    {
        parent::__construct();
        $this->response = new \stdClass;
        $this->response->errors = array();
        $this->response->messages = array();
        $this->load->model('custom/Organization');
        $this->load->model('custom/Transaction');
        $this->load->model('custom/PaymentsInvoicesServices');
        $this->load->model('custom/Contact');
        $this->load->helper('utils_helper');
    }


    public function initTransaction2()
  {


    $result = $this->PaymentsInvoicesServices->initTransaction2($invoice, $amount);
    $data=json_decode($result);

    echo '<html>
    <body>
        <form name="webpayform" method="post" action="'.$data->url.'">
            <input type="hidden" name="token_ws" value="'.$data->token.'">
        </form>
        <script type="text/javascript">
            document.webpayform.submit();
        </script>
    </body>
</html>';
  }

    public function initTransaction()
    {
     //  $this->preventDirectAccess();
  
      $invoice              = getUrlParm('invoice'); //"256659"
      $amount               = getUrlParm('amount');  //1000
      $contract_number      = getUrlParm('contract_number');// 31978
      $rut                  = getUrlParm('rut');  //opcional 82392600-6
      if (empty($rut))
        $rut = getCompanyRutBySession();

      $amount = $this->getAmountService($invoice, $contract_number, $rut);

      if ($amount === false)
      {
        echo "Web service : ".$this->error;
        return;
      }
      //$this->load->model('custom/PaymentsInvoicesServices');
      $result = $this->PaymentsInvoicesServices->initTransaction($invoice, $amount);
      if ($result == false)
        echo "Web service : ".$this->PaymentsInvoicesServices->getLastError();
      else
      {
        $array_webpay = json_decode($result, true);

        if (is_array($array_webpay))
        {
          $a_transactionWepPay = $array_webpay["initTransactionResponse"];
          if (!empty($a_transactionWepPay['return']['token']) and !empty($a_transactionWepPay['return']['url']))
          {
            $token             = $a_transactionWepPay['return']['token'];
            $url2              = $a_transactionWepPay['return']['url'];


            echo '<html>
                <body>
                    <form name="webpayform" method="post" action="'.$url2.'">
                        <input type="hidden" name="token_ws" value="'.$token.'">
                    </form>
                    <script type="text/javascript">
                        document.webpayform.submit();
                    </script>
                </body>
            </html>';
          }
          else
          {
            echo "Json invalido ".$a_transactionWepPay;
          }
        }
      }
    }


    private function getAmountService($invoice, $contract_number, $organization_rut)
    {
      try
      {
          //$json_consumptionsLines = $this->PaymentsInvoicesServices->getLastConsumptionsLines($organization_rut, $contract_number, $invoice);
          
          
          $invoice=$this->PaymentsInvoicesServices->SearchInvoice($organization_rut,$invoice,$organization_rut,'','','');
          
          /*if (is_bool($json_consumptionsLines))
          {
              $this->error = $this->PaymentsInvoicesServices->getLastError();
              return false;
          }

          $decoded = json_decode($json_consumptionsLines, true);

          if (!is_array($decoded))
          {
              $this->error = "La información recibida no es de tipo JSON válido";
              return false;
          }

          if (!$decoded["result"])
          {
              $this->error = "Error en la solicitud: ".$decoded["response"]["message"] ;
              return false;
          }*/


          // se cambia amount_clp amount_remaining 
          // $amount = $decoded["response"]["header"]["amount_clp"];
          // $iva    = ((int)$amount * 19) / 100;
          // $total  = $amount + $iva;
          // RTORRENS 21/10/2020
          //$total = $decoded["response"]["header"]["amount_remaining"];

          $total= $invoice->response->lines->ammount_remaining;
          return $total;

      }
      catch(\Exception $e)
      {
          $this->error = $this->PaymentsInvoicesServices->getLastError();
          return false;
      }

    }

    public function validTransaction()
    {
      try
      {
        $token_ws = $_POST["token_ws"];

        //Validación de de valor de token
        if (empty($token_ws))
        {
          throw new \Exception('Token nulo o vacio!', 3);
        }

        $result = $this->PaymentsInvoicesServices->getTransactionResult($token_ws);

        if ($result !== false)
        {
          //echo $result;

          $finalMessage = $this->PaymentsInvoicesServices->acknowledgeTransaction($token_ws);
          if ($finalMessage === false)
          {
            echo "Error ".$this->PaymentsInvoicesServices->getLastError();
          }
          else
          {
            //echo $finalMessage;


            $url = $this->saveTransaction($result);
            if ($url === false)
            {
              throw new \Exception($this->error, 4); //Error en la información recibida
            }
            else
            {
              header("Location: $url");
            }
          }
        }
        else
        {
          echo "Error ".$this->PaymentsInvoicesServices->getLastError();
        }


      }
      catch(\Exception $e)
      {
        header('Content-Type: application/json');
        $a_result = array("result"=> false,  "error" => array("code"=> $e->getCode(), "message" => $e->getMessage()));
        echo json_encode($a_result);
      }

    }

    public function sucess()
    {
      try
      {
        $url = \RightNow\Utils\Url::getOriginalUrl(false);
        header("Location: ".$url."/app/sv/home");  
        //print_r($_POST);
      }
      catch(\Exception $e)
      {
        header('Content-Type: application/json');
        $a_result = array("result"=> false,  "error" => array("code"=> $e->getCode(), "message" => $e->getMessage()));
        echo json_encode($a_result);
      }

    }

    private function saveTransaction($json)
    {
   
      try
      {
        $decoded = json_decode($json, true);

        if (!is_array($decoded))
        {
          throw new \Exception('El contenido recibido no es un JSON valido.', 5);
        }

        $data_json = $decoded["getTransactionResultResponse"]["return"];

        if (empty($data_json))
        {
          throw new \Exception('El contenido getTransactionResultResponse->return no esta presente', 6);
        }

        //Se crea un objeto para posteriormente enviarlo al modelo
        $transaction_c                    = new \stdClass();
        $transaction_c->authorizacionCode = $data_json['detailOutput']['authorizationCode'];
        $transaction_c->shareNumber       = (int) $data_json['detailOutput']['sharesNumber'];
        $transaction_c->paymentTypeCode   = $data_json['detailOutput']['paymentTypeCode'];
        $transaction_c->responseCode      = $data_json['detailOutput']['responseCode'];
        $transaction_c->amount            = $data_json['detailOutput']['amount'];
        // $transaction_c->invoice_rut       = $invoiceRut;
        $transaction_c->invoice_number    = $data_json['buy_order'];
        $transaction_c->info_json         = $json; //El json que llegó
        $transaction_c->cardNumber        = $data_json['cardDetail']['cardNumber'];
        $transaction_c->transactionDate   = strtotime($data_json['transactionDate']);
        $transaction_c->organization_rut  = (empty(getCompanyRutBySession()))?null:getCompanyRutBySession();
        $transaction_c->contact_id        = (\RightNow\Utils\Framework::isLoggedIn())?$this->session->getProfile()->c_id->value:null;


        $transaction_id = $this->Transaction->setBasicNewTransaction($transaction_c);
        if(is_bool($transaction_id))
        {
            $msgError = $this->Transaction->getLastError();
            throw new \Exception("Error en Api Interna: ".$msgError, 2);
        }
        if($transaction_c->responseCode=='0')
        {
            $receipt = $this->PaymentsInvoicesServices->createReceipt($json);
        }
        

        $url = \RightNow\Utils\Url::getOriginalUrl(false)."/app/sv/payment/voucher/id/".$transaction_id.$transaction_c->transactionDate;
        return $url;

        //header("Location: $url");
      }
      catch(\Exception $e)
      {
        $this->error = $e->getMessage();
        return false;
      }
    }


    private function getResponseCodeError($errorCode)
    {
        $msg = "";
        switch ($errorCode)
        {
            case -1:
                $msg = "Rechazo de transacción.";
                break;
            case -2:
                $msg = "Transacción debe reinventarse.";
                break;
            case -3:
                $msg = "Error en transacción.";
                break;
            case -4:
                $msg = "Rechazo de transacción.";
                break;
            case -5:
                $msg = "Rechazo por error de tasa.";
                break;
            case -6:
                $msg = "Exceda cupo máximo mensual.";
                break;
            case -7:
                $msg = "Excede límite diario por transacción.";
                break;
            case -8:
                $msg = "Rubro no autorizado.";
                break;
        }

          return $msg;
    }

    private function is_validRut($rut)
    {
        $rut = preg_replace('/[^k0-9,.+*]/i', '', $rut);
        $dv  = substr($rut, -1);
        $numero = substr($rut, 0, strlen($rut)-1);

        $n = (int)$numero;
        if(!$n)
        {
            return false;
        }
        else
        {
            $i = 2;
            $suma = 0;
            foreach(array_reverse(str_split($numero)) as $v)
            {
                if($i==8)
                    $i = 2;
                $suma += $v * $i;
                ++$i;
            }
            $dvr = 11 - ($suma % 11);

            if($dvr == 11)
                $dvr = 0;
            if($dvr == 10)
                $dvr = 'K';
            if($dvr == strtoupper($dv))
                return true;
            else
                return false;
        }

    }
    public function test()
    {
      $transaction=RNCPHP\TRA\Transaction2::fetch(33);
        //echo json_encode($transaction);
        echo $transaction->ID . $transaction->TransactionDate;
    }
/*
    public function createReceiptM()
    {
      $ajson= array();

      /*'{
          "getTransactionResultResponse":
              {"return":
                  {
                    "accountingDate":"0521",
                    "buyOrder":"509874",
                    "cardDetail":
                    {
                      "cardNumber":"2532"
                    },
                    "detailOutput":
                    {
                      "sharesNumber":"0",
                      "amount":"30728",
                      "commerceCode":"597032782698",
                      "buyOrder":"509874",
                      "authorizationCode":"539402",
                      "paymentTypeCode":"VN",
                      "responseCode":"0"
                    },
                    "transactionDate":"2020-05-21T18:53:51.856-04:00",
                    "urlRedirection":"https://webpay3g.transbank.cl:443/webpayserver/voucher.cgi",
                    "VCI":"TSY"
                  }
                }
              }';

      //$ajson['getTransactionResultResponse']['return']['sessionId']='bfc6bab8-f5d7-3ea8-bdd6-16ae41d21cb6';

      $ajson['getTransactionResultResponse']['return']['detailOutput']['authorizationCode']='047456';
      $ajson['getTransactionResultResponse']['return']['detailOutput']['sharesNumber']='6';
      $ajson['getTransactionResultResponse']['return']['detailOutput']['paymentTypeCode']='NC';
      $ajson['getTransactionResultResponse']['return']['detailOutput']['responseCode']='0';
      $ajson['getTransactionResultResponse']['return']['detailOutput']['amount']='628285';
      $ajson['getTransactionResultResponse']['return']['detailOutput']['commerceCode']='597032782698';
      $ajson['getTransactionResultResponse']['return']['detailOutput']['buyOrder']='506933';
      
      $ajson['getTransactionResultResponse']['return']['accountingDate']='0521';
      $ajson['getTransactionResultResponse']['return']['buyOrder']='506933';
      $ajson['getTransactionResultResponse']['return']['cardDetail']['cardNumber']='6287';
      $ajson['getTransactionResultResponse']['return']['transactionDate']='2020-05-21T16:21:25.490-04:00';
      $ajson['getTransactionResultResponse']['return']['urlRedirection']='https://webpay3g.transbank.cl:443/webpayserver/voucher.cgi';
      $ajson['getTransactionResultResponse']['return']['VCI']='TSY';


      echo json_encode($ajson);
      $json=json_encode($ajson);
      $receipt = $this->PaymentsInvoicesServices->createReceipt($json);
      echo json_encode($receipt);
    }
*/
}
