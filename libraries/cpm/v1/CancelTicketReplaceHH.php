<?php

/**
 * Skeleton Incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_2 as RNCPHP;

class CancelTicketReplaceHH
{
    static function HandleIncident($runMode, $action, $incident_old, $cycle)
    {

        if ($cycle !== 0) return;


        try
        {
          //self::insertPrivateNote($incident_old, "LLegando a CancelTicketReplaceHH" );
          
          $incident = RNCPHP\Incident::fetch($incident_old->CustomFields->OP->Incident->ID);
          $incident->StatusWithType->Status->ID=162;
          $incident->CustomFields->c->motivo_solucion=null;
          
          
          $Account=$incident_old->Threads[0]->Account->DisplayName;
          $textoNP=$incident_old->Threads[0]->Text;
          /* Debemos copiar la nota del supervisor en el ticket */
          self::insertPrivateNote($incident,"Nota de Jefe de Taller(" . $Account . "): [" . $textoNP . "]");
           
          $incident->Save();
          
        }
        catch (RNCPHP\ConnectAPIError $err )
        {
           $message = "Error ".$err->getMessage();
           self::insertPrivateNote($incident, "Error Query: ".$message);

        }


    }

    static function insertPrivateNote($incident, $textoNP)
    {
        try
        {

           
          $incident->Threads = new RNCPHP\ThreadArray();
          $incident->Threads[0] = new RNCPHP\Thread();
          $incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
          $incident->Threads[0]->EntryType->ID = 1; // Used the ID here. See the Thread object for definition
          $incident->Threads[0]->Text = $textoNP;
          $incident->Save(RNCPHP\RNObject::SuppressAll);
        }
        catch (RNCPHP\ConnectAPIError $err)
        {
          $incident->CustomFields->c->shipping_instructions=$incident->CustomFields->c->shipping_instructions . 'Error';
        
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            return FALSE;
        }
    }

}
