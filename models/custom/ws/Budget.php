<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_3 as RNCPHP;

class Budget extends \RightNow\Models\Base
{
    //public $error = '';
    public  $error          = array ('numberID' => null , 'message' => null);
    private $nro_referencia = '';

    function __construct()
    {
        parent::__construct();
        //\RightNow\Libraries\AbuseDetection::check();
    }

    public function getConsumoMes($IDtypeFlow)
    {
      try {
          $CI             = get_instance();
          $accountValues  = $CI->session->getSessionData('Account_loggedValues');
          //Parche Cookie
          $accountValues  = unserialize($_COOKIE['Account_loggedValues']);

          $idAccount      = $accountValues['ID'];

          $mes            = date("m");
          $agno           = date("Y");

          $budget         = RNCPHP\OP\Budget::first("Account = {$idAccount} and TypeFlow = {$IDtypeFlow} and Month.LookupName = '{$mes}' and Year.LookupName = '{$agno}'");
          //print_r($budget->Month->LookupName);


          if ($IDtypeFlow = 41)
            $incidents      = RNCPHP\Incident::find("AssignedTo.Account.ID = {$idAccount} and ClosedTime >= date_trunc( sysdate(), 'month' ) and StatusWithType.StatusType = 2 and (Disposition.ID = {$IDtypeFlow} or Disposition.ID = 42)");
          else
            $incidents      = RNCPHP\Incident::find("AssignedTo.Account.ID = {$idAccount} and ClosedTime >= date_trunc( sysdate(), 'month' ) and  Disposition.ID = {$IDtypeFlow} and StatusWithType.StatusType = 2");

          $total    = 0;
          $totalRefund   = 0;
          $totalReparation = 0;
          foreach ($incidents as $inc)
          {
            //echo $inc->CustomFields->c->total_cost_items."<br>";
            $total += $inc->CustomFields->c->total_cost_items;

            if ($inc->Disposition->ID == 41) //Servicio Técnico
              $totalReparation += $inc->CustomFields->c->total_cost_items;

            if ($inc->Disposition->ID == 42) //Devoluciones
              $totalRefund += $inc->CustomFields->c->total_cost_items;
          }

          $result                  = new \stdClass();
          $result->budget          = $budget;
          $result->total           = $total;
          $result->totalRefund     = $totalRefund;
          $result->totalReparation = $totalReparation;

          return $result;

      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        echo $this->error['message'] ;
        return false;
      }
    }

    public function getLastError()
    {
      return $this->error['message'];
    }

    public function getNumberError()
    {
      return $this->error['numberID'];
    }

}
