<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;

class SaveCounters
{
    static function HandleIncident($runMode, $action, $incident, $cycle)
    {

        if ($cycle !== 0) return;
        $bannerNumber = 0;

        try 
        {
            //self::insertPrivateNote($incident, "Salvando Contadores");
            self::saveCounters($incident);
        } 
        catch (Exception $e) 
        {
            self::insertPrivateNote($incident, "Error " . $e->getMessage());
            return FALSE;
        }
    }

    static function insertPrivateNote($incident, $textoNP)
    {
        try 
        {
            $incident->Threads                   = new RNCPHP\ThreadArray();
            $incident->Threads[0]                = new RNCPHP\Thread();
            $incident->Threads[0]->EntryType     = new RNCPHP\NamedIDOptList();
            $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
            $incident->Threads[0]->Text          = $textoNP;
            $incident->Save(RNCPHP\RNObject::SuppressAll);
        } 
        catch (RNCPHP\ConnectAPIError $err) 
        {
            $incident->Subject = "Error " . $err->getMessage();
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            return FALSE;
        }
    }

    static function insertBanner($incident, $typeBanner, $texto = '')
    {
        if (!is_numeric($typeBanner) and $typeBanner > 3 and $typeBanner < 0)
            $typeBanner = 1;

        $texto = '';
        if ($typeBanner == 3)
            $texto = "HH no pudo ser asignada";

        $incident->Banner->Text = $texto;
        $incident->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
        $incident->Save(RNCPHP\RNObject::SuppressAll);
    }

    static function saveCounters($incident)
    {
        $a_json_hh = json_decode($incident->CustomFields->c->json_hh, TRUE);
        //self::insertPrivateNote($incident, 'JSON READ ' . $incident->CustomFields->c->json_hh);    
        if (array_key_exists("counters", $a_json_hh))
        {
            try 
            {
                foreach ($a_json_hh["counters"] as $counter) 
                {
                    //Contadores
                    $count_id               = $counter['ID'];
                    $count_tipo             = $counter['Tipo'];
                    $count_valor            = $counter['Valor'];
                    $contador               = new RNCPHP\DOS\Contador();
                    $contador->ContadorID   = $count_id;
                    $contador->Valor        = $count_valor;
                    $contador->Incident     = $incident;
                    $contador->TipoContador = RNCPHP\DOS\TipoContador::fetch($counter['Tipo']);
                    $contador->Asset        = $incident->Asset;
                    $contador->save(RNCPHP\RNObject::SuppressAll);
                }
                return TRUE;
            } 
            catch (RNCPHP\ConnectAPIError $err) 
            {
                self::insertPrivateNote($incident, "Codigo : " . $err->getCode() . " " . $err->getMessage());
                return FALSE;
            }
        } 
        else 
        {
            self::insertPrivateNote($incident, "Estructura no Valida en los contadores");
            return FALSE;
        }
    }
}
