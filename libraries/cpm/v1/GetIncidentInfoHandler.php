<?php

/**
 * Skeleton Opportunity cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;

require_once "Labels.php";




class GetIncidentInfoHandler
{
  

    static function GetIncidentInfo($runMode, $action, $incident, $cycle)
    {

        if ($cycle !== 0) return;

        $incident = RNCPHP\Incident::fetch($incident->ID);

        self::insertPrivateNote($incident, 'HOLA');
    }

    static function insertPrivateNote($incident, $textoNP)
    {
        try
        {
            $incident->Threads = new RNCPHP\ThreadArray();
            $incident->Threads[0] = new RNCPHP\Thread();
            $incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
            $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
            $incident->Threads[0]->Text = $textoNP;
            $incident->Save(RNCPHP\RNObject::SuppressAll);
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {
            $incident->Subject = "Error" . $err->getMessage();
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            return false;
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

}
