<?php
namespace Custom\Models;

use RightNow\Connect\v1_3 as RNCPHP;

class Transaction extends \RightNow\Models\Base
{

    public $error = array();

    function __construct()
    {
        parent::__construct();
    }


    /******************** GET ***********************/

    //this function call the table Transaction by id.
    public function getTransaction($transaction_id)
    {
        try
        {
            $transaction = RNCPHP\TRA\Transaction2::fetch($transaction_id);


            if(!is_null($transaction))
            {
                return $transaction;
            }
            else
            {
                $this->error['message'] = "La transaccion no existe en la base de datos.";
                return false;
            }

        }
        catch (RNCPHP\ConnectAPIError $err )
        {
            $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }

    }

      /******************** SET CREATE ***********************/


    //this function set the transactión json and save the data in the data base.
    public function setBasicNewTransaction($transaction_c)
    {
       
      try
      {
        $transaction = new RNCPHP\TRA\Transaction2();
        switch ($transaction_c->responseCode)
        {
          case "0":
              //Pago correcto
              $transaction->Status = RNCPHP\TRA\Status::fetch(1);
              break;
          case NULL:
              //Pago no pudo ser procesado
              $transaction->Status = RNCPHP\TRA\Status::fetch(2); //ID;
              break;
          default:
              //Pago anulado
              $transaction->Status = RNCPHP\TRA\Status::fetch(3);
              break;
        }
        

        $transaction->AuthCode        = $transaction_c->authorizacionCode;
        
        $transaction->ShareNumber     = $transaction_c->shareNumber;
        
        $transaction->PaymentCodeType = $transaction_c->paymentTypeCode;
        
        $transaction->Amount          = $transaction_c->amount;
        
        $transaction->ResponseCode    = $transaction_c->responseCode;
        
        $transaction->InvoiceNumber   = $transaction_c->invoice_number;
        
        $transaction->JsonInfo        = $transaction_c->info_json;
        
        $transaction->CardNumber      = $transaction_c->cardNumber;
        
        $transaction->TransactionDate = $transaction_c->transactionDate;
        
        
        if(!empty($transaction_c->organization_rut))
        {
            $transaction->RutInvoice = $transaction_c->organization_rut;
            $organization = RNCPHP\Organization::first("Organization.CustomFields.c.rut ='".$transaction_c->organization_rut."'");
            if ($organization instanceof RNCPHP\Organization)
              $transaction->Organization = $organization;
        }

        if(!empty($transaction_c->contact_id))
        {
            $contact = RNCPHP\Contact::fetch($transaction_c->contact_id);

            if($contact instanceof RNCPHP\Contact)
              $transaction->Contact = $contact;
        }




  			$transaction->save();
  			return $transaction->ID;
  		}
      catch(RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Error: ".$err->getCode()." ".$err->getMessage();
        return false;
  		}
  	}


    //this function set the transactión json and save the data in the data base.
    public function setNewTransaction($transaction_c,$organization)
    {

          try
          {
             $transaction = new RNCPHP\TRA\Transaction2();

              switch ($transaction_c->responseCode)
              {

                   case "0":
                      //Pago correcto
                      $transaction->Status = RNCPHP\TRA\Status::fetch(1);
                      break;

                  case NULL:
                      //Pago no pudo ser procesado
                      $transaction->Status = RNCPHP\TRA\Status::fetch(2); //ID;
                      break;

                  default:
                      //Pago anulado
                      $transaction->Status = RNCPHP\TRA\Status::fetch(3);
                      break;

              }

              $transaction->AuthCode = $transaction_c->authorizacionCode;
              $transaction->ShareNumber = $transaction_c->shareNumber;
              $transaction->PaymentCodeType = $transaction_c->paymentTypeCode;
              $transaction->Amount = $transaction_c->amount;
              $transaction->RutInvoice = $transaction_c->invoice_rut;
              $transaction->ResponseCode = $transaction_c->responseCode;
              $transaction->InvoiceNumber = $transaction_c->invoice_number;
              $transaction->JsonInfo = $transaction_c->info_json;
              $transaction->Organization = $organization;

        			$transaction->save();
        			return $transaction->ID;

      		}
          catch(RNCPHP\ConnectAPIError $err )
          {
              $this->error['message']  = "Error: ".$err->getCode()." ".$err->getMessage();
              return false;
      		}
  	}

    public function getLastError()
    {
        return $this->error['message'];
    }

    public function test()
    {
        echo "HI";
    }
}
