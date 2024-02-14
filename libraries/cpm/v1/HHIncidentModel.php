<?php
/*
 * Given a CPHP object, appends a string to the specified field and saves the
 * object.
 */
namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;

class HHIncidentModel
{
    private $inc_obj;
    public $error = '';

    function __construct($object)
    {
        $this->inc_obj = $object;
    }

    public function saveInfoHH($marca, $modelo, $sla,$sla_rsn, $bool_convenio, $hh_tipo_contrato, $array_contadores, $array_direcciones,$serie_hh,$numero_delfos)
    {
        try
        {
            RNCPHP\ConnectAPI::commit();
            $incident                                     = RNCPHP\incident::fetch($this->inc_obj->ReferenceNumber);
            $incident->CustomFields->c->marca_hh          = $marca;
            $incident->CustomFields->c->modelo_hh         = $modelo;
            $incident->CustomFields->c->convenio          = (int) $bool_convenio;
            $incident->CustomFields->c->tipo_contrato     = $hh_tipo_contrato;
            $incident->CustomFields->c->sla_hh            = $sla;
            $incident->CustomFields->c->sla_hh_rsn        = $sla_rsn;
            $incident->CustomFields->c->cliente_bloqueado = (int) $array_direcciones['Bloqueado'];
            $incident->CustomFields->c->serie_maq         = $serie_hh;
            $incident->CustomFields->c->numero_delfos     = $numero_delfos;
            $id_ebs_direccion                             = $array_direcciones['ID_direccion'];
            //if ($id_ebs_direccion != null and $id_ebs_direccion != "-1")
                $array_Direccion_obj = RNCPHP\DOS\Direccion::find('d_id = '. $id_ebs_direccion);
            if (is_array($array_Direccion_obj) and is_object($array_Direccion_obj[0]))
                $incident->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
            //$incident->save(RNCPHP\RNObject::SuppressExternalEvents);
            //$incident->save();
            $incident->save(RNCPHP\RNObject::SuppressAll);

            if ($this->updateAsset() === false) //Creación del activo
            {
                RNCPHP\ConnectAPI::rollback();
                return false;
            }

            $counter_result = $this->saveCounters($array_contadores); //Creación de contadores
            if ($counter_result === false)
            {
                RNCPHP\ConnectAPI::rollback();
                return false;
            }

            return  $id_ebs_direccion;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
            RNCPHP\ConnectAPI::rollback();
            return false;
        }
    }

    public function saveInfoTecnico($array_tecnico, $array_direccion)
    {

        if (is_array($array_tecnico) and is_array($array_direccion))
        {
            try
            {
                RNCPHP\ConnectAPI::commit();
                $id_tecnico = $array_tecnico['ID_IBS'];
                $id_ebs_direccion = (int) $array_direccion['ID_direccion'];
                $bool_dir_bloqueo = (int) $array_direccion['Bloqueado'];

                $array_Account_obj = RNCPHP\Account::find("CustomFields.c.resource_id = ". $id_tecnico);
                if (is_array($array_Account_obj) and is_object( $array_Account_obj[0])){
                    $this->inc_obj->AssignedTo->Account = $array_Account_obj[0]; //agregar técnico
                }
                else
                {
                    $this->error = "Técnico enviado por ws no se encuentra en Oracle RightNow";
                    RNCPHP\ConnectAPI::rollback();
                    return false;
                }

                $array_Direccion_obj = RNCPHP\DOS\Direccion::find('d_id = '. $id_ebs_direccion);
                if (is_array($array_Direccion_obj) and is_object($array_Direccion_obj[0]))
                    $this->inc_obj->CustomFields->DOS->Direccion =  $array_Direccion_obj[0];
                else
                {
                    $this->error = "Dirección enviada por ws no se encuentra en Oracle RightNow";
                    RNCPHP\ConnectAPI::rollback();
                    return false;
                }

                //$this->inc_obj->CustomFields->c->cliente_bloqueado = $bool_dir_bloqueo;
                $this->inc_obj->save(RNCPHP\RNObject::SuppressAll);
                /*
                if ($this->insertInitialTask() === false) //Creación del activo
                {
                    RNCPHP\ConnectAPI::rollback();
                    return false;
                }
                */
                return true;
            }
            catch ( RNCPHP\ConnectAPIError $err )
            {
                $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
                RNCPHP\ConnectAPI::rollback();
                return false;
            }
        }
        else
        {
            $this->error = "Estructura no Valida en los valores de direccion y tecnico";
            return false;
        }

    }

    private function saveCounters($array_counters)
    {
        //$referenceNumber = "150622-000001";

        if (is_array($array_counters))
        {
            try
            {
                RNCPHP\ConnectAPI::commit();
                foreach ($array_counters as $counter)
                {
                    //Contadores
                    $count_id    = $counter['ID'];
                    $count_tipo  = $counter['Tipo'];
                    $count_valor = $counter['Valor'];
                    $contador               = new RNCPHP\DOS\Contador();
                    $contador->ContadorID   = $count_id;
                    $contador->Valor        = $count_valor;
                    $contador->Incident     = $this->inc_obj;
                    //$contador->Incident     = RNCPHP\Incident::fetch($referenceNumber);
                    $contador->TipoContador = RNCPHP\DOS\TipoContador::fetch($counter['Tipo']);
                    $contador->Asset        = $this->inc_obj->Asset;
                    $contador->save(RNCPHP\RNObject::SuppressAll);


                }
                return true;
            }
            catch ( RNCPHP\ConnectAPIError $err )
            {
                $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
                RNCPHP\ConnectAPI::rollback();
                return false;
            }
        }
        else
        {
            $this->error = "Estructura no Valida en los contadores";
            return false;
        }
    }

    private function updateAsset()
    {
        try
        {
            $asset = RNCPHP\Asset::first( "SerialNumber = '".$this->inc_obj->CustomFields->c->id_hh."'");
            if (empty($asset)) {

                $asset = new RNCPHP\Asset;
                //$asset->Name = $this->inc_obj->CustomFields->c->id_hh."-".$this->inc_obj->CustomFields->c->marca_hh."-".$this->inc_obj->CustomFields->c->modelo_hh;
                $nameHH = $this->inc_obj->CustomFields->c->id_hh."-".$this->inc_obj->CustomFields->c->marca_hh."-".$this->inc_obj->CustomFields->c->modelo_hh;
                $asset->Name = substr($nameHH, 0, 80);

                $asset->Contact = $this->inc_obj->PrimaryContact;
                //$asset->Organization = $this->inc_obj->Organization;
                $asset->Product = 2;
                $asset->SerialNumber = $this->inc_obj->CustomFields->c->id_hh;
                $asset->save(RNCPHP\RNObject::SuppressAll);

            }
            $asset->CustomFields->DOS->Direccion =  $this->inc_obj->CustomFields->DOS->Direccion;
            $asset->save(RNCPHP\RNObject::SuppressAll);
            $this->inc_obj->Asset = $asset;
            $this->inc_obj->save(RNCPHP\RNObject::SuppressAll);

        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error = "Problema al generar activo | Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }
    }

    private function insertInitialTask()
    {
        try
        {
            $new_task = new RNCPHP\Task();

            //Set assigned account
            $new_task->AssignedToAccount = $this->inc_obj->AssignedTo->Account;

            //Set comment
            $new_task->Comment = $this->inc_obj->Subject;

            //Set Inherit
            $new_task->Inherit = new RNCPHP\InheritOptions();
            $new_task->Inherit->InheritContact = true;
            $new_task->Inherit->InheritOrganization = true;
            $new_task->Inherit->InheritStaffAssignment = true;

            //Set Name
            $new_task->Name = "Tarea: ".$this->inc_obj->Subject;

            //Set Priority
            $new_task->Priority = new RNCPHP\NamedIDOptList();
            $new_task->Priority->ID = 1;

            //Add Incident
            $new_task->ServiceSettings = new RNCPHP\TaskServiceSettings();
            $new_task->ServiceSettings->Incident = $this->inc_obj;

            //Set Start time
            $new_task->StartTime = time();

            //Set Task type
            $new_task->TaskType = new RNCPHP\NamedIDOptList();
            $new_task->TaskType->ID = 1;

            $new_task->save(RNCPHP\RNObject::SuppressAll);
            return true;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $this->error = "Problema al generar la Tarea | Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }
    }

    public function test()
    {
        $this->error = "Mensaje de prueba";
        return false;
    }

    public function getLastError()
    {
        return $this->error;
    }


}
