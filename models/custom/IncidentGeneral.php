<?php
namespace Custom\Models;
use RightNow\Connect\v1_3 as RNCPHP;

class IncidentGeneral extends \RightNow\Models\Base
{
    public  $error          = array ('numberID' => null , 'message' => null);

    function __construct()
    {
        parent::__construct();
    }

    public function get($incidentId)
    {
      try
      {
        $incident = RNCPHP\Incident::fetch($incidentId);
        return $incident;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Query Errror ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }
    }

    /**
     * Crea un incidente.
     *
     * @param string  $subject.     Asunto que tendrá el incidente.
     * @param int     $product_id   ID del producto del incidente.
     * @param int     $category_id  ID de la categoria del incidente.
     * @param int     $contact_id   ID del contacto principal del incidente.
     * @return object|bool Retorna un objeto de tipo Incident o falso en caso de error.
     */
    public function createIncident( $subject, $product_id, $category_id, $contact_id )
    {
      try
      {
        $incident                 = new RNCPHP\Incident();
        $incident->Subject        = $subject;
        $incident->Product        = RNCPHP\ServiceProduct::fetch($product_id);
        $incident->Category       = RNCPHP\ServiceCategory::fetch($category_id);
        $incident->PrimaryContact = RNCPHP\Contact::fetch($contact_id);

        $incident->Save();

        return $incident;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Creación de Requerimiento.  Error: ".$err->getMessage();
        $this->error['numberID'] = $err->getCode();
        return false;
      }
    }

    /**
     * Función que cambia el producto, categoría y el estado de un incidente.
     *
     * @param int $incident_id.   Es el ID del incidente al cual se quiere hacer los cambios.
     * @param int $product_id.    Es el ID del producto que reemplazará al existente.
     * @param int $category_id.   Es el ID de la categoría que reemplazará a la existente.
     * @param int $status_id.     Es el ID del estado que reemplazará al existente.
     * @return bool.              Retornará verdadero en caso de éxito, de lo contrario retornará falso.
     */
    public function changeProductCategory($incident_id, $product_id, $category_id, $status_id)
    {
      try
      {
        $incident = RNCPHP\Incident::fetch($incident_id);

        if($incident instanceof RNCPHP\Incident)
        {
          $incident->Product                      =  RNCPHP\ServiceProduct::fetch($product_id);

          $incident->Category                     = RNCPHP\ServiceCategory::fetch($category_id);
          $incident->StatusWithType->Status->ID   = $status_id;
          //$incident->StatusWithType->Status->ID = $status_id;
          $incident->save();
          return true;
        }
        else
        {
          $this->error['message']  = " No se pudo obtener el requerimiento.";
          $this->error['numberID'] = 2;
          return false;
        }

      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Query Error ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }
    }

    public function insertPrivateNote($id, $comments, $entry_type_id = NULL)
    {
      try
      {
        $incident                                      = RNCPHP\Incident::fetch($id);
        $incident->Threads                             = new RNCPHP\ThreadArray();
        $incident->Threads[0]                          = new RNCPHP\Thread();
        $incident->Threads[0]->EntryType               = new RNCPHP\NamedIDOptList();
        if($entry_type_id)
          $incident->Threads[0]->EntryType->ID           = $entry_type_id;
        else
          $incident->Threads[0]->EntryType->ID           = 8; // 1: nota privada
        $incident->Threads[0]->Text                    = $comments;
        $incident->Save(RNCPHP\RNObject::SuppressAll);
        return true;
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Nota Privada : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }
    }

    public function FindIncident($rut)
    {
      try
      {
        $incidents =$this->data['js']['incidents']= RNCPHP\Incident::find(" CustomFields.DOS.Direccion.Organization.CustomFields.c.rut='" . $rut. "' and StatusWithType.status.ID not in(2,149)"); 
        return $incidents;
        
      }
      catch (RNCPHP\ConnectAPIError $err )
      {
        $this->error['message']  = "Nota Privada : ".$err->getCode()." ".$err->getMessage();
        $this->error['numberID'] = 1;
        return false;
      }
    }
    public function getError()
    {
      return $this->error['message'];
    }

}
