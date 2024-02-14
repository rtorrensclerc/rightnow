<?php

/**
 * Skeleton Incdent cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_2  as RNCPHP;

require_once "Labels.php";
//require_once "Blowfish.php";
require_once "ConnectUrl.php";



class EnviaSoloManoObra
{
    //CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    //URL de Test
    //CONST URL          = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    //URL de Producción:
    //CONST URL          = "http://190.14.56.27:8080//dts/rn_integracion/rntelejson.php";


    static function HandleIncident($runMode, $action, $incidents, $cycle)
    {

       
        //if ($cycle !== 0) return;
        $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL
        $cfg2 = RNCPHP\Configuration::fetch( 1000019 ); //CUSTOM_CFG_WSO2_URL
        $incident = RNCPHP\Incident::fetch($incidents->ID);
        
        //self::insertPrivateNote($incident, "RESPxxxx : ". $incident->CustomFields->c->invoice_number);
        if($incident->CustomFields->c->invoice_number==0)
        {
         
          
          $new_opportunity                                  = new RNCPHP\Opportunity();
          //self::insertPrivateNote($incident, "NUEVO 2: ");
          $new_opportunity->Name                            = "Cobro Mano de Obra Inicial - {$incident->ReferenceNumber}";
          $new_opportunity->InitialContactDate              = $incident->UpdatedTime;
          $new_opportunity->PrimaryContact                  = new RNCPHP\OpportunityContact();
          $new_opportunity->PrimaryContact->Contact         = $incident->PrimaryContact;
          $new_opportunity->StatusWithType                  = new RNCPHP\StatusWithType();
          $new_opportunity->StatusWithType->Status->ID      = 200; //14; //Aceptado
          $new_opportunity->CustomFields->c->source_opp->ID = 66; // Cobro Mano de Obra
          $new_opportunity->CustomFields->c->id_venus=$incident->ReferenceNumber;
          $new_opportunity->CustomFields->c->id_hh=$incident->CustomFields->c->id_hh;
          $new_opportunity->CustomFields->c->oc_number=$incident->CustomFields->c->contract_number;
          //self::insertPrivateNote($incident, "RESP11 : ". $incident->CustomFields->c->invoice_number);
          //Asignar Territorio
          if ($incident->AssignedTo->Account->SalesSettings->Territory)
          {
            $new_opportunity->Territory                     = RNCPHP\SalesTerritory::fetch($incident->AssignedTo->Account->SalesSettings->Territory->ID);
          }
       
          //self::insertPrivateNote($incident, "RESP2 : ". $incident->CustomFields->c->invoice_number);
          //Organización del Incidente
          if ($incident->CustomFields->DOS->Direccion->Organization)
          {
            $new_opportunity->Organization                  = RNCPHP\Organization::fetch($incident->CustomFields->DOS->Direccion->Organization->ID);
          }
    
          $new_opportunity->CustomFields->OP->IncidentService = $incident;
         
          $a=RNCPHP\Comercial\Ejecutivo::fetch(105);
          //self::insertPrivateNote($incident, "RESP3 : ". $incident->CustomFields->c->invoice_number);
    
          $new_opportunity->CustomFields->Comercial->EjecutivoZona = $a;
        
          if (!empty($incident->CustomFields->DOS->Vendedor))
          {
             $new_opportunity->CustomFields->Comercial->Ejecutivo = $incident->CustomFields->DOS->Vendedor;
          }
          if (!empty($incident->CustomFields->DOS->DireccionFacturacioon))
          {
            $new_opportunity->CustomFields->OP->Direccion = $incident->CustomFields->DOS->DireccionFacturacioon;
          }
          //self::insertPrivateNote($incident, "RESP4 : ". $incident->CustomFields->c->invoice_number);
          //$new_opportunity->CustomFields->c->id_ar= $incident->CustomFields->c->invoice_number;
          $new_opportunity->CustomFields->c->id_venus= $incident->ReferenceNumber;
          
          /* Se comenta esta linea ya que con  SuppressAll no queda en ningun estado de regla.
            $new_opportunity->save(RNCPHP\RNObject::SuppressAll);
          */
          $new_opportunity->save();
    
    
          $cfg_idProductWF = RNCPHP\Configuration::fetch( "CUSTOM_CFG_PRODUCT_ID_VISITA" );
          $Product           = RNCPHP\OP\Product::fetch($cfg_idProductWF->Value);
        
          //Agregar Linea Cobro mano de obra
          $WorkForceLine                    = new RNCPHP\OP\OrderItems();
          $WorkForceLine->QuantitySelected  = 1;
          $WorkForceLine->QuantityReserved  = 1;
          $WorkForceLine->QuantityConfirmed = 1;
          $WorkForceLine->Product           = $Product;
          $WorkForceLine->Opportunity       = $new_opportunity;
          $WorkForceLine->State             = 3; //Confirmado
          $WorkForceLine->Save();
          //deja que el Incidente avance a la siguiente etapa 
          $incident->CustomFields->c->invoice_number=$new_opportunity->ID;
          $incident->StatusWithType->Status->ID=117;
          self::insertPrivateNote($incident, "PTTO Creado : ". $new_opportunity->ID);
          $incident->Save(RNCPHP\RNObject::SuppressAll);
          
        }
  

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
