<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_2 as RNCPHP;

class TicketsModel extends \RightNow\Models\Base
{
    public $error;

    function __construct()
    {
        parent::__construct();
       
    }
  
    public function getTicketsCargobyDate($fecha)
    {
        try
        {
            //$array_incident = RNCPHP\Incident::find("CreatedTime >= '{$fecha}'");
           // $array_incident = RNCPHP\Incident::find("CreatedTime >= '{$fecha}' And CustomFields.c.convenio = 0 And StatusWithType.StatusType.ID != 2");
            $array_incident = RNCPHP\Incident::find("CreatedTime >= '{$fecha}' And CustomFields.c.convenio = 0");
            if (is_array( $array_incident))
                return  $array_incident;
            else
                return false;
        }
        catch ( RNCPHP\ConnectAPIError $err ) 
        {
            $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }

    }

    public function getLastError()
    {
        return $this->error;
    }


}