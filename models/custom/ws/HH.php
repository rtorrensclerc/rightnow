<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_2 as RNCPHP;

class HH extends \RightNow\Models\Base
{
    //public $error = '';
    public  $error          = array ('numberID' => null , 'message' => null);
    private $nro_referencia = '';

    function __construct()
    {
        parent::__construct();
        //\RightNow\Libraries\AbuseDetection::check();
    }


    public function getInfoReparation($idHH)
    {
      try
      {
        $lastIncident     = RNCPHP\Incident::find("CustomFields.c.id_hh = {$idHH} and StatusWithType.StatusType.ID = 2 order by UpdatedTime DESC limit 1");
        $cont_1           = $this->getCounter($idHH, 1); //Copia B/N
        $cont_2           = $this->getCounter($idHH, 2); //Copia Color
        $obj_response = (object)[];
        if (is_object($lastIncident[0]))
        {
          $obj_response->id_hh    = $lastIncident[0]->CustomFields->c->id_hh;
          $obj_response->cont_1   = $cont_1;
          $obj_response->cont_2   = $cont_2;
          $obj_response->org      = $lastIncident[0]->CustomFields->DOS->Direccion->Organization->Name;
          $obj_response->model_hh = $lastIncident[0]->CustomFields->c->modelo_hh;
          return $obj_response;
        }
        else
          return $obj_response;

      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }
    }

    private function getCounter($idHH, $idType)
    {

      try {

          $asset = RNCPHP\Asset::first("SerialNumber = ".$idHH);
          $obj_counter = RNCPHP\DOS\Contador::first("Asset.ID = {$asset->ID} and TipoContador.ID = {$idType} order by UpdatedTime DESC limit 1");
          return $obj_counter->Valor;

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
