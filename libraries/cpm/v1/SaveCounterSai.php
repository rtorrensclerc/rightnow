<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;
use RightNow\Connect\v1_3 as RNCPHP;

require_once "Labels.php";
require_once "Blowfish.php";
require_once "ConnectUrl.php";

class SaveCounterSai
{
   
    static function HandleCounter($runMode, $action, $incident, $cycle)
    {

      $counter_bn =0;
      $counter_color =0;
      $counter_a3_bn =0;
      $counter_a3_color =0;
      $counter_b4_bn =0;
      $counter_b4_color =0;
      $counter_dupl =0;
      $counter_metro =0;
      


      $obj_incident = RNCPHP\Incident::fetch($incident->ID);
     
        if ($cycle !== 0) return;
        //self::insertPrivateNote($obj_incident,"Inicio Contadores");
    
    
     
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
        //self::insertPrivateNote($obj_incident, "Leyendo 1000019");
        
        
        //echo $cfg2->Value . "---->";
        if(is_null($obj_incident->CustomFields->c->cont1_hh))
        {
          $cont1_hh=0;
        }
        else
        {
          $cont1_hh=$obj_incident->CustomFields->c->cont1_hh;
        }
        if(is_null($obj_incident->CustomFields->c->cont2_hh))
        {
          $cont2_hh=0;
        }
        else
        {
          $cont2_hh=$obj_incident->CustomFields->c->cont2_hh;
        }
       
      
        //self::insertPrivateNote($obj_incident,"[" . $cont1_hh . "]");

        //self::insertPrivateNote($obj_incident,"[" . $cont2_hh . "]");
        //$hh = RNCPHP\Asset::first("SerialNumber = " . $obj_incident->CustomFields->c->id_hh  );
        //self::insertPrivateNote($obj_incident, "SaveCounterSai HH -> " . json_encode( $obj_incident->CustomFields->c->id_hh));

        //self::insertPrivateNote($obj_incident, "HH -> " . json_encode( $obj_incident));     
        
        //$contadores = RNCPHP\DOS\Contador::find('Asset.ID ='. $obj_incident->Asset);
        $contadores = RNCPHP\DOS\Contador::find('Asset.ID ='. $obj_incident->Asset->ID);
 
        //self::insertPrivateNote($obj_incident, "contadores -> " . json_encode( $contadores));     
     

        foreach($contadores as $key => $value)
        {
            if( ($value->TipoContador->ID==1 or $value->TipoContador->ID==13 or $value->TipoContador->ID==16 ) and $value->Valor )
          {
         
            $copia_BN=$value;
            $id_BN=$value->ContadorID;
            $id_BN_type=$value->TipoContador->ID;
       
          }

          if( ($value->TipoContador->ID==2 or $value->TipoContador->ID==14 ) and $value->Valor )
          {
            $copia_Color=$value;
            $id_Color=$value->ContadorID;
            $id_Color_type=$value->TipoContador->ID;
          }
        }



          
          switch($id_BN_type)
          {
            case 1:
              $counter_bn=$cont1_hh;
             break;
            
            case 13:
              $counter_dupl=$cont1_hh;
             break;
            case 16:
              $counter_metro=$cont1_hh;
            break;
          }
    
          switch($id_Color_type)
          {
            case 2:
              $counter_color=$cont2_hh;
            break;
            
            case 14:
              $counter_metro=$cont2_hh;
              break ;
          }
       
          //self::insertPrivateNote($obj_incident,"TIPO " . json_encode( $obj_incident->Disposition));

          switch($incident->StatusWithType->Status->ID)
          {
            case 2:
            case 166:
              $estado='Cierre';
            break;
            case 1:
            case 165:
            case 119:
            case 118:
            case 175:
            case 129:
            case 134:
              $estado='Creación';
            break;
            default:
              $estado=$incident->StatusWithType->Status->LookupName;
            break;
          }
      
          //self::insertPrivateNote($obj_incident,"SOLO HH " . $HH);
          $json_data='{
            "hh": ' . $obj_incident->CustomFields->c->id_hh .',
            "comments": "RIGHTNOW-'  . $obj_incident->ReferenceNumber . '-' .$incident->Disposition->LookupName . '-' . $estado .'",
            "Purchase_order": "",
            "user_id": "",
            "counters": {
                "counter_bn": ' . $counter_bn .',
                "counter_color": ' .$counter_color .',
                "counter_a3_bn": ' .$counter_a3_bn .',
                "counter_a3_color": ' .$counter_a3_color .',
                "counter_b4_bn": ' .$counter_b4_bn .',
                "counter_b4_color": ' .$counter_b4_color .',
                "counter_dupl": ' .$counter_dupl .',
                "counter_metro": ' .$counter_metro .'
            }
        }';
        
      
       
        //self::insertPrivateNote($obj_incident,"ENVIANDO " . $json_data);
       // $json_request=json_encode($json_data);
       //self::insertPrivateNote($obj_incident,"DATA-> " . $cfg2->Value ."/mb/insertCounterRN");
       $url=$cfg2->Value ."/mb/insertCounterRN";
       //self::insertPrivateNote($obj_incident,"URL " . $url);
        $response=ConnectUrl::requestCURLJsonRaw($url, $json_data); 
        //self::insertPrivateNote($obj_incident, '->'. $response);
        $data=json_decode($response);
        return;

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
            $incident->Threads                   = new RNCPHP\ThreadArray();
            $incident->Threads[0]                = new RNCPHP\Thread();
            $incident->Threads[0]->EntryType     = new RNCPHP\NamedIDOptList();
            $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
            $incident->Threads[0]->Text          = "Error " . $err->getMessage() . '[' .$textoNP. ']';
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            return FALSE;
        }
    }
}
