<?php
namespace Custom\Widgets\payments;

class Voucher extends \RightNow\Libraries\Widget\Base {

    //Varibale que almacena los errores surgidos
    public $msgError = "";
    public $contactId;
    function __construct($attrs) {
        parent::__construct($attrs);
        $this->CI->load->model('custom/Contact');
        $this->CI->load->model('custom/PaymentsInvoicesServices');
        $this->CI->load->model('custom/Transaction');
        $this->setAjaxHandlers(array(
            'default_ajax_endpoint' => array(
                'method'      => 'handle_default_ajax_endpoint',
                'clickstream' => 'custom_action',
            ),
        ));


        $this->contactId  = $this->CI->session->getProfile()->c_id->value;

    }

    function getData() {


        //Se obtiene el valor del parametro id desde la URL
        $idCode = getUrlParm('id');
        $this->data['js']['Contact']=$this->CI->Contact->getContactById($this->contactId );

        // Quitar codificación
        $lenghtDate  = 10;
        $qCaracteres = strlen ( $idCode );
        $id          = substr($idCode, -$qCaracteres, $qCaracteres-$lenghtDate);
        $date        = substr($idCode, $qCaracteres-$lenghtDate);


        $id_int = (int)$id;
        $transaction = $this->getTransaction($id_int);
        //$this->data['js']['Contact']->Organization->CustomFields->c->rut;
        $transaction->RutInvoice=$this->data['js']['Contact']->Organization->CustomFields->c->rut;
        $transaction->Organization=$this->data['js']['Contact']->Organization->ID;
        $transaction->save();
        $transaction = $this->getTransaction($id_int);

        // echo "Unix DB ".$transaction->TransactionDate."<br>";
        // echo "Unix URL ".$date."<br>";
        

        if ($transaction->TransactionDate  !=  $date)
        {
          echo "Código de voucher no valido ";
          return parent::getData();
        }

        if(is_bool($transaction))
        {
            $this->data['js']['voucher']['error'] = $this->CI->Transaction->getLastError();
        }
        else
        {

            $this->data['js']['voucher']['Date']            = date("d/m/Y", $transaction->TransactionDate);
            $this->data['js']['voucher']['Time']            = date("H:i:s", $transaction->TransactionDate);
            $this->data['js']['voucher']['ID']              = $transaction->ID;
            $this->data['js']['voucher']['AuthCode']        = $transaction->AuthCode;
            $this->data['js']['voucher']['ShareNumber']     = $transaction->ShareNumber;
            $this->data['js']['voucher']['PaymentCodeType'] = $this->getPaymentType($transaction->PaymentCodeType);
            $this->data['js']['voucher']['ResponseCode']    = $this->getTransactionStatus($transaction->ResponseCode);
            $this->data['js']['voucher']['Amount']          = $transaction->Amount;
            $this->data['js']['voucher']['InvoiceNumber']   = $transaction->InvoiceNumber;
            $this->data['js']['voucher']['RutInvoice']      = $transaction->RutInvoice;
            $this->data['js']['voucher']['JsonInfo']        = $transaction->JsonInfo;
            $this->data['js']['voucher']['Status']          = $transaction->Status;
            $this->data['js']['voucher']['Organization']    = $transaction->Organization;
        }


        $this->data['id'] = $id;

        return parent::getData();

    }

    /**
     * Handles the default_ajax_endpoint AJAX request
     * @param array $params Get / Post parameters
     */
    function handle_default_ajax_endpoint($params) {
        // Perform AJAX-handling here...
        // echo response
    }

    function getTransactionStatus($responseCode)
    {
        $status = array(
            "0" => "Transacción aprobada",
            "-1" => "Rechazo de transacción",
            "-2" => "Transacción debe reinventarse",
            "-3" => "Error en transacción",
            "-4" => "Rechazo de transacción",
            "-5" => "Rechazo por error de tasa",
            "-6" => "Exceda cupo máximo mensual",
            "-7" => "Excede límite diario por transacción",
            "-8" => "Rubro no autorizado"
        );
        $val;
        $resp_string = (string)$responseCode;

        return $status[$resp_string];

    }

    function getPaymentType($type)
    {
        $val;
        switch ($type)
        {
             case "VD":
                $val = "Venta débito";
                break;

            case "VN":
                $val = "Venta normal";
                break;

            case "VC":
                $val = "Venta en cuotas";
                break;

            case "SI":
                $val = "3 Cuotas sin interés";
                break;

            case "S2":
                $val = "2 Cuotas sin interés";
                break;

            case "NC":
                $val = "N Cuotas sin interés";
                break;

            default:
                $val = "Tipo de pago no identificado";
                break;

        }

        return $val;
    }

    function getTransaction($id)
    {
        try
        {
            $response = $this->CI->Transaction->getTransaction($id);
            return $response;

        }
        catch (\Exception $e)
        {
            $this->msgError = array("result"=> false,  "error" => array("code"=> $e->getCode(), "message" => $e->getMessage()));
            return false;
        }
    }
}
