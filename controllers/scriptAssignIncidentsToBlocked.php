<?php
namespace Custom\Controllers;

use RightNow\Connect\v1_3 as RNCPHP;

class scriptAssignIncidentsToBlocked extends \RightNow\Controllers\Base
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('custom/IncidentGeneral');
    }

    public function getContactsOrigins()
    {
        try 
        {
            set_time_limit(0);
            //se obtien contactos bloqueado 
            $a_contacts_blockeds = RNCPHP\Contact::find("CustomFields.c.blocked.ID = 1 AND Disabled = 1 AND CustomFields.c.contact_original_id NOT NULL LIMIT 4500");

            
            //entro a los contactos para traerme el id de los mismos 
            foreach ($a_contacts_blockeds as $key => $contact) 
            {
                $date = date("h:i:s/A");

                $contact = RNCPHP\Contact::fetch($contact->ID);
                //se obtienen los incidentes de los contactos bloqueados
                $a_incidents = RNCPHP\Incident::find("PrimaryContact.Contact.ID = {$contact->ID}");
                //se obtienen los contactos originales desde los contactos bloqueados 
                $original_contact = RNCPHP\Contact::fetch($contact->CustomFields->c->contact_original_id);
                //se obtienen las oportunidades desde el contacto bloqueado o duplicado en este caso
                $a_opportunity = RNCPHP\Opportunity::find("PrimaryContact.Contact.ID = {$contact->ID}");
              
                if($contact->CustomFields->c->opportunities_process === FALSE || $contact->CustomFields->c->opportunities_process === NULL)
                {
                    if(count($a_opportunity) < 1)
                    {
                        $contact->CustomFields->c->opportunities_process = TRUE;
                        $contact->Save(RNCPHP\RNObject::SuppressAll);
                        continue;
                    }
                    else
                    {
                        echo "<pre>";
                        echo $key . " ";
                        echo "ID contacto: ".$contact->ID . " ";
                        echo "Cantidad de Oportunidades: ".count($a_opportunity);
                        echo "</pre>";
        
                        foreach ($a_opportunity as $key => $opportunity) 
                        {
                            echo "<pre>";
                            echo "La oportunidad CON ID : ".$opportunity->ID." pertence al ID de contacto bloqueado : ".$opportunity->PrimaryContact->Contact->ID." ID Original ".$opportunity->PrimaryContact->Contact->CustomFields->c->contact_original_id;
                            echo "</pre>";
                            //se asocian las oportunidades de los contactos duplicados o bloqueados a los contactos originales
                            $opportunity->PrimaryContact->Contact = $original_contact;
                            $opportunity->Save(RNCPHP\RNObject::SuppressAll);
                            echo "<pre>";
                            echo "[{$date}] - La oportunidad con ID : ".$opportunity->ID." Fue asignada al ID de contacto original : ".$opportunity->PrimaryContact->Contact->ID." Incidentes procesados : ".$contact->CustomFields->c->incidents_process;
                            echo "</pre>";
    
                            if($key == count($a_opportunity) - 1)
                            {
                                $contact->CustomFields->c->opportunities_process = TRUE;
                                $contact->Save(RNCPHP\RNObject::SuppressAll);
                            }
                        }
                    }
                }

                if($contact->CustomFields->c->incidents_process === FALSE || $contact->CustomFields->c->incidents_process === NULL)
                {
                    if(count($a_incidents) < 1)
                    {
                        // Agregar el .invalid al correo y además concaternarle el ID del contacto = Resultando example@example.invalid.{ID}
                        // Al login igualmente aplicar la lógica anterior -> En dimacofi el login es el correo electrónico.
                        if(!empty($contact->Login))
                        {
                            $contact->Login                              .= ".invalid." . $contact->ID;
                        }
                        if(!empty($contact->Emails[0]->Address))
                        {
                            $contact->Emails[0]->Address                 .= ".invalid." . $contact->ID;
                        }
                        $contact->CustomFields->c->incidents_process = TRUE;
                        $contact->Save(RNCPHP\RNObject::SuppressAll);
                        continue;
                    }
                    else
                    {
                        echo "<pre>";
                        echo $key . " ";
                        echo "ID contacto: ".$contact->ID . " ";
                        echo "Cantidad de Incidentes: ".count($a_incidents);
                        echo "</pre>";
    
                        foreach ($a_incidents as $key => $incident) 
                        {
                            
                            echo "<pre>";
                            echo "ID Contacto bloqueado ".$contact->ID." esta asignado al incidente : ".$incident->ID;
                            echo "</pre>";
                            //se asocian los incidentes de los contactos duplicados o bloqueados a los contactos originales
                            $incident->PrimaryContact = $original_contact;
                            $incident->Save(RNCPHP\RNObject::SuppressAll);
                            $comments = "[{$date}] - ID de contacto original : ".$incident->PrimaryContact->ID." fue asignado al incidente : ".$incident->ID." Incidentes procesados : ".$contact->CustomFields->c->incidents_process;
                            echo "<pre>";
                            echo $comments;
                            echo "</pre>";
                            $this->IncidentGeneral->insertPrivateNote($incident->ID, $comments, null);
    
                            if($key == count($a_incidents) - 1)
                            {
                                if (!empty($contact->Login)) 
                                {
                                    $contact->Login                              .= ".invalid." . $contact->ID;
                                }
                                if(!empty($contact->Emails[0]->Address))
                                {
                                    $contact->Emails[0]->Address                 .= ".invalid." . $contact->ID;
                                }
                                $contact->CustomFields->c->incidents_process = TRUE;
                                $contact->Save(RNCPHP\RNObject::SuppressAll);
                            }
                        }
                    }
                }
            }
        } 
        catch (\Exception $err) 
        {
            $this->error['message']  = "Query Errror ".$err->getCode()." ".$err->getMessage();
            $this->error['numberID'] = 1;
            return false;
        }
    }

}
