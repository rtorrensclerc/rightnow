<?php
namespace Custom\Widgets\payments;

class ListPaymentsAndInvoices extends \RightNow\Libraries\Widget\Base
{
    //Varibale que almacena los errores surgidos
    public $msgError = "";

    public function __construct($attrs)
    {
        parent::__construct($attrs);

        $this->setAjaxHandlers(array(
            'getInvoicePaymentList' => array(
                'method'      => 'handle_getInvoicePaymentList',
                'clickstream' => 'custom_action',
            ),
            'getLastConsumptionsLines' => array(
              'method'      => 'handle_getLastConsumptionsLines',
              'clickstream' => 'custom_action',
            )
        ));

        //Se cargan los modelos
        $this->CI->load->model('custom/PaymentsInvoicesServices');
        $this->CI->load->model('custom/Contact');
        $this->CI->load->model('custom/GeneralServices');
        
    }

    public function summary_lines($exchange_rate, $lines)
    {
        $a_new_array = [];

        foreach ($lines as $key => $value)
        {
            $rate                                              = $value['rate'] * $exchange_rate;// Se obtiene la tarifa por una copia.

            $total_rate                                        = $value['billed_quantity'] * $rate;// Se obtiene el total de clics.

            $a_new_array[$value['counter_type']]['unit_price'] = $rate;
            $a_new_array[$value['counter_type']]['total']      += $total_rate;
            $a_new_array[$value['counter_type']]['quantity']   += $value['billed_quantity'];
            $a_new_array[$value['counter_type']]['um']         = "UND";
            $a_new_array[$value['counter_type']]['code']       = $value['counter_sku']; //TODO Faltaría hacerlo dinámico
            $a_new_array[$value['counter_type']]['detail']     = $value['counter_type']. " según contrato";
        }

        return $a_new_array;
    }

    /**
     * Handles the getLastConsumptionsLines AJAX request
     * El detalle de factura
     * @param array $params Get / Post parameters
     */
    public function handle_getLastConsumptionsLines($params)
    {
        try
        {
            $rut             = $params["rut"];
            $contract_number = $params["contract_number"];
            $invoice_number  = $params["invoice_number"];
            $quantity_hh;

            

            // Se verifica si el rut contiene algo
            if (empty($rut))
            {
                // Si esta vacio, se obtiene el id del usuario logueado.
                $c_id   = $this->CI->session->getProfile()->c_id->value;
                if ($c_id == null)
                {
                    throw new \Exception('El usuario no existe en la base de datos', 0);
                }

                // Se busca al usuario, mediante su id.
                $user = $this->CI->Contact->getContactById($c_id);

                // Se verifica que el usuario pertenezca a una organización.
                if (is_null($user->Organization))
                {
                    throw new \Exception('El usuario no pertenece a una organización', 0);
                }

                // Se obtiene la organizacion.
                $organization = $user->Organization;

                // Se obtiene el rut de la organizacion.
                $rut = $organization->CustomFields->c->rut;
            }

            //TODO: Comentar despues
            // $rut = "96689310-9";
            // $contract_number = 40946;
            // $invoice_number = 255509;



            // Se llama al método del modelo que consume el servicio y lo obtenido se guarda en la variable $json_consumptionsLines.
            $json_consumptionsLines = $this->CI->PaymentsInvoicesServices->getLastConsumptionsLines($rut, $contract_number, $invoice_number);

            //echo "Rut " . $rut . " contract_number " . $contract_number . " " . $invoice_number . " <br>";

            // print_r($json_consumptionsLines);

            // Se verifica si el valor $json_consumptionsLines es un booleano.
            if (is_bool($json_consumptionsLines))
            {
                $msgError = $this->CI->PaymentsInvoicesServices->getLastError();
                throw new \Exception($msgError);
            }

            // Se convierte el json recibido en un arreglo asociativo del tipo "clave"=>"valor"
            $decoded = json_decode($json_consumptionsLines, true);

            /* echo "<pre>";
            print_r($json_consumptionsLines);
            echo "</pre>"; */

            // Si no es un array
            if (!is_array($decoded))
            {
                throw new \Exception('La información recibida no es de tipo JSON válido', 4);
            }

            // Se verifica que el campo 'result' del JSON esperado sea verdadero.
            if (!$decoded["result"])
            {
                throw new \Exception('Error en la solicitud'. $decoded["response"]["message"], 0);
            }

            // Se verificarán los datos del header
            $decoded["response"]["header"]["invoice_number"]  = $invoice_number;
            $decoded["response"]["header"]["contract_number"] = $contract_number;
            $quantity_hh                                      = $decoded["response"]["header"]["quantity_hh"];
            $a_headers                                        = $decoded["response"]["header"];


            // if (!is_int($a_headers["quantity_hh"]) || !is_string($a_headers["divisa"]) || !is_float($a_headers["exchange_rate"]) ||
            //   !is_int($a_headers["amount_clp"]) || !is_numeric($a_headers["amount"]) || !is_int($a_headers["fixed_amount"]) ||
            //   !is_int($a_headers["quantity_bn"]) || !is_int($a_headers["quantity_color"]) || !is_int($a_headers["minimun_bn"]) ||
            //   !is_int($a_headers["minimun_color"]))
            // {
            //     throw new \Exception('Error de Integridad de datos', 6);
            // }
            // Se termino de validar el header


            // Se validarán las lineas
            $a_lines = $decoded["response"]["lines"];

            // foreach ($a_lines as $line)
            // {
            //     if (!is_int($line["hh"]) || !is_string($line["model"]) ||
            //       !is_numeric($line["fixed_amount"]) || !is_int($line["last_read"]) || !is_string($line["last_date"]) ||
            //       !is_string($line["actual_date"]) || !is_int($line["actual_read"]) || !is_int($line["credit"]) ||
            //       !is_string($line["counter_type"]) || !is_int($line["real_quantity"]) || !is_int($line["billed_quantity"]) ||
            //       !is_numeric($line["amount"]) || !is_string($line["address"])
            //     ) {
            //         throw new \Exception('Error de Integridad de datos', 6);
            //     }
            // }

            // Se agrupará las líneas mediante su counter_type y se sumarán los valores
            //Se obtiene la tasa de cambio
            $exchange_rate = $a_headers["exchange_rate"];

            //Se obtiene la suma de las lineas agrupadas por su counter_type
            $summary_lines = $this->summary_lines($exchange_rate, $a_lines);

            //Se obtiene el cargo fijo de todas las líneas
            
            // Cuando no es CLP
            if($decoded["response"]["header"]["divisa"] != "CLP")
            {
                $summary_fixed_amount          = $quantity_hh * $exchange_rate;
                $a_fixed_amount                = array(
                    "unit_price"               => $summary_fixed_amount,
                    "total"                    => $summary_fixed_amount,
                    "quantity"                 => 1,
                    "um"                       => "UND",
                    "code"                     => 90800,
                    "detail"                   => "Cargo fijo según contrato"
                );

                $summary_lines["fixed_amount"] = $a_fixed_amount;
            }

            // Cuando es CLP
            $summary_fixed_amount = $this->totalFixedAmount($a_lines);
            if ($summary_fixed_amount > 0)
            {
              $a_fixed_amount                = array(
                  "unit_price"               => $summary_fixed_amount,
                  "total"                    => $summary_fixed_amount,
                  "quantity"                 => 1,
                  "um"                       => "UND",
                  "code"                     => 90800,
                  "detail"                   => "Cargo fijo según contrato"
              );

              $summary_lines["fixed_amount"] = $a_fixed_amount;
            }

            

            // Se crea un objeto con el formato de respuesta.
            $response                        = new \stdClass();
            $response->success               = ($decoded["result"])?true: false;
            $response->message               = $decoded["response"]["message"];
            $response->detail                = new \stdClass();
            $response->detail->header        = $a_headers;
            $response->detail->lines         = $decoded["response"]["lines"];
            $response->detail->summary_lines = $summary_lines;
            //$response->detail->fixed_amount = $summary_fixed_amount;

            // Expone la respuesta
            header('Content-Type: application/json');
            echo json_encode($response);
        }
        catch (\Exception $e)
        {
            header('Content-Type: application/json');
            $a_result = array("success"=> false,  "message" => $e->getMessage()." ".$e->getCode()."");
            $encoded = json_encode($a_result);
            echo $encoded;
        }
    }

    public function totalFixedAmount($lines)
    {
       $sum_fixed = array_sum(array_column($lines, 'fixed_amount'));
       return $sum_fixed;
    }

    public function getData()
    {
        $contracts = array();
        $error = "";
        $rut;
        $a_contracts;

        //Se verifica de que haya una session activa
        if (\RightNow\Utils\Framework::isLoggedIn() == false)
        {
            $error = 'El usuario debe iniciar sesión';
        }
        else
        {
            //Se busca al usuario, mediante su id
            $c_id = $this->CI->session->getProfile()->c_id->value;
            
            $user = $this->CI->Contact->getContactById($c_id);

            //Se verifica que el usuario pertenezca a una organización
            if (is_null($user->Organization))
            {
                $error = "El usuario no pertenece a una organización";
            }
            else
            {
                $organization = $user->Organization;
                $rut = $organization->CustomFields->c->rut;

                // carga lista de contratos
                $a_contracts = $this->getContracts($rut);
                //$a_ruts = $this->getRuts($rut);
                
                //$statussai=$this->CI->GeneralServices->getOrganizationStatusbyRut($rut);
                //Verificamos que contenga contratos
                if (is_bool($a_contracts))
                {
                    $error = $this->getLastError();
                }
                else
                {
                    $contracts = $a_contracts;
                }
            }
        }


        // $json = array(
        //     "rut" => "1-9",
        //     "contract_number" => 0
        // );
        // $json_encoded = json_encode($json);
        //
        // $a_data = array(
        //     "data" => $json_encoded
        // );
        // $this->handle_getInvoicePaymentList($a_data);


        $this->data['js']['list']['contracts_error'] = $error;
        $this->data['js']['list']['contracts'] = $contracts; //array de ID, name de contratos
    


        return parent::getData();
    }

    /**
     * Handles the getInvoicePaymentList AJAX request
     * @param array $params Get / Post parameters
     */
    public function handle_getInvoicePaymentList($params)
    {

        try
        {
            $this->CI        = & get_instance();
            $rut             = $params["rut"];
            $contract_number = $params["contract_number"];



            //Se verifica si el rut contiene algo
            if (empty($rut))
            {
                //Si esta vacio, se obtiene el id del usuario logueado
                $c_id   = $this->CI->session->getProfile()->c_id->value;

                //echo "ID de contacto ".$c_id;
                if ($c_id == null)
                {
                    throw new \Exception('El usuario no existe en la base de datos', 0);
                }


                //Se busca al usuario, mediante su id
                $user = $this->CI->Contact->getContactById($c_id);

                //Se verifica que el usuario pertenezca a una organización
                if (is_null($user->Organization))
                {
                    throw new \Exception('El usuario no pertenece a una organización', 0);
                }

                $organization = $user->Organization;
                $rut = $organization->CustomFields->c->rut;
            }

            //TODO: eliminar esto mas adelante
            // $rut = "65045929-6";
            // $contract_number = 32023;


            $json_invoices = $this->CI->PaymentsInvoicesServices->getLastSixInvoices($rut, $contract_number);

            if(is_bool($json_invoices))
            {
                $msgError = $this->CI->PaymentsInvoicesServices->getLastError();
                throw new \Exception($msgError);
            }

            $decoded = json_decode($json_invoices, true);


            if (!is_array($decoded))
            {
                throw new \Exception('La información recibida no es de tipo JSON válido', 4);
            }

            $a_payments = $decoded["response"]["last_payments"];
            
            if($decoded["response"]["last_invoices"]["amount_remaining"]==null)
            {
                $a_invoices = $decoded["response"]["last_invoices"];
            }
            else
            {
              $a_invoices[] = $decoded["response"]["last_invoices"];
            }


            // foreach ($a_payments as $payment)
            // {
            //     if (!is_string($payment["pay_number"]) || !is_int($payment["pay_amount"]))
            //     {
            //         throw new \Exception('Error de Integridad de datos', 6);
            //     }
            //     if (intval($payment["pay_number"]) == 0)
            //     {
            //         throw new \Exception('El número de pago debe ser un valor numérico!', 8);
            //     }
            //     if (intval($payment["pay_amount"]) == 0)
            //     {
            //         throw new \Exception('El monto de pago debe ser un valor numérico!', 8);
            //     }
            //
            //     foreach ($payment["pay_invoices"] as $pay_invoice)
            //     {
            //         if (!is_string($pay_invoice["trx_number"]) || !is_int($pay_invoice["trx_amount"]))
            //         {
            //             throw new \Exception('Error de Integridad de datos', 6);
            //         }
            //         if (intval($pay_invoice["trx_number"]) == 0)
            //         {
            //             throw new \Exception('El número de transacción debe ser un valor numérico!', 8);
            //         }
            //     }
            // }

            $payment_invoice = array(
                "last_payments" => $a_payments,
                "last_invoices" => $a_invoices
            );

            //Si todo esta bien, se va a responder
            $response          = new \stdClass();
            $response->success = ($decoded["result"])?true:false;
            $response->message = $decoded["response"]["message"];
            $response->detail  = $payment_invoice;
            $response->lastPayments = $a_payments;
            $response->lastInvoices = $a_invoices;

            header('Content-Type: application/json');
            // Expone la respuesta
            echo json_encode($response);
        }
        catch (\Exception $e)
        {
            header('Content-Type: application/json');
            $a_result = array("success"=> false,  "message" => $e->getMessage()." ".$e->getCode()  );
            echo json_encode($a_result);
        }
    }

    public function getContracts($rut)
    {
        try
        {
            $json_contracts = $this->CI->PaymentsInvoicesServices->getAllContractsByRut($rut);
            if (is_bool($json_contracts))
            {
                $this->msgError = $this->CI->PaymentsInvoicesServices->getLastError();
                return false;
            }
            $decoded = json_decode($json_contracts, true);

            $response;

            if (!is_array($decoded))
            {
                $this->msgError = "La información recibida no es de tipo JSON válido.";
                return false;
            }

            if (!$decoded["result"])
            {
                $this->msgError = $decoded["error"]["message"];
                return false;
            }

            // echo "<pre>";
            // print_r($decoded);
            // echo "</pre>";

            $a_response = $decoded["response"]["lines"];



            $activeContracts = array();

            //Se validarán los datos
            foreach ($a_response as $resp)
            {
                if ($resp["customer"] == "" || $resp["status"] == "")
                {
                    $this->msgError = "Campos requeridos.";
                    return false;
                }

                if (!is_numeric($resp["contract_number"]) || !is_string($resp["customer"]) || !is_string($resp["status"]))
                {
                    $this->msgError = "Error de integridad de datos.";
                    return false;
                }

                if (intval($resp["contract_number"]) == 0)
                {
                    $this->msgError = "El número de contrato debe ser un campo numérico.";
                    return false;
                }

                //Se verifica y se busca a todos los contratos activos
                //if ($resp["status"] == "ACTIVO")
                //{
                    $array_temp = array(
                        "ID" => $resp["contract_number"],
                        "name" => $resp["contract_number"].":".$resp["customer_number"] .":". $resp["customer"] .":". $resp["status"]
                    );
                    array_push($activeContracts, $array_temp);
                //}
            }

            //Se verificará que la variable $activeContracts contenga algo
            if (empty($activeContracts))
            {
                $this->msgError = "El usuario no posee contratos.";
                return false;
            }
            else
            {
                $response = $activeContracts;
            }
            // Expone la respuesta
            return $response;
        }
        catch (\Exception $e)
        {
            $a_result = array("result"=> false,  "error" => array("code"=> $e->getCode(), "message" => $e->getMessage()));
            echo $a_result;
        }
    }




    public function getRuts($rut)
    {
        try
        {
            $json_Ruts =  $this->CI->GeneralServices->getOrganizationStatusbyRut($rut);
            if (is_bool($json_Ruts))
            {
                $this->msgError = $this->CI->PaymentsInvoicesServices->getLastError();
                return false;
            }
           
            if (!is_array($json_Ruts))
            {
                $this->msgError = "La información recibida no es de tipo JSON válido.";
                return false;
            }

            if (!$json_Ruts["result"])
            {
                $this->msgError = $json_Ruts["error"]["message"];
                return false;
            }

            // echo "<pre>";
            // print_r($decoded);
            // echo "</pre>";

            $a_response = $json_Ruts["response"]["lines"];



            $activeContracts = array();

            //Se validarán los datos
            foreach ($a_response as $resp)
            {
                if ($resp["customer"] == "" || $resp["status"] == "")
                {
                    $this->msgError = "Campos requeridos.";
                    return false;
                }

                if (!is_numeric($resp["contract_number"]) || !is_string($resp["customer"]) || !is_string($resp["status"]))
                {
                    $this->msgError = "Error de integridad de datos.";
                    return false;
                }

                if (intval($resp["contract_number"]) == 0)
                {
                    $this->msgError = "El número de contrato debe ser un campo numérico.";
                    return false;
                }

                //Se verifica y se busca a todos los contratos activos
                if ($resp["status"] == "ACTIVO")
                {
                    $array_temp = array(
                        "ID" => $resp["contract_number"],
                        "name" => $resp["contract_number"]."-".$resp["customer"]
                    );
                    array_push($activeContracts, $array_temp);
                }
            }

            //Se verificará que la variable $activeContracts contenga algo
            if (empty($activeContracts))
            {
                $this->msgError = "El usuario no posee contratos.";
                return false;
            }
            else
            {
                $response = $activeContracts;
            }
            // Expone la respuesta
            return $response;
        }
        catch (\Exception $e)
        {
            $a_result = array("result"=> false,  "error" => array("code"=> $e->getCode(), "message" => $e->getMessage()));
            echo $a_result;
        }
    }

    public function getLastError()
    {
        return $this->msgError;
    }
}
