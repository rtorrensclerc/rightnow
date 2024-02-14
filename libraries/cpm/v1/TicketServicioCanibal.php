<?php

/**
 * Skeleton Incdent cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_2  as RNCPHP;







class TicketServicioCanibal
{
   
    static function HandleIncident($runMode, $action, $incident, $cycle)
    {
       
        self::insertPrivateNote($incident, "Homologa Incidente Padre Status (" . $incident->StatusWithType->Status->ID. "," . $incident->CustomFields->OP->Incident->StatusWithType->Status->ID . ")");
        

        if($incident->Disposition->ID==43)
        {

            switch($incident->StatusWithType->Status->ID)
            {
                case 155:
                    $incident->CustomFields->OP->Incident->StatusWithType->Status->ID=155;    
                    $incident->CustomFields->OP->Incident->Save(RNCPHP\RNObject::SuppressAll);
                    $incident->Save(RNCPHP\RNObject::SuppressAll);
                    break;
                case 156:
                    $incident->CustomFields->OP->Incident->StatusWithType->Status->ID=156;    
                    $incident->CustomFields->OP->Incident->Save(RNCPHP\RNObject::SuppressAll);
                    $incident->Save(RNCPHP\RNObject::SuppressAll);
                    break;
                case 158:
                    $incident->CustomFields->OP->Incident->StatusWithType->Status->ID=158;    
                    $incident->CustomFields->OP->Incident->Save(RNCPHP\RNObject::SuppressAll);
                    $incident->Save(RNCPHP\RNObject::SuppressAll);
                case 157:
                    $incident->CustomFields->OP->Incident->StatusWithType->Status->ID=157;    
                    $incident->CustomFields->OP->Incident->Save(RNCPHP\RNObject::SuppressAll);
                    $incident->StatusWithType->Status->ID=149;
                    $incident->Save(RNCPHP\RNObject::SuppressAll);
                    break;
                case 151:
                    $incident->CustomFields->OP->Incident->StatusWithType->Status->ID=151;    
                    $incident->CustomFields->OP->Incident->Save(RNCPHP\RNObject::SuppressAll);
                    $incident->Save(RNCPHP\RNObject::SuppressAll);
                    break;
                default :

                return;
            }
        }
/*
        if($incident->Disposition->ID==25)
        {
            $incidentR = RNCPHP\Incident::find('CustomFields.OP.Incident.ID=' . $incident->ID  . '  and StatusWithType.status.ID not in(2,149,146)  ' );


            if(is_array($incidentR))
            {
                $incidentR[0]->StatusWithType->Status->ID = 2;  // Cerrado
                $incidentR[0]->save();
            }
        }
*/
  
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
          return false;
      }
    }

    static function insertBanner($incident, $typeBanner, $texto = '')
    {
        if (!is_numeric($typeBanner) and $typeBanner > 3 and $typeBanner < 0)
            $typeBanner = 1;

        $texto = '';
        if ($typeBanner == 3)
            $texto = "Error respuesta OM";

        $incident->Banner->Text           = $texto;
        $incident->Banner->ImportanceFlag = $typeBanner; // [Low] => 1, [Medium] => 2, [High] => 3
        $incident->Save(RNCPHP\RNObject::SuppressAll);
    }

}
