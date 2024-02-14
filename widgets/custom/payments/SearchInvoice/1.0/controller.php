<?php
namespace Custom\Widgets\payments;

class SearchInvoice extends \RightNow\Libraries\Widget\Base {
    public $contactId;
    public $msgError;

    function __construct($attrs) {
        parent::__construct($attrs);
        $this->setAjaxHandlers(array(
            'getInvoice_ajax_endpoint' => array(
                'method'    => 'handle_getInvoice_ajax_endpoint',
                'clickstream' => 'getInvoice_ajax_endpoint'
            ),
            'getInvoiceDetail_ajax_endpoint' => array(
                'method'      => 'handle_getInvoiceDetail_ajax_endpoint',
                'clickstream' => 'getInvoiceDetail_ajax_endpoint'
            ),
            'getInvoiceDTE_ajax_endpoint' => array(
                'method'      => 'handle_getInvoiceDTE_ajax_endpoint',
                'clickstream' => 'getInvoiceDTE_ajax_endpoint'
            )
        ));
       
        $this->CI->load->model('custom/Contact');
        $this->CI->load->model('custom/PaymentsInvoicesServices');
        $this->contactId  = $this->CI->session->getProfile()->c_id->value;

    }

    function getData() {
        $contracts = array();
        $c_id = $this->CI->session->getProfile()->c_id->value;
        $user = $this->CI->Contact->getContactById($c_id);
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
                $contracts=$a_contracts;
                $this->data['js']['list']['contracts'] = $contracts;
            }
        }
        return parent::getData();

    }

    /**
     * Handles the handle_getInvoice_ajax_endpoint AJAX request
     * @param array $params Get / Post parameters
     */
    function handle_getInvoice_ajax_endpoint($params) {
        // Perform AJAX-handling here...
        
        
        $contact = $this->CI->Contact->get($this->contactId);
        $rut = $contact->Organization->CustomFields->c->rut;
        $data = json_decode($params['data'], TRUE);
        
        $invoice_number   = $data["invoice_number"];
     
        $invoice_rut=$data["invoice_rut"];
        $invoice_contrato= $data["invoice_contrato"];
        $invoice_from= $data["invoice_from"];
        $invoice_to= $data["invoice_to"];
       
        // DEBEMOS LLAMAR A UN PROCEIMIENTO QUE SOLO BUSQUE la Factura

        $invoice=$this->CI->PaymentsInvoicesServices->SearchInvoice($rut,$invoice_number,$invoice_rut,$invoice_contrato,$invoice_from,$invoice_to);
    

        if($invoice->response->lines==null)
        {
            $params['invoice_data']=$invoice->response->lines;
        }
        else
        {
            if(!array_key_exists(0, $invoice->response->lines))
            {
                $params['invoice_data'][]=$invoice->response->lines;
            }
            else
            {
                $params['invoice_data']=$invoice->response->lines;
            }
        }
        $params['success']=$invoice->result;
        $params['test']=$invoice;
        echo json_encode($params);
    }
     /**
     * Handles the handle_getInvoice_ajax_endpoint AJAX request
     * @param array $params Get / Post parameters
     */
    function handle_getInvoiceDetail_ajax_endpoint($params) {
        // Perform AJAX-handling here...
        
        //$contact = $this->CI->Contact->get($this->contactId);
        //$rut = $contact->Organization->CustomFields->c->rut;
        $data = json_decode($params['data'], TRUE);
        
        $invoice_number   = $data["invoice_number"];
       
        // DEBEMOS LLAMAR A UN PROCEIMIENTO QUE  BUSQUE  Detalle de la Factura
        $invoice_detail=$this->CI->PaymentsInvoicesServices->SearchInvoiceDetail($data,$this->contactId);
        //echo json_encode($invoice_detail);
        if(!array_key_exists(0, $invoice_detail->response->lines))
        {
            $params['invoice_data'][]=$invoice_detail->response->lines;
        }
        else
        {
            $params['invoice_data']=$invoice_detail->response->lines;
        }
        foreach ($params['invoice_data'] as $invoice)
        {
            $invoice->divisa=$invoice_detail->response->header->divisa;
            $invoice->exchange_rate=$invoice_detail->response->header->exchange_rate;
        }

        
        $params['success']=$invoice_detail->result;
       
        echo json_encode($params);
    }


 /**
     * Handles the handle_getInvoiceDTE_ajax_endpoint AJAX request
     * @param array $params Get / Post parameters
     */
    function handle_getInvoiceDTE_ajax_endpoint($params) {
        $data = json_decode($params['data'], TRUE);
        
        $trx_number   = $data["invoice_number"];
        $dte_detail = $this->CI->PaymentsInvoicesServices->getDTEInvoice($trx_number);
        $datos=json_decode($dte_detail);
       
        $params['success']=true;
        $params['url_dte']=$datos->estado_emitidoResponse->url_dte;
        echo json_encode($params);
    }

    public function getContracts($rut)
    {
        try
        {
            $json_contracts = $this->CI->PaymentsInvoicesServices->getAllContractsByRutH($rut);

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
                if ($resp["status"] == "ACTIVO" || $resp["status"] == "ACTIVE")
                {
                    $array_temp = array(
                        "ID" => $resp["contract_number"],
                        "name" => $resp["contract_number"]."-".$resp["customer"] 
                    );
                    array_push($activeContracts, $array_temp);

                }
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
};