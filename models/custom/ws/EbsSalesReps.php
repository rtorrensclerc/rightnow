<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_2 as RNCPHP;

class EbsSalesReps extends \RightNow\Models\Base
{
    private $error = '';

    function __construct()
    {
        parent::__construct();
        //\RightNow\Libraries\AbuseDetection::check();
    }

    public function modifySalesReps($sales_rep_id, $name)
        {
          if (!empty($sales_rep_id) and !empty($name))
          {
            try
            {



              $Objsales_rep = RNCPHP\Comercial\Ejecutivo::first("sales_rep_id = '{$sales_rep_id}'");
              if (empty($Objsales_rep))
              {
                return $this->createSalesRep($sales_rep_id, $name);
              }
              else if (is_object($Objsales_rep) and ($Objsales_rep instanceof RNCPHP\Comercial\Ejecutivo))
              {
                return $this->updateSalesRep($Objsales_rep,$sales_rep_id, $name );
              }

            }
            catch (RNCPHP\ConnectAPIError $err)
            {
              $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
              return false;
            }

          }
          else
          {
              $this->error = "sales_rep_id o nombre vienen vacios";
              return false;
          }
        }


        private function createSalesRep($sales_rep_id, $name )
         {
           try
           {
               $SalesRep = new RNCPHP\Comercial\Ejecutivo();
               $SalesRep->sales_rep_id   = $sales_rep_id;
               $SalesRep->name           = $name;
               $SalesRep->DisplayOrder=1;
               $SalesRep->Save();

           }
           catch ( RNCPHP\ConnectAPIError $err )
           {
               $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
               return false;
           }
           return true;
         }



         private function updateSalesRep($Objsales_rep, $sales_rep_id, $name)
         {
           try
           {
               if (!($Objsales_rep instanceof RNCPHP\Comercial\Ejecutivo))
               {
                 $this->error = "No es un Objeto Vendedor";
                 return false;
               }

               $SalesRep = $Objsales_rep;
               $SalesRep->sales_rep_id   = $sales_rep_id;
               $SalesRep->name           = $name;
               $SalesRep->Save(RNCPHP\RNObject::SuppressAll);
           }
           catch ( RNCPHP\ConnectAPIError $err )
           {
               $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
               return false;
           }
           return true;
         }


    public function getLastError()
    {
        return $this->error;
    }


}
