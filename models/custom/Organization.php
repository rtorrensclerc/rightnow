<?php
namespace Custom\Models;
use RightNow\Connect\v1_3 as RNCPHP;

class Organization extends \RightNow\Models\Base
{
    public  $error          = array ('numberID' => null , 'message' => null);
    private $nro_referencia = '';

    function __construct()
    {
        parent::__construct();
        // \RightNow\Libraries\AbuseDetection::check();
    }

    public function getOrganizationByRut($rut_organization)
    {
        try
        {
            $organization_array = RNCPHP\Organization::find("CustomFields.c.rut = '$rut_organization'");

            if(count($organization_array) > 0)
            {
                $id_organization;
                foreach ($organization_array as $org)
                {
                    $id_organization = $org->ID;
                }

                $organization = RNCPHP\Organization::fetch($id_organization);
                return $organization;
            }
            else
            {
                return false;
            }
        }
        catch (RNCPHP\ConnectAPIError $err )
        {
            $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }
    }

    public function getOrganizationById($id_organization)
    {
        try
        {
            $organization = RNCPHP\Organization::fetch($id_organization);

            return $organization;
        }
        catch (RNCPHP\ConnectAPIError $err )
        {
            $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }
    }

    public function getDirectionsByOrgId($id)
    {
      try
      {
          $a_obj = RNCPHP\DOS\Direccion::find("Organization.ID = ".$id);
          return $a_obj;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
          $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
          return false;
      }
    }

    public function getDirectionByEbsId($id)
    {
      try
      {
        $obj = RNCPHP\DOS\Direccion::first("d_id = ".$id);
        if ($obj instanceof RNCPHP\DOS\Direccion)
          return $obj;
        else
        {
          $this->error['message']  = "Dirección no encontrada en el sistema";
          return false;
        }
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        return false;
      }
    }

    public function getDirectionById($id)
    {
      try
      {
        $obj = RNCPHP\DOS\Direccion::fetch($id);
        if ($obj instanceof RNCPHP\DOS\Direccion)
          return $obj;
        else
        {
          $this->error['message']  = "Dirección no encontrada en el sistema";
          return false;
        }
      }
      catch (RNCPHP\ConnectAPIError $err)
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        return false;
      }
    }

    public function getListByName($name)
    {
      try
      {
        $res = RNCPHP\ROQL::query("select o.name, o.ID from Organization as o where o.Name LIKE '%{$name}%' LIMIT 500")->next();
        $a_obj = array();
        while($obj = $res->next()) {
            $a_tempObj["ID"]   = $obj["ID"];
            $a_tempObj["name"] = $obj["Name"];
            $a_obj[] = $a_tempObj;
        }
        return $a_obj;

      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
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
