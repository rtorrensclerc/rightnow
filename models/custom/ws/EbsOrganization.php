<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_2 as RNCPHP;

class EbsOrganization extends \RightNow\Models\Base
{
    private $error = '';

    function __construct()
    {
        parent::__construct();
        //\RightNow\Libraries\AbuseDetection::check(); // necesario para modificar las organizaciones
    }

    public function modifyClient($idCliente, $partyNumber, $rut, $razonSocial)
    {
        //echo "ID CLiente". $idCliente. "Party Number ".$partyNumber;
        if (!empty($idCliente) and !empty($partyNumber))
        {
            //$ObjOrg = RNCPHP\Organization::find("CustomFields.c.id_cliente = '".$idCliente."'");
            try {
                $ObjOrg = RNCPHP\Organization::find("CustomFields.c.id_cliente = {$idCliente}");
                //print_r($ObjOrg);
                if (empty($ObjOrg) and is_array($ObjOrg))
                {
                   return $this->createClient($idCliente, $partyNumber, $rut, $razonSocial);
                }
                else if (is_object($ObjOrg[0]))
                {
                   return $this->updateClient($ObjOrg[0], $idCliente, $partyNumber, $rut, $razonSocial);
                }
            }
            catch ( RNCPHP\ConnectAPIError $err )
            {
                $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
                return false;
            }
        }
        else
        {
            $this->error = "partyNumber o id de cliente vacios";
            return false;
        }
    }

    private function createClient($idCliente, $partyNumber, $rut, $razonSocial)
    {
        try
        {
            $ObjOrg = new RNCPHP\Organization();
            $ObjOrg->Name = $razonSocial. "-" .$idCliente;
            $ObjOrg->CustomFields->c->id_cliente = $idCliente;
            $ObjOrg->CustomFields->c->rut = $rut;
            $ObjOrg->CustomFields->c->party_number = $partyNumber;
            $ObjOrg->Save();
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }
        return true;
    }

    private function updateClient($ObjOrg, $idCliente, $partyNumber, $rut, $razonSocial)
    {

        if (is_object($ObjOrg))
        {
            try
            {
                $ObjOrg->Name = $razonSocial. "-" .$idCliente;
                $ObjOrg->CustomFields->c->id_cliente = $idCliente;
                $ObjOrg->CustomFields->c->rut = $rut;
                $ObjOrg->CustomFields->c->party_number = $partyNumber;
                $ObjOrg->Save();
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
            $this->error = "El objeto organización no es un objeto";
            return false;
        }
    }

    public function modifyDirection($id_direction, $id_parent_client, $party_site_number, $dir_envio, $region, $comuna, $is_facturacion, $activate)
    {
        //echo "ID CLiente". $idCliente. "Party Number ".$partyNumber;
        if (!empty($id_direction) and !empty($id_parent_client) and !empty($party_site_number) and !empty($dir_envio))
        {
            $ObjDirection = RNCPHP\DOS\Direccion::find("d_id = {$id_direction}");

            if (empty($ObjDirection) and is_array($ObjDirection))
            {
               //return false;
               return $this->createDirection($id_direction, $id_parent_client, $party_site_number, $dir_envio, $region, $comuna, $is_facturacion, $activate);
            }
            else if (is_object($ObjDirection[0]))
            {
               //return false;
               return $this->updateDirection($ObjDirection[0], $id_direction, $id_parent_client, $party_site_number, $dir_envio, $region, $comuna, $is_facturacion, $activate);
            }
        }
        else
        {
            $this->error = "Dirección de envío, id de dirección o id de cliente se encuentran vacios";
            return false;
        }
    }

    private function createDirection($id_direction, $id_parent_client, $party_site_number, $dir_envio, $region, $comuna, $is_facturacion, $activate)
    {
        try
        {
            $ObjOrg = RNCPHP\Organization::find("CustomFields.c.id_cliente = {$id_parent_client}");

            if (!empty($ObjOrg) and is_array($ObjOrg))
            {
                $ObjDir = new RNCPHP\DOS\Direccion();
                $ObjDir->d_id = $id_direction;
                $ObjDir->party_site_number = $party_site_number;
                $ObjDir->dir_envio = $dir_envio;
                $ObjDir->es_facturacion = (int)$is_facturacion;
                $ObjDir->activado = (int)$activate;
                $ObjDir->Organization = $ObjOrg[0];

                $ObjDir->ebs_region = $region;
                $ObjDir->ebs_comuna = $comuna;

                $ObjDir->Save();
            }
            else
            {
                $this->error = "ID de Padre no encontrado";
                return false;
            }
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }
        return true;
    }

    private function updateDirection($ObjDirection, $id_direction, $id_parent_client, $party_site_number, $dir_envio, $region, $comuna, $is_facturacion, $activate)
    {
        try
        {
            $ObjOrg = RNCPHP\Organization::find("CustomFields.c.id_cliente = {$id_parent_client}");

            if (!empty($ObjOrg) and is_array($ObjOrg))
            {
                $ObjDir = $ObjDirection;
                $ObjDir->d_id = $id_direction;
                $ObjDir->party_site_number = $party_site_number;
                $ObjDir->dir_envio = $dir_envio;
                $ObjDir->es_facturacion = (int)$is_facturacion;
                $ObjDir->activado = (int)$activate;
                $ObjDir->Organization = $ObjOrg[0];

                $ObjDir->ebs_region = $region;
                $ObjDir->ebs_comuna = $comuna;

                $ObjDir->Save();
            }
            else
            {
                $this->error = "ID de Padre no encontrado";
                return false;
            }
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
