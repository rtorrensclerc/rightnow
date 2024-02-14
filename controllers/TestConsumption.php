<?php
namespace Custom\Controllers;

class TestConsumption extends \RightNow\Controllers\Base
{
    public function __construct()
    {
        parent::__construct();
        $this->response = new \stdClass;
        $this->response->errors = array();
        $this->response->messages = array();
        $this->load->model('custom/Consumption');
        $this->load->model('custom/Contact');
        $this->load->model('custom/Organization');
        $this->load->model('custom/Contract');
        $this->load->model('custom/Invoice');
    }

    public function lastConsumptionsMonths($rut = "", $contract_number = "")
    {
        /*Para enviar parámetros por url es https://soportedimacoficl--tst1.custhelp.com/cc/Controller/Método/Parametro1/Parametro2*/
        try
        {
            //Se obtiene el id del usuario que haya iniciado sesión
            $c_id   = $this->session->getProfile()->c_id->value;

            $contact;
            $organization;

            //Se verifica si el número de contrato recibido por la url tiene algún dato.
            if($contract_number != "")
            {
                //Se valida que el contenido del número de contrato sea numérico.
                if(intval($contract_number) == 0)
                {
                    throw new \Exception('El número de contrato debe ser un valor numérico!', 8);
                }
            }

            //Se verifica que los parámetros contengan información
            if($rut != "" && $c_id == null)
            {
                //La función retornará un valor verdadero si el rut es válido, de lo contrario será falso.
                $valid_rut = $this->valida_rut($rut);

                if(!$valid_rut)
                {
                    throw new \Exception('Error de integridad de datos!', 6);
                }

                //Se busca el contacto mediante el rut
                $contact = $this->Contact->getContactByRut($rut);

                //Se verifica que el contacto haya sido encontrado.
                if(is_bool($contact))
                {
                    throw new \Exception('El contacto no existe en la base da datos!', 8);
                }
                else
                {
                    //Se verifica que el contacto pertenezca a una organización
                    if($contact->Organization == null)
                    {
                        throw new \Exception('El contacto no pertenece a alguna organización', 8);
                    }

                    //Se busca la organización a la que pertenece el contacto
                    $organization = $this->Organization->getOrganizationById($contact->Organization->ID);
                }

            }

            //Código que se ejecuta si un usuario se ha logueado
            if($c_id != null)
            {
                //Se busca al contaco mediante el ID obtenido de la sesión activa
                $contact = $this->Contact->getContactById($c_id);

                //Se verifica que el contacto pertenezca a una organizacion
                if($contact->Organization == null)
                {
                    throw new \Exception('El contacto no pertenece a alguna organización', 8);
                }
                //Se busca la organización a la que pertenece el contacto
                $organization = $this->Organization->getOrganizationById($contact->Organization->ID);
            }

            //Se ejecuta la función del modelo que consume el servicio de dimacofi
            $val = $this->Consumption->getLastConsumptionsMonths();

            //Se convierte el JSON recibido a un array del tipo ["clave"] => "valor"
            $val_decoded = json_decode($val, true);

            //Se valida que el contenido recibido tenga el formato JSON.
            if(!is_array($val_decoded))
            {
                throw new \Exception('Received content contained invalid JSON!', 4);
            }

            //Se verifica que el campo 'result' del JSON esperado sea verdadero.
            if(!$val_decoded["result"])
            {
                throw new \Exception('Error en la solicitud', 0);
            }

            //Se obtiene el contenido de la respuesta
            $a_info = $val_decoded["response"]["info"];

            //Se recorre el contenido de la respuesta
            foreach ($a_info as $info)
            {
                $a_temp = $info[0];
                foreach ($a_temp as $content)
                {
                    //Se verifica que los campos sean del tipo de dato correcto.
                    if(!is_string($content["counter_type"]) || !is_int($content["quantity"]) || !is_string($content["percentage"]))
                    {
                        throw new \Exception('Error de Integridad de datos', 6);
                    }
                    if($content["counter_type"] == "" || $content["percentage"] == "")
                    {
                        throw new \Exception('Campos requeridos!', 5);
                    }
                }
            }

            $a_info = array(
                $val_decoded["response"]["info"]
            );

            //Si todo esta bien, se va a responder
            $response          = new \stdClass();
            $response->success = ($val_decoded["result"])?true:false;
            $response->message = $val_decoded["response"]["message"];
            $response->detail  = $a_info;

            // Expone la respuesta
            echo json_encode($response);

        }
        catch(\Exception $e)
        {
            header('Content-Type: application/json');
            $a_result = array("result"=> false,  "error" => array("code"=> $e->getCode(), "message" => $e->getMessage()));
            echo json_encode($a_result);
        }

    }




    public function lastConsumptionsLines($rut = "", $contract_number = "", $invoice_number = "")
    {
        try
        {
            $c_id   = $this->session->getProfile()->c_id->value;
            $contact;
            $organization;


            if($contract_number != "")
            {
                //Se validará el número de contrato
                if(intval($contract_number) == 0)
                {
                    throw new \Exception('El número de contrato debe ser un valor numérico!', 8);
                }
            }

            if($invoice_number != "")
            {
                //Se validará el número de contrato
                if(intval($contract_number) == 0)
                {
                    throw new \Exception('El número de factura debe ser un valor numérico!', 8);
                }
            }

            //Se verifica que los parámetros contengan información
            if($rut != "" && $c_id == null)
            {
                //Se validará el rut del contacto
                $valid_rut = $this->valida_rut($rut);
                if(!$valid_rut)
                {
                    throw new \Exception('El rut es inválido!', 6);
                }
                $contact = $this->Contact->getContactByRut($rut);

                //Se verificará que el contacto haya sido encontrado;
                if(is_bool($contact))
                {
                    throw new \Exception('El contacto no existe en la base da datos!', 8);
                }
                else
                {
                    //Verificamos que el contacto pertenezca a una organizacion
                    if($contact->Organization == null)
                    {
                        throw new \Exception('El contacto no pertenece a alguna organización', 8);
                    }

                    //Buscar la organización a la que pertenece el contacto
                    $organization = $this->Organization->getOrganizationById($contact->Organization->ID);
                }

            }

            //Codigo que se ejecutará si c_id existe
            if($c_id != null)
            {
                $contact = $this->Contact->getContactById($c_id);

                //Verificamos que el contacto pertenezca a una organizacion
                if($contact->Organization == null)
                {
                    throw new \Exception('El contacto no pertenece a alguna organización', 8);
                }
                //Buscar la organización a la que pertenece el contacto
                $organization = $this->Organization->getOrganizationById($contact->Organization->ID);
            }


            $result = $this->Consumption->getLastConsumptionsLines($rut, $contract_number, $invoice_number);


            //Se validará que el contenido recibido tenga el formato JSON.
            $val_decoded = json_decode($result, true);
            if(!is_array($val_decoded))
            {
                throw new \Exception('Received content contained invalid JSON!', 4);
            }
            //Se verificará que el campo 'result' del JSON esperado sea verdadero.
            if(!$val_decoded["result"])
            {
                throw new \Exception('Error en la solicitud', 0);
            }

            //Se verificarán los datos del header
            $a_headers = $val_decoded["response"]["header"];

            if(!is_int($a_headers["quantity_hh"]) || !is_string($a_headers["divisa"]) || !is_float($a_headers["exchange_rate"]) ||
              !is_int($a_headers["amount_clp"]) || !is_int($a_headers["amount"]) || !is_int($a_headers["fixed_amount"]) ||
              !is_int($a_headers["quantity_bn"]) || !is_int($a_headers["quantity_color"]) || !is_int($a_headers["minimun_bn"]) ||
              !is_int($a_headers["minimun_color"])
            )
            {
                throw new \Exception('Error de Integridad de datos', 6);
            }
            //Se termino de validar el header


            //Se validarán las lineas
            $a_lines = $val_decoded["response"]["lines"];
            foreach($a_lines as $line)
            {
                if(!is_string($line["hh"]) || !is_string($line["serie"]) || !is_string($line["model"]) ||
                  !is_int($line["fixed_amount"]) || !is_int($line["last_read"]) || !is_string($line["last_date"]) ||
                  !is_string($line["actual_date"]) || !is_int($line["actual_read"]) || !is_int($line["credit"]) ||
                  !is_string($line["counter_type"]) || !is_int($line["real_quantity"]) || !is_int($line["billed_quantity"]) ||
                  !is_float($line["rate"]) || !is_int($line["amount"]) || !is_string($line["address"])
                )
                {
                    throw new \Exception('Error de Integridad de datos', 6);
                }

            }

            //Si todo esta bien, se va a responder
            $response          = new \stdClass();
            $response->success = ($val_decoded["result"])?true:false;
            $response->message = $val_decoded["response"]["message"];
            $response->detail  = new \stdClass();
            $response->detail->header  = $val_decoded["response"]["header"];
            $response->detail->lines = $val_decoded["response"]["lines"];

            // Expone la respuesta
            echo json_encode($response);

        }
        catch(\Exception $e)
        {
            header('Content-Type: application/json');
            $a_result = array("result"=> false,  "error" => array("code"=> $e->getCode(), "message" => $e->getMessage()));
            echo json_encode($a_result);
        }

    }

    public function getContracts($rut = "1-9")
    {
        try
        {
            $json_contracts = $this->Contract->getAllContractsByRut($rut);
            $decoded = json_decode($json_contracts, true);

            if(is_bool($json_contracts) || !is_array($decoded))
            {
                throw new \Exception('La información recibida no es de tipo JSON válido', 4);
            }

            $a_response = $decoded["response"]["lines"];

            $activeContracts = array();

            //Se validarán los datos
            foreach($a_response as $resp)
            {
                if($resp["customer"] == "" || $resp["status"] == "")
                {
                    throw new \Exception('Campos requeridos', 5);
                }

                if(!is_int($resp["contract_number"]) || !is_string($resp["customer"]) || !is_string($resp["status"]))
                {
                    throw new \Exception('Error de Integridad de datos', 6);
                }

                if(intval($resp["contract_number"]) == 0)
                {
                    throw new \Exception('El número de contrato debe ser un valor numérico!', 8);
                }

                //Se verifica y se busca a todos los contratos activos
                if($resp["status"] == "ACTIVO")
                {
                    $array_temp = array(
                        "ID" => $resp["contract_number"],
                        "customer" => $resp["customer"]
                    );
                    array_push($activeContracts, $array_temp);
                }

            }

            //Se verificará que la variable $activeContracts contenga algo
            if(empty($activeContracts))
            {
                throw new \Exception('El usuario con el rut xxxxx-x no posee contratos activos', 0);
            }

            //Si todo esta bien, se va a responder
            $response          = new \stdClass();
            $response->success = ($decoded["result"])?true:false;
            $response->message = $decoded["response"]["message"];
            $response->detail  = $activeContracts;
            // Expone la respuesta
            echo json_encode($response);
        }
        catch(\Exception $e)
        {
            header('Content-Type: application/json');
            $a_result = array("result"=> false,  "error" => array("code"=> $e->getCode(), "message" => $e->getMessage()));
            echo json_encode($a_result);
        }
    }



    public function getLastSixInvoices()
    {
        try
        {
            $json_invoices = $this->Invoice->getLastSixInvoices();
            $decoded = json_decode($json_invoices, true);

            if(!is_array($decoded))
            {
                throw new \Exception('La información recibida no es de tipo JSON válido', 4);
            }

            $a_payments = $decoded["response"]["last_payments"];
            $a_invoices = $decoded["response"]["last_invoices"];


            foreach($a_payments as $payment)
            {
                if(!is_string($payment["pay_number"]) || !is_int($payment["pay_amount"]))
                {
                    throw new \Exception('Error de Integridad de datos', 6);
                }
                if(intval($payment["pay_number"]) == 0)
                {
                    throw new \Exception('El número de pago debe ser un valor numérico!', 8);
                }
                if(intval($payment["pay_amount"]) == 0)
                {
                    throw new \Exception('El monto de pago debe ser un valor numérico!', 8);
                }

                foreach ($payment["pay_invoices"] as $pay_invoice)
                {
                    if(!is_string($pay_invoice["trx_number"]) || !is_int($pay_invoice["trx_amount"]))
                    {
                        throw new \Exception('Error de Integridad de datos', 6);
                    }
                    if(intval($pay_invoice["trx_number"]) == 0)
                    {
                        throw new \Exception('El número de transacción debe ser un valor numérico!', 8);
                    }
                }

            }

            $payment_invoice = array(
                "last_payments" => $a_payments,
                "last_invoices" => $a_invoices
            );

            //Si todo esta bien, se va a responder
            $response          = new \stdClass();
            $response->success = ($decoded["result"])?true:false;
            $response->message = $decoded["response"]["message"];
            $response->detail  = $payment_invoice;
            // Expone la respuesta
            echo json_encode($response);

        }
        catch(\Exception $e)
        {
            header('Content-Type: application/json');
            $a_result = array("result"=> false,  "error" => array("code"=> $e->getCode(), "message" => $e->getMessage()));
            echo json_encode($a_result);
        }
    }


    private function valida_rut($rut)
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
}
