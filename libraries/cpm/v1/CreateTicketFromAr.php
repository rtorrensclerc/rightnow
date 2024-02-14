<?php

/**
 * Skeleton OpportCreateSaiRemovalMachine cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_3 as RNCPHP;

class CreateTicketFromAr
{
   
    //URL de Test
    //CONST URL          = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";
    //URL de Producción:
    //CONST URL          = "http://190.14.56.27:8080//dts/rn_integracion/rntelejson.php";


    public static function HandleIncident( $incident_old)
    {
   
    try
    {
      $incident                                              = new RNCPHP\Incident();
      $incident->PrimaryContact                              = $incident_old->PrimaryContact;
      $incident->CustomFields->c->tipificacion_sugerida->ID  = 221;  /* Mantencion */
      //$incident->Subject                                     = "SOLICITUD ASISTENCIA TÉCNICA - ESPECIAL - ". strtoupper($incident->CustomFields->c->tipificacion_sugerida->LookupName);
      $incident->Subject                                     = $incident_old->Subject;
      $incident->Product                                     = $incident_old->Product;
      $incident->Category                                    = $incident_old->Category;
      $incident->Disposition                                 = RNCPHP\ServiceDisposition::fetch(25); // Soporte Especial
      $incident->StatusWithType                              = new RNCPHP\StatusWithType() ;
      $incident->StatusWithType->Status                      = new RNCPHP\NamedIDOptList() ;
      $incident->StatusWithType->Status->ID                  = 162 ;  // asignado a tecnico
      

      //$incident->CustomFields->c->diagnostico->ID            = $incident_old->CustomFields->c->diagnostico->ID;
      //$incident->CustomFields->c->motivo_solucion->ID        = $incident_old->CustomFields->c->motivo_solucion->ID;
      $incident->CustomFields->c->tipo->ID                   = $incident_old->CustomFields->c->tipo->ID;
      $incident->CustomFields->c->seguimiento_tecnico    = $incident_old->CustomFields->c->seguimiento_tecnico;
      $incident->CustomFields->c->seguimiento_tecnico->ID    = 15;
      $incident->CustomFields->c->soporte_telefonico         =false; // NO

      //Datos de SOLICITUD
      $incident->CustomFields->c->cont1_hh                   = $incident_old->CustomFields->c->cont1_hh;
      $incident->CustomFields->c->cont2_hh                   = $incident_old->CustomFields->c->cont2_hh;
      
      //Datos de HH
      $incident->CustomFields->c->id_hh                      = $incident_old->CustomFields->c->id_hh;
      $incident->AssignedTo->Account                         = $incident_old->AssignedTo->Account;
      
      $incident->CustomFields->c->marca_hh                = $incident_old->CustomFields->c->marca_hh;
      $incident->CustomFields->c->modelo_hh               = $incident_old->CustomFields->c->modelo_hh;
      $incident->CustomFields->c->convenio                = $incident_old->CustomFields->c->convenio;
      $incident->CustomFields->c->tipo_contrato           = $incident_old->CustomFields->c->tipo_contrato;
      $incident->CustomFields->c->sla_hh                  = $incident_old->CustomFields->c->sla_hh;
      $incident->CustomFields->c->sla_hh_rsn              = $incident_old->CustomFields->c->sla_hh_rsn;
      $incident->CustomFields->c->cliente_bloqueado       = $incident_old->CustomFields->c->cliente_bloqueado;
      $incident->CustomFields->c->serie_maq               = $incident_old->CustomFields->c->serie_maq;
      $incident->CustomFields->c->numero_delfos           = $incident_old->CustomFields->c->numero_delfos;
      $incident->CustomFields->c->order_number_om_ref     = $incident_old->CustomFields->c->order_number_om_ref;
      $incident->CustomFields->c->shipping_instructions   = $incident_old->CustomFields->c->shipping_instructions;
      
      $incident->CustomFields->c->direccion_incorrecta    = $incident_old->CustomFields->c->direccion_incorrecta;
      $incident->CustomFields->c->direccion_correcta      = $incident_old->CustomFields->c->direccion_correcta;
      $incident->CustomFields->c->codigo_error            = $incident_old->CustomFields->c->codigo_error;
      $incident->CustomFields->c->equipo_detenido_cliente = $incident_old->CustomFields->c->equipo_detenido_cliente;
      $incident->CustomFields->DOS->Direccion             = $incident_old->CustomFields->DOS->Direccion;
      $incident->Asset                                    = $incident_old->Asset ;
      $incident->CustomFields->c->ar_flow                 = $incident_old->CustomFields->c->ar_flow;
      $incident->CustomFields->OP->Incident               = $incident_old;
      self::insertPrivateNote($incident_old,'Incidente Creado  ['. $incident->ReferenceNumber .']'  );
      $incident->Save();
      
      
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
          /*  $incident->Threads                   = new RNCPHP\ThreadArray();
            $incident->Threads[0]                = new RNCPHP\Thread();
            $incident->Threads[0]->EntryType     = new RNCPHP\NamedIDOptList();
            $incident->Threads[0]->EntryType->ID = 1; // 1: nota privada
            $incident->Threads[0]->Text          = "Error " . $err->getMessage();
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            */
            $incident->Save(RNCPHP\RNObject::SuppressAll);
            return FALSE;
        }
    }





}
