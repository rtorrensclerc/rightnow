<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_2 as RNCPHP;

class TicketModel extends \RightNow\Models\Base
{
    public $error = '';
    private $nro_referencia = '';

    function __construct()
    {
        parent::__construct();
        //\RightNow\Libraries\AbuseDetection::check();

    }
    /*
    public function getTickets($id_tecnico)
    {
        try
        {
            $array_obj = RNCPHP\Incident::find("AssignedTo.ParentAccount.CustomFields.c.resource_id = ".$id_tecnico. " And StatusWithType.StatusType.ID != 2");
            return $array_obj;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }
    }
    */
    public function setCotizacion($obj_incident, $id_cotizacion, $tipo)
    {
        try
        {
            //$incident = RNCPHP\Incident::fetch($nro_referencia);
            $cotizacion = new RNCPHP\Comercial\Cotizacion();
            $cotizacion->Tipo = $tipo;
            $cotizacion->IdCotizacion = $id_cotizacion;
            $cotizacion->Incident = $obj_incident;
            $cotizacion->Account = $obj_incident->AssignedTo->Account;
            $cotizacion->Save();
            return $cotizacion;
            //return true;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }

    }

    public function setStateCotizacion($obj_incident, $bool_state)
    {
        try
        {

           // $obj_incident->StatusWithType               = new RNCPHP\StatusWithType() ;
            //$obj_incident->StatusWithType->Status       = new RNCPHP\NamedIDOptList() ;
            if ($bool_state == true and $obj_incident->StatusWithType)
                $obj_incident->StatusWithType->Status->ID    = 108; //solicitud cotizacion aceptada
            elseif($bool_state == false and $obj_incident->StatusWithType)
                 $obj_incident->StatusWithType->Status->ID   = 146; //Solicitud cotizacion rechazada
            $obj_incident->Save();
            return true;
            //return true;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }

    }

    public function getObjectTicket($nro_referencia)
    {
        try
        {
            $incident = RNCPHP\Incident::fetch($nro_referencia);
            if (is_object($incident))
                return $incident;
            else
                return false;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }

    }
    public function getIncident($RefNo)
    {
      try
      {
        $obj_incident             = RNCPHP\incident::fetch($RefNo);
        if (!empty($obj_incident) and ($obj_incident instanceof RNCPHP\Incident))
        {
          $obj_result             = new \stdClass();
          $obj_result->order      = $obj_incident;

          return $obj_result;
        }
        else
        {
          return false;
          $this->error['message']  = "No se encuentra incidente activo";
          $this->error['numberID'] = 1;
        }
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        RNCPHP\ConnectAPI::rollback();
        return false;
      }
    }

    public function getCounters($RefNo)
    {

      try
      {
        $obj_incident             = RNCPHP\incident::fetch($RefNo);
        //$contadores = RNCPHP\DOS\Contador::find("Asset.SerialNumber = "  .  $obj_incident->CustomFields->c->id_hh   );
        $hh = RNCPHP\Asset::first("SerialNumber = " . $obj_incident->CustomFields->c->id_hh  );
        $contadores = RNCPHP\DOS\Contador::find("Asset.ID = {$hh->ID}");
        if (!empty($contadores))
        {

          $obj_result             = new \stdClass();

          $bn=0;
          $cc=0;
          foreach($contadores as $key => $value)
          {
            
            if( $value->TipoContador->ID==1  )
            {
             
              if($bn < $value->Valor)
              {
               
                $bn=$value->Valor;
               $copia_BN=$value;
              }
            }

            if( $value->TipoContador->ID==2  )
            {
              if($cc < $value->Valor)
              {
                $cc=$value->Valor;
                $copia_Color=$value;
              }
            }
          //$texto= $texto . '-' . $value->UpdatedTime . ' ' . $value->TipoContador->LookupName . ' ' . $value->TipoContador->ID;
          }

          $obj_result->copia_BN      = $copia_BN;
          $obj_result->copia_Color      = $copia_Color;
          $obj_result->Contadores=  $contadores ;


          return $obj_result;
        }
        else {
           return false;
        }

      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        RNCPHP\ConnectAPI::rollback();
        return false;
      }
    }


    public function getLastError()
    {
        return $this->error;
    }

  public function setIncidentState($obj_incident, $state)
	{
		try
		{
			$obj_incident->StatusWithType->Status->ID    = $state; // Cambio de estado
			$obj_incident->Save();
			return true;
			//return true;
		}
		catch ( RNCPHP\ConnectAPIError $err )
		{
			$this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
			return false;
		}

	}

  public function setPriorization($obj_incident, $setPriorization)
	{
		try
		{
			$obj_incident->CustomFields->c->priorization   = $setPriorization; // Cambio de estado
			$obj_incident->Save(RNCPHP\RNObject::SuppressAll);
			return true;
			//return true;
		}
		catch ( RNCPHP\ConnectAPIError $err )
		{
			$this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
			return false;
		}

	}

}
