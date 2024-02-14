<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_2 as RNCPHP;

class EbsTransaction extends \RightNow\Models\Base
{   
    
    private $error = '';

    function __construct()
    {
        parent::__construct();
        
    }

    public function getTransactionNotConciliada()
    {
        try
        {
            $array_transaction = RNCPHP\TRA\Transaction2::find("Conciliado  = 0");
            if (is_array( $array_transaction))
                return  $array_transaction;
            else
                return false;
        }
        catch ( RNCPHP\ConnectAPIError $err ) 
        {
            $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }

    }
    
    private function modifyTransaction($InvoiceNumber,$rut,$Conciliado){
        
        if (!empty($InvoiceNumber) and !empty($rut))
        {    
            try {
                
                $ObjTra = RNCPHP\TRA\Transaction2::find("RutInvoice ='".$rut."' and InvoiceNumber='".$InvoiceNumber."'");
                if (is_object($ObjOrg[0]))
                {
                    return $this->updateTransaction($ObjTra[0], $InvoiceNumber, $rut);
                }
            }
            catch ( RNCPHP\ConnectAPIError $err )
            {
                $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
                return false;
            }
        }  
        return true;
    }

    private function updateTransaction($ObjTra,  $InvoiceNumber, $rut)
    {

        if (is_object($ObjTra))
        {
            try
            {
                
                $ObjTra->CustomFields->c->conciliado = 1;
                $ObjTra->Save();
            }
            catch ( RNCPHP\ConnectAPIError $err )
            {
                $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
                return false;
            }
            return true;
        }
        else
        {
            $this->error = "El objeto transaccion no es un objeto";
            return false;
        }
    }
    public function getLastError()
    {
        return $this->error;
    }


}
