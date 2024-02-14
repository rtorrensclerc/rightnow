<?php
namespace Custom\Controllers;
use RightNow\Connect\v1_2 as RNCPHP;
class TestServices extends \RightNow\Controllers\Base
{
    public function __construct()
    {
        parent::__construct();
        //$this->load->model('custom/Consumption');
        $this->load->model('custom/PaymentsInvoicesServices');
        $this->load->model('custom/ConnectUrl');
    }


    public function getHH()
    {
        try
        {
            // Se obtendrá un token
            $jsonToken = $this->PaymentsInvoicesServices->getToken();
            if($jsonToken === FALSE)
                throw new \Exception("'getHH' {$this->PaymentsInvoicesServices->getLastError()}", 1);

            $a_jsonToken = json_decode($jsonToken, TRUE);

            if (empty($a_jsonToken["access_token"]))
                throw new \Exception("'getHH' Json de token invalido {$jsonToken}", 2);

            $token = $a_jsonToken["access_token"];
            $url   = "https://api-test.dimacofi.cl/apiEBS/GetListHH";
            $rut   = "76570350-6";

            $a_request = array(
                "RUT" => $rut
            );

            

            $json_request = json_encode($a_request);
            /* echo "<pre>";
            print_r($a_jsonToken);
            echo "</pre>"; */
            $response     = $this->ConnectUrl->requestCURLJsonRaw($url, $json_request, $token);

            if($response === FALSE)
                throw new \Exception("'getHH' Error obteniendo listado de HH para el RUT {$rut}. {$this->ConnectUrl->getResponseError()}", 3);
            else
                return $response;


        }
        catch(\Exception $e)
        {
            header('Content-Type: application/json');
            $a_response = array("success" => FALSE, "error" => array("message" => $e->getMessage(), "code" => $e->getCode(), "line" => $e->getLine()));
            echo json_encode($a_response);
        }
    }

    public function lastConsumptionsMonths()
    {
        $response = array(
              'result' => true ,
              'response' => array (
                  'message' => "Consumos obtenidos exitosamente.",
                  'info' => array(
                    array (
                        "period" => "11-2017",
                        "data" => array(
                              array(
                                "counter_type"=> "Copias A3 BN",
                                "quantity"=> 6869,
                                "percentage"=> 41.42,
                                "invoice" => 261736
                              ),
                              array(
                                "counter_type"=> "Copias A3 Color",
                                "quantity"=> 3876,
                                "percentage"=> 23.37,
                                "invoice"=> 261736
                              ),
                              array(
                                "counter_type"=> "Copias B4 BN",
                                "quantity"=> 1916,
                                "percentage"=> 11.55,
                                "invoice"=> 261736
                              ),
                        ),
                      ),
                      array(
                        "period" => "12-2017",
                        "data" => array(
                              array(
                                "counter_type"=> "Copias A3 BN",
                                "quantity"=> 3501,
                                "percentage"=> 10.75,
                                "invoice"=> 266419
                              ),
                              array(
                                "counter_type"=> "Copias A3 Color",
                                "quantity"=> 10151,
                                "percentage"=> 31.18,
                                "invoice"=> 266419
                              ),
                              array(
                                "counter_type"=> "Copias B4 BN",
                                "quantity"=> 16505,
                                "percentage"=> 50.69,
                                "invoice"=> 266419
                              ),
                            ),
                      ),
                  ),
                ),
            );

        $response_error = array(
            "result" => false,
            "error" => array(
                "code" => 0,
                "message" => "Error en la solicitud"
            ),
        );
        $encoded = json_encode($response);
        $decoded = json_decode($encoded, true);
        header('Content-Type: application/json');
        echo $encoded;
    }

    public function lastConsumptionsLines()
    {


        $response = array(
            "result" => true,
            "response" => array(
                "message" => "Operación realizada exitosamente.",
                "header" => array(
                   "quantity_hh" => 2,
                   "divisa" => "UF",
                   "exchange_rate" => 26500.2,
                   "amount_clp" => 1000000,
                   "amount" => 10,
                   "fixed_amount" => 0,
                   "quantity_bn" => 10,
                   "quantity_color" => 100,
                   "minimun_bn" => 0,
                   "minimun_color" => 0
                ),
                "lines" => array(
                    array(
                       "hh" => "1597534",
                       "serie" => "07LQBJFH5000EDJ",
                       "model" => "EQUIPO  SL M4580FX",
                       "fixed_amount" => 2,
                       "last_date" => "29/05/2018",
                       "last_read" => 10,
                       "actual_date" => "29/06/2018",
                       "actual_read" => 100,
                       "counter_type" => "Copias B/N",
                       "credit" => 0,
                       "real_quantity" => 90,
                       "billed_quantity" => 100,
                       "rate" => 0.00072,
                       "amount" => 100,
                       "address" => "HUERFANOS 355"
                    ),
                    array(
                       "hh" => "1597534",
                       "serie" => "07LQBJFH5000EDJ",
                       "model" => "EQUIPO  SL M4580FX",
                       "fixed_amount" => 4,
                       "last_date" => "29/05/2018",
                       "last_read" => 10,
                       "actual_date" => "29/06/2018",
                       "actual_read" => 100,
                       "counter_type" => "Copias B/N",
                       "credit" => 0,
                       "real_quantity" => 90,
                       "billed_quantity" => 100,
                       "rate" => 0.00072,
                       "amount" => 100,
                       "address" => "HUERFANOS 355"
                    ),
                ),
            ),
        );

        $response_error = array(
            "result" => false,
            "error" => array(
                "code" => 0,
                "message" => "Error en la solicitud"
            ),
        );
        $encoded = json_encode($response);
        $decoded = json_decode($encoded, true);
        echo $encoded;

    }

    public function getContracts()
    {
        $response = array(
            "result" => true,
            "response" => array(
                "message" => "Contratos obtenidos exitosamente.",
                "lines" => array(
                    array(
                        "contract_number" => 1,
                        "customer" => "contrato 1",
                        "status" => "ACTIVO"
                    ),
                    array(
                        "contract_number" => 2,
                        "customer" => "contrato 2",
                        "status" => "ACTIVO"
                    ),
                ),
            ),
        );

        $encoded = json_encode($response);
        $decoded = json_decode($encoded, true);
        echo $encoded;
    }


    public function getInvoices()
    {
        $response = array(
            "result" => true,
            "response" => array(
                "message" => "Operación realizada exitosamente!",
                "last_payments" => array(
                    array(
                      "pay_number" => "307049",
                      "pay_date" => "31/02/2018",
                      "pay_method" => "CHECK",
                      "pay_invoices" => array(
                          array(
                            "trx_number" => "12345",
                            "trx_date" => "31/02/2018",
                            "trx_amount" => 12345432
                          ),
                      ),
                      "pay_amount" => 123456,
                    ),
                    array(
                      "pay_number" => "307050",
                      "pay_date" => "31/02/2018",
                      "pay_method" => "CHECK",
                      "pay_invoices" => array(
                          array(
                            "trx_number" => "234323",
                            "trx_date" => "31/02/2018",
                            "trx_amount" => 21312
                          ),
                      ),
                      "pay_amount" => 323232,
                    ),
                ),
                "last_invoices" => array(
                    array(
                      "trx_number" => "307049",
                      "trx_date" => "31/02/2018",
                      "due_date" => "31/03/2018",
                      "trx_amount" => 123456,
                      "url_dte" => "https://dimacofi.getdte.cl/custodia_digital.php?id=S92083000-5_T33_F307049_H31f3bbfa18ffc5f6e07852ee71fa2793"
                    ),
                    array(
                      "trx_number" => "12323",
                      "trx_date" => "31/03/2018",
                      "due_date" => "31/04/2018",
                      "trx_amount" => 234323,
                      "url_dte" => "https://dimacofi.getdte.cl/custodia_digital.php?id=S92083000-5_T33_F307049_H31f3bbfa18ffc5f6e07852ee71fa2793"
                    ),
                ),
            ),
        );

        $encoded = json_encode($response);
        $decoded = json_decode($encoded, true);
        echo $encoded;
    }
    public function getIncidents()
    {
        $i=0;
        $incidentR = RNCPHP\Incident::find("CustomFields.c.supply_reason.ID=285 and ReferenceNumber like '211217%'" );
        foreach ($incidentR as $key => $value)
        {
            echo $i .'-'.$value->Subject . '-' . $value->ReferenceNumber . '<br>';
            $value->destroy();
            $i++;
            if($i==1000)
            {
                exit;
            }
        }
    }
}
