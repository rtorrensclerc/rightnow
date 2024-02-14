<?php
namespace Custom\Models;
use RightNow\Connect\v1_3 as RNCPHP;

class Profiling extends \RightNow\Models\Base
{
    public  $error          = array ('numberID' => null , 'message' => null);
    private $nro_referencia = '';

    function __construct()
    {
        parent::__construct();
        // \RightNow\Libraries\AbuseDetection::check();
    }

    public function getProfilingByType($typeID)
    {
      try
      {
        $a_profileModule = RNCPHP\PROF\ProfileModule::find("Type.ID =  " .$typeID);
        return $a_profileModule;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {   
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        return false;
      }
    }

    /**
    * Obtiene la lista de modulos activos para perfilamiento
    */
    public function getModules()
    {
      try 
      {
        $menues = RNCPHP\PROF\Module::find("ID is not null");
        $a_menu = array();
        foreach ($menues as $menu)
        {
          $a_tempMenu["ID"] = $menu->ID;
          $a_tempMenu["name"] = $menu->Name;
          $a_menu[]   = $a_tempMenu;
        }
        return $a_menu;
      } 
      catch (RNCPHP\ConnectAPIError $err) 
      {
        $this->error['message'] = "Código: ".$err->getCode()." ".$err->getMessage();
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
