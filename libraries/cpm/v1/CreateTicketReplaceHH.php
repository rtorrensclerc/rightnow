<?php

/**
 * Skeleton Incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_2 as RNCPHP;

class CreateTicketReplaceHH
{
    static function HandleIncident($runMode, $action, $incident_old, $cycle)
    {

        if ($cycle !== 0) return;


        try
        {
          //self::insertPrivateNote($incident_old, "LLegando a CreateTicketReplaceHH" );
          $incident                                              = new RNCPHP\Incident();
          $incident->PrimaryContact                              = $incident_old->PrimaryContact;
          $incident->Disposition                                 = RNCPHP\ServiceDisposition::fetch(123); // Reemplazo Equipo
          $incident->StatusWithType                              = new RNCPHP\StatusWithType() ;
          $incident->StatusWithType->Status                      = new RNCPHP\NamedIDOptList() ;
          $incident->StatusWithType->Status->ID                  = 175 ;  //  Supervisión
          $incident->CustomFields->OP->Incident               = $incident_old;
          //$incident->CustomFields->c->numero_delfos           = $incident_old->CustomFields->c->numero_delfos;
          $incident->Asset                                    = $incident_old->Asset ;
          $incident->CustomFields->c->requiere_taller=false;
          $incident->CustomFields->c->id_hh                   = $incident_old->CustomFields->c->id_hh;
          $incident->Subject                                  ="Reemplazo de Equipo HH : " .$incident->CustomFields->c->id_hh;
          $incident->CustomFields->DOS->Direccion             = $incident_old->CustomFields->DOS->Direccion;
          $incident->CustomFields->c->tipo_contrato           = $incident_old->CustomFields->c->tipo_contrato;
          
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
