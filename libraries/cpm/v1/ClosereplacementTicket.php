<?php

/**
 * Skeleton ClosereplacementTicket cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;

class ClosereplacementTicket
{
   
    

    public static function HandleIncident( $incident)
    {
   
    try
    {
      
      $IncidentR  = RNCPHP\Incident::find(" CustomFields.OP.Incident.ID = " . $incident->ID . ' and StatusWithType.status.ID not in(2,149,146)' ); 

      foreach($IncidentR as $i)
      {
          //self::insertPrivateNote($i, "Cerrando Ticket de Reparación " . $i->ReferenceNumber);
          $i->StatusWithType->Status->ID=2;  //Cerrado   2023/11/21  RTC   113 Por Retirar  estaba antes
          $i->Save(RNCPHP\RNObject::SuppressAll);
      }
      
    }
      catch (RNCPHP\ConnectAPIError $err) 
      {
        
        $incident->CustomFields->c->shipping_instructions=$err->getMessage();
        $incident->Save(RNCPHP\RNObject::SuppressAll);
        self::insertPrivateNote($incident, "CPM Motor: " . $err->getMessage());
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
