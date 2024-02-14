<?php
namespace Custom\Widgets\payments;

class BillingAndPayments extends \RightNow\Libraries\Widget\Base
{
    // Varibale que almacena los errores surgidos
    public $msgError = "";

    public function __construct($attrs)
    {
        parent::__construct($attrs);

        $this->setAjaxHandlers(array(
            'getConsumptionLastSixMonths' => array(
                'method'      => 'handle_getConsumptionLastSixMonths',
                'clickstream' => 'custom_action',
            ),
            'getDetailInvoices' => array(
                'method'      => 'handle_getDetailInvoices',
                'clickstream' => 'custom_action',
            ),
            'getLastConsumptionsLines' => array(
                  'method'      => 'handle_getLastConsumptionsLines',
                  'clickstream' => 'custom_action',
              ),
            'getLastSixInvoices' => array(
                'method'      => 'handle_getLastSixInvoices',
                'clickstream' => 'custom_action',
            )
        ));
        // $this->setAjaxHandlers(array(
        //     'getLastConsumptionsLines' => array(
        //         'method'      => 'handle_getLastConsumptionsLines',
        //         'clickstream' => 'custom_action',
        //     ),
        // ));

        // $this->setAjaxHandlers(array(
        //     'getLastSixInvoices' => array(
        //         'method'      => 'handle_getLastSixInvoices',
        //         'clickstream' => 'custom_action',
        //     ),
        // ));

        // Se cargan los modelos
        $this->CI->load->model('custom/PaymentsInvoicesServices');
        $this->CI->load->model('custom/Contact');
    }

    public function getData()
    {
        $contracts = array();
        $error = "";
        $rut;
        $a_contracts;

        // Se verifica de que haya una session activa
        if (\RightNow\Utils\Framework::isLoggedIn() == false)
        {
            $error = 'El usuario debe iniciar sesión';
        }
        else
        {
            // Se busca al usuario, mediante su id
            $c_id = $this->CI->session->getProfile()->c_id->value;
            $user = $this->CI->Contact->getContactById($c_id);

            // Se verifica que el usuario pertenezca a una organización
            if (is_null($user->Organization))
            {
                $error = "El usuario no pertenece a una organización";
            }
            else
            {
                $organization = $user->Organization;
                // var_dump($organization->CustomFields->c->rut);
                $rut = $organization->CustomFields->c->rut;

                // carga lista de contratos
                $a_contracts = $this->getContracts($rut);

                // Verificamos que contenga contratos
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
        //     "rut" => "",
        //     "contract_number" => 32023,
        //     "invoice_number" => 266411
        // );
        // $json_encoded = json_encode($json);
        //
        // $a_data = array(
        //     "data" => $json_encoded
        // );
        // $this->handle_getLastConsumptionsLines($a_data);



        $this->data['js']['list']['contracts_error'] = $error;
        $this->data['js']['list']['contracts'] = $contracts; // array de ID, name de contratos

        return parent::getData();
    }


    /**
     * Handles the getLastSixInvoices AJAX request
     * @param array $params Get / Post parameters
     */
    public function handle_getLastSixInvoices($params)
    {
        try
        {
            $rut             = $params["rut"];
            $contract_number = $params["contract_number"];

            // Se verifica si el rut contiene algo.
            if (empty($rut))
            {
                // Si el campo rut esta vacio, se obtiene el id del usuario logueado.
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

                // Se obtiene la organizacion del usuario.
                $organization = $user->Organization;

                // Se obtiene el rut de la organizacion.
                $rut = $organization->CustomFields->c->rut;
            }

            //TODO: eliminar esto mas adelante
            // $rut = "65045929-6";
            // $contract_number = 32023;

            // Se llama al método del modelo que consume el servicio y lo obtenido se guarda en la variable $json_invoices.
            $json_invoices = $this->CI->PaymentsInvoicesServices->getLastSixInvoices($rut, $contract_number);

            // Si el valor retornado es un booleano, se produjo un error.
            if (is_bool($json_invoices))
            {
                // Se guarda el mensaje del error.
                $msgError = $this->CI->PaymentsInvoicesServices->getLastError();
                throw new \Exception($msgError);
            }

            // Se convierte el json en un array con el formato clave=>valor.
            $decoded = json_decode($json_invoices, true);

            // Se verifica que $decoded sea un array.
            if (!is_array($decoded))
            {
                throw new \Exception('La información recibida no es de tipo JSON válido', 4);
            }

            if( !$decoded["result"] )
            {
                throw new \Exception("Respuesta fallida ".$decoded["response"]["message"], 4);
            }

            // Se capturan los ultimos pagos.
            //$a_payments = $decoded["response"]["last_payments"];
           
            
            if($decoded["response"]["last_payments"]["amount_remaining"]==null)
            {
                $a_payments = $decoded["response"]["last_payments"];
            }
            else
            {
              $a_payments[] = $decoded["response"]["last_payments"];
            }


            // Se capturan las ultimas facturas.
            
            if($decoded["response"]["last_invoices"]["amount_remaining"]==null)
            {
                $a_invoices = $decoded["response"]["last_invoices"];
            }
            else
            {
              $a_invoices[] = $decoded["response"]["last_invoices"];
            }


            // Se recorre el array de pagos y se verifica que el tipo de dato sea correcto.
            foreach ($a_payments as $payment)
            {
                if (!is_numeric($payment["pay_number"]) || !is_numeric($payment["pay_amount"]) || !is_string($payment["pay_date"]))
                {
                    throw new \Exception('Error de Integridad de datos', 6);
                }
                // if (intval($payment["pay_number"]) == 0)
                // {
                //     throw new \Exception('El número de pago debe ser un valor numérico!', 8);
                // }
                // if (intval($payment["pay_amount"]) == 0)
                // {
                //     throw new \Exception('El monto de pago debe ser un valor numérico!', 8);
                // }

                // foreach ($payment["pay_invoices"] as $pay_invoice)
                // {
                //     if (!is_string($pay_invoice["trx_number"]) || !is_int($pay_invoice["trx_amount"]))
                //     {
                //         throw new \Exception('Error de Integridad de datos', 6);
                //     }
                //     if (intval($pay_invoice["trx_number"]) == 0)
                //     {
                //         throw new \Exception('El número de transacción debe ser un valor numérico!', 8);
                //     }
                // }
            }

            // Se recorre el array de facturas y se verifica que el tipo de dato sea correcto.
            foreach ($a_invoices as $invoice)
            {
                if( !is_numeric($invoice["amount_remaining"]) || !is_numeric($invoice["trx_amount"]) || !is_numeric($invoice["trx_number"]) || !is_string($invoice["due_date"]) || !is_string($invoice["trx_date"]) )
                {
                    throw new \Exception('Error de Integridad de datos', 6);
                }
            }


            // Se crea un arreglo para guardar los pagos y las facturas.
            $payment_invoice = array(
                "last_payments" => $a_payments,
                "last_invoices" => $a_invoices
            );

            // Se crea un objeto que almacene información con el formato de respuesta.
            $response          = new \stdClass();
            $response->success = ($decoded["result"])?true:false;
            $response->message = $decoded["response"]["message"];
            //$response->detail  = $payment_invoice;
            $response->lastPayments = $a_payments;
            $response->lastInvoices = $a_invoices;

            header('Content-Type: application/json');
            // Expone la respuesta.
            echo json_encode($response);
        }
        catch (\Exception $e)
        {
            header('Content-Type: application/json');
            $a_result = array("success"=> false,  "message" => $e->getMessage()." ".$e->getCode()  );
            echo json_encode($a_result);
        }
    }


    /**
     * Handles the getDetailInvoices AJAX request
     *
     * @param array $params Get / Post parameters
     */
    public function handle_getDetailInvoices($params)
    {
        try
        {
            $rut             = $params["rut"];
            $contract_number = $params["contract_number"];

            // Se verifica si el rut contiene algo.
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

                // Se obtiene la organizacion del usuario.
                $organization = $user->Organization;
                // Se obtiene el rut de la organizacion.
                $rut = $organization->CustomFields->c->rut;
            }

            // Se llama al método del modelo que consume el servicio y lo obtenido se guarda en la variable $jsonDetails.
            $jsonDetails = $this->CI->PaymentsInvoicesServices->getDetailInvoices($rut, $contract_number);

            // Se verifica si el json recibido es un booleano.
            if (is_bool($jsonDetails))
            {
                throw new \Exception($this->CI->PaymentsInvoicesServices->getLastError(), 1);
            }

            // Se crea un arreglo asociativo del tipo "clave"=>"valor".
            $decoded = json_decode($jsonDetails, true);

            // Se verifica que el contenido sea un arreglo.
            if (!is_array($decoded))
            {
                throw new \Exception('La información recibida no es de tipo JSON válido', 4);
            }

            if($decoded["Info"] == NULL)
            {
                throw new \Exception("No se pudo obtener el detalle de las facturas para el contrato seleccionado.", 1);
            }

            $a_result = array();
            $a_contenedor = array();

            foreach ($decoded["Info"]["Data"] as $key => $element) 
            {
                $a_period = explode("/", $element["DUE_DATE"]);
                $a_tempBN = array(
                    "counter_type" => "Copias BN",
                    "quantity"     => 0,
                    "percentage"   => $element["BN"],
                    "invoice"      => $element["NRO_FACTURA"]
                );
                
                $a_contenedor[$a_period[1] . "-" . $a_period[0]][] = $a_tempBN;

                $a_tempColor = array(
                    "counter_type" => "Copias Color",
                    "quantity"     => 0,
                    "percentage"   => $element["COLOR"],
                    "invoice"      => $element["NRO_FACTURA"]
                );
                $a_contenedor[$a_period[1] . "-" . $a_period[0]][] = $a_tempColor;
            }

            foreach ($a_contenedor as $key => $contenedor) 
            {
                $a_temp = array(
                    "period" => $key,
                    "data"   => $contenedor,
                );
                $a_result[] = $a_temp;
            }
            
            $response = array(
                "success" => TRUE,
                "message" => "Detalles obtenidos correctamente.",
                "detail"  => $a_result
            );

            //echo json_encode($response->detail);
            header('Content-Type: application/json');
            // Expone la respuesta
            echo json_encode($response);
        }
        catch (\Exception $e)
        {
            header('Content-Type: application/json');
            $code = $e->getCode();
            $a_result = array("success"=> false,  "message" => $e->getMessage() . (($code)?' ' . $e->getCode():''));
            $encoded = json_encode($a_result);
            echo $encoded;
        }
    }
    
    /**
     * Handles the getConsumptionLastSixMonths AJAX request
     *
     * @param array $params Get / Post parameters
     */
    public function handle_getConsumptionLastSixMonths($params)
    {
        try
        {
            $rut             = $params["rut"];
            $contract_number = $params["contract_number"];

            // Se verifica si el rut contiene algo.
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

                // Se obtiene la organizacion del usuario.
                $organization = $user->Organization;
                // Se obtiene el rut de la organizacion.
                $rut = $organization->CustomFields->c->rut;
            }
            /* echo "(".$rut .")<br>";
            echo "(".$contract_number.")";
            exit; */

            // Se llama al método del modelo que consume el servicio y lo obtenido se guarda en la variable $json_consumptions.
            // echo "RUT ". $rut." ContractNumber ". $contract_number;
            $json_consumptions = $this->CI->PaymentsInvoicesServices->getLastConsumptionsMonths($rut, $contract_number);

            

            // Se verifica si el json recibido es un booleano.
            if (is_bool($json_consumptions))
            {
                throw new \Exception($this->CI->PaymentsInvoicesServices->getLastError(), null);
            }

            // Se crea un arreglo asociativo del tipo "clave"=>"valor".
            $decoded = json_decode($json_consumptions, true);

            // Se verifica que el contenido sea un arreglo.
            if (!is_array($decoded))
            {
                throw new \Exception('La información recibida no es de tipo JSON válido', 4);
            }

            if($decoded["Meses"] === NULL)
            {
                throw new \Exception("No se pudo obtener el listado de consumos para el contrato seleccionado.", 1);
            }

            // Se valida si el resultado contiene un sólo elemento.
            if(is_array($decoded["Meses"]["Total"]) && array_key_exists("AMOUNT", $decoded["Meses"]["Total"]))
            {
                $temp[] = $decoded["Meses"]["Total"];
                unset($decoded["Meses"]["Total"]);
                $decoded["Meses"]["Total"] = $temp;
            }


            $response = array(
                "success" => TRUE,
                "message" => "Consumos obtenidos correctamente.",
                "detail"  => $decoded["Meses"]["Total"]
            );

            // Se crea un objeto que tenga el formato de respuesta.
            /* $response          = new \stdClass();
            $response->success = ($decoded["result"]) ? true : false;
            $response->message = $decoded["response"]["message"];
      
            if($decoded["response"]["info"]["period"] == null)
            {
                $response->detail  = $decoded["response"]["info"];
            }
            else
            {
                $response->detail[]  = $decoded["response"]["info"];
            } */

            //echo json_encode($response->detail);
            header('Content-Type: application/json');
            // Expone la respuesta
            echo json_encode($response);
        }
        catch (\Exception $e)
        {
            header('Content-Type: application/json');
            $code = $e->getCode();
            $a_result = array("success"=> false,  "message" => $e->getMessage() . (($code)?' ' . $e->getCode():''));
            $encoded = json_encode($a_result);
            echo $encoded;
        }
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


            /* var_dump($rut, $contract_number, $invoice_number);
            exit; */

            // Se llama al método del modelo que consume el servicio y lo obtenido se guarda en la variable $json_consumptionsLines.
            $json_consumptionsLines = $this->CI->PaymentsInvoicesServices->getLastConsumptionsLines($rut, $contract_number, $invoice_number);

            // echo "Rut " . $rut . " contract_number " . $contract_number . " " . $invoice_number . " <br>";

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

            if (!is_array($decoded))
            {
                $this->msgError = "La información recibida no es de tipo JSON válido.";
                return false;
            }

            if(!$decoded["result"])
            {
                $this->msgError = $decoded["response"]["message"];
                return false;
            }

            $a_response = $decoded["response"]["lines"];
            $activeContracts = array();

            // Se validarán los datos
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

                // Se verifica y se busca a todos los contratos activos
                //if ($resp["status"] == "ACTIVO" || $resp["status"] == "ACTIVE")
                //{
                    $array_temp = array(
                        "ID" => $resp["contract_number"],
                        "name" => $resp["contract_number"]."-".$resp["customer"] . "-" . $resp["status"]
                    );
                    array_push($activeContracts, $array_temp);

                //}
            }

            // Se verificará que la variable $activeContracts contenga algo
            if (empty($activeContracts))
            {
                $this->msgError = "El usuario no posee contratos.";
                return false;
            }
            else
            {
                return $activeContracts;
            }

        }
        catch (\Exception $e)
        {
            $this->msgError = $e->getMessage(). " ".$e->getCode();
            return false;
        }
    }

    public function getLastError()
    {
        return $this->msgError;
    }
}
