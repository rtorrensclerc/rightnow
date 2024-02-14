<?php

/**
 * Skeleton SaveArFlow cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;

class SaveArFlow
{
   
    //URL de Test
    //CONST URL          = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    //URL de Producción:
    //CONST URL          = "http://190.14.56.27:8080//dts/rn_integracion/rntelejson.php";


    public static function HandleIncident( $incident)
    {
   
    try
    {
      self::insertPrivateNote($incident, "SaveArFlow");
      $z=new RNCPHP\OP\Incident_Status();
      $z->incident=$incident;
      $z->Account_Id=$incident->AssignedTo->Account->ID;
      $z->ar_flow=$incident->CustomFields->c->ar_flow->ID;
      $z->save();
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
