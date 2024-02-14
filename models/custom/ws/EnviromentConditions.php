<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_2 as RNCPHP;

class EnviromentConditions extends \RightNow\Models\Base
{
    //public $error = '';
    public  $error          = array ('numberID' => null , 'message' => null);
    private $nro_referencia = '';

    function __construct()
    {
        parent::__construct();
        //\RightNow\Libraries\AbuseDetection::check();
    }

    public function createEnviromentConditions($Conditions,$nota)
    {


      if($EnviromentConditions = $this->getObjectEnviromentConditions($Conditions->Incident))
      {
          return $EnviromentConditions;
      }
      try
      {
            //return $Conditions;            LogMessage("createEnviromentConditions  " . json_encode($Conditions));
            $Enviroment = new RNCPHP\OP\EnviromentConditions();
            //$Enviroment->Notes= $Conditions['Notes'];
            $Enviroment->Incident= $Conditions->Incident;
            if(strlen($nota)>1)
            {
              $Enviroment->Notes = new RNCPHP\NoteArray();
              $Enviroment->Notes[0] = new RNCPHP\Note();
              $Enviroment->Notes[0]->Text =$nota;
            }
            if(strlen($Conditions->Description)>=1)
            {
              $Enviroment->Description= $Conditions->Description;
            }
            else {
              $Enviroment->Description="-descripcion-";
            }
            if(strlen($Conditions->Solution)>=1)
            {
              $Enviroment->Solution= $Conditions->Solution;
            }
            else {
              $Enviroment->Solution='-solucion-';
            }
            if(strlen($Conditions->IpNumber)>=1)
            {
              $Enviroment->IpNumber= $Conditions->IpNumber;
            }
            else {
              $Enviroment->IpNumber="-Numero IP-";
            }

            $Enviroment->Copy= $Conditions->Copy;
            $Enviroment->Scan= $Conditions->Scan;
            $Enviroment->Printer= $Conditions->Printer;
            $Enviroment->Fax= $Conditions->Fax;
            $Enviroment->Temperture= $Conditions->Temperture;
            $Enviroment->IssueCausa= $Conditions->IssueCausa;
            $Enviroment->ElectricalCondition= $Conditions->ElectricalCondition;
            $Enviroment->EnviromentCondit= $Conditions->EnviromentCondit;
            $Enviroment->PrintFlow= $Conditions->PrintFlow;
            $Enviroment->OperatingSystem= $Conditions->OperatingSystem;

            $Enviroment->Area= $Conditions->Area;
            $Enviroment->CostCenter= $Conditions->CostCenter;
            $Enviroment->Reception_Name= $Conditions->Reception_Name;
            $Enviroment->NoDataMobile=$Conditions->NoDataMobile;
            $Enviroment->VisitNumber= 1;
            if(strlen($Conditions->AlternativeEmails)>=1)
            {
              $Enviroment->AlternativeEmails= $Conditions->AlternativeEmails;
            }
            else {
              $Enviroment->AlternativeEmails="-Sin Correos-";
            }

            $Enviroment->Save(RNCPHP\RNObject::SuppressExternalEvents);
            return $Enviroment;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {

        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        RNCPHP\ConnectAPI::rollback();
        return false;
      }

    }

    public function updateEnviromentConditions($Conditions,$nota)
    {


       if(!($EnviromentConditions = $this->getObjectEnviromentConditions($Conditions->Incident->ID)))
       {
           return $this->createEnviromentConditions($Conditions,$nota);
       }
       try {

       //$EnviromentConditions->Notes= $Conditions->Notes;

       if(strlen($nota)>1)
       {
         $EnviromentConditions->Notes = new RNCPHP\NoteArray();
         $EnviromentConditions->Notes[0] = new RNCPHP\Note();
         $EnviromentConditions->Notes[0]->Text =$nota;
       }
       if(strlen($Conditions->Description)>=1)
       {
         $EnviromentConditions->Description= $Conditions->Description;
       }
       else {
         $EnviromentConditions->Description="-descripcion-";
       }
       if(strlen($Conditions->Solution)>=1)
       {
         $EnviromentConditions->Solution= $Conditions->Solution;
       }
       else {
         $EnviromentConditions->Solution='-solucion-';
       }
       if(strlen($Conditions->IpNumber)>=1)
       {
         $EnviromentConditions->IpNumber= $Conditions->IpNumber;
       }
       else {
         $EnviromentConditions->IpNumber="-Numero IP-";
       }
       $EnviromentConditions->Copy= $Conditions->Copy;
       $EnviromentConditions->Scan= $Conditions->Scan;
       $EnviromentConditions->Printer= $Conditions->Printer;
       $EnviromentConditions->Fax= $Conditions->Fax;
       $EnviromentConditions->Temperture= $Conditions->Temperture;
       $EnviromentConditions->IssueCausa= $Conditions->IssueCausa;
       $EnviromentConditions->ElectricalCondition= $Conditions->ElectricalCondition;
       $EnviromentConditions->EnviromentCondit= $Conditions->EnviromentCondit;

       $EnviromentConditions->Area= $Conditions->Area;
       $EnviromentConditions->CostCenter= $Conditions->CostCenter;
       $EnviromentConditions->Reception_Name= $Conditions->Reception_Name;
       $EnviromentConditions->NoDataMobile = $Conditions->NoDataMobile;
       $EnviromentConditions->VisitNumber = $Conditions->VisitNumber;
       if(strlen($Conditions->AlternativeEmails)>=1)
       {
         $EnviromentConditions->AlternativeEmails= $Conditions->AlternativeEmails;
       }
       else {
         $EnviromentConditions->AlternativeEmails="-Sin Correos-";
       }

       if(strlen($Conditions->PrintFlow)>=1)
       {
             $EnviromentConditions->PrintFlow= $Conditions->PrintFlow;
       }
       else {
          $EnviromentConditions->PrintFlow='Sin Valor';
       }
       $EnviromentConditions->OperatingSystem= $Conditions->OperatingSystem;

       $EnviromentConditions->Save(RNCPHP\RNObject::SuppressExternalEvents);
     } catch (Exception $e) {
       $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
       logMessage($this->error['message']);
     }
      if ($EnviromentConditions !== false)
      {
        try
        {
          return $EnviromentConditions->ID;
        }
        catch (RNCPHP\ConnectAPIError $err )
        {
          $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
          $this->error['numberID'] = 1;
          RNCPHP\ConnectAPI::rollback();
          return false;
        }

      }
      else
      {
        return false;
      }
    }


    public function getObjectEnviromentConditions($father_id)
    {
     
        try
        {
            $EnviromentConditions = RNCPHP\OP\EnviromentConditions::find("incident ='{$father_id}'");

            if (is_object($EnviromentConditions[0]))
            {
                return $EnviromentConditions[0];
            }
            else
            {

              $this->error['message']  = "No Hay Condiciones Ingresadas";
              $this->error['numberID'] = 2;
              return false;
            }
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
            $this->error['numberID'] = 1;
            return false;
        }
    }


}
