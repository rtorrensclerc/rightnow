<?php
namespace Custom\Models;

use RightNow\Connect\v1_3 as RNCPHP;

class Contact extends \RightNow\Models\Base
{
  public $error      = array('numberID' => null , 'message' => null);

  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Método para actualizar el perfilamiento de un contacto
   *
   * @param integer $contact_type
   * @param object $profiling
   * 
   * @return void
   */
  public function setProfiling($contactId, $profilingID, $json = null)
  {
    try 
    {
      // if (empty($profilingID))
      // {
      //   $this->error['message']  = 'Identificador de tipo de perfil vacío.';
      //   return false;
      // }

      $contact                                       = RNCPHP\Contact::fetch($contactId);
      if ($profilingID !== null)
        $contact->CustomFields->PROF->ProfileType = RNCPHP\PROF\Type::fetch($profilingID);
      else
        $contact->CustomFields->PROF->ProfileType  = null;
      $contact->CustomFields->c->json_custom_profile = $json;
      $contact->Save(RNCPHP\RNObject::SuppressAll);
      return $contact;
    } 
    catch (RNCPHP\ConnectAPIError $err) 
    {
      $this->error['message']  = "Codigo Profiling : ".$err->getCode()." ".$err->getMessage();
      return false;
    }
  }

   /**
   * Obtiene la lista de tipos de Perfil
   */
  public function getProfileTypes()
  {
    try 
    {
      //$menu = RNCPHP\ConnectAPI::getNamedValues('RightNow\\Connect\\v1_3\\PROF\\Type');
      $menues = RNCPHP\PROF\Type::find("ID is not null");
      $a_menu = array();
      foreach ($menues as $menu)
      {
        $a_tempMenu["ID"] = $menu->ID;
        $a_tempMenu["name"] = $menu->Name;
        $a_menu[]   = $a_tempMenu;
      }
      return $a_menu;
    } 
    catch (RNCPHP\ConnectAPIError $err) 
    {
      $this->error['message'] = "Código: ".$err->getCode()." ".$err->getMessage();
      return false;
    }
  }

  public function get($c_id)
  {
    try {
  
      $contact = RNCPHP\Contact::fetch($c_id);

      return $contact;
    } catch (RNCPHP\ConnectAPIError $err) {
      $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
      return false;
    }
  }

  public function getContactById($id_contact)
  {
    try {
      $contact = RNCPHP\Contact::fetch($id_contact);

      return $contact;
    } catch (RNCPHP\ConnectAPIError $err) {
      $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
      return false;
    }
  }

  public function getContactByRut($rut)
  {
    try {
      $response;
      $contact_array = RNCPHP\Contact::find("CustomFields.c.rut = '$rut'");

      if (count($contact_array) > 0) {
        $id_contact;
        foreach ($contact_array as $cont) {
          $contact = $cont;
          break;
        }
        $response = $contact->ID;
      } else {
        $response = false;
      }
      return $response;
    } catch (RNCPHP\ConnectAPIError $err) {
      $this->error['message']  = "Codigo : ".$err->getCode()." ".$err->getMessage();
      return false;
    }
  }

  /**
   * Obtiene la lista del menu del contacto
   */
  public function getContactMenu($nameField)
  {
    try {
      $menu = RNCPHP\ConnectAPI::getNamedValues('RightNow\\Connect\\v1_3\\Contact', $nameField);
      return $menu;
    } catch (RNCPHP\ConnectAPIError $err) {
      $this->error['message'] = "Código: ".$err->getCode()." ".$err->getMessage();
      return false;
    }
  }

  
  /**
   * Establece los valores del usuario
   */
  public function setUser($a_user)
  {
    try {
      $c_id        = $a_user['u_id'];
      $name        = $a_user['name'];
      $last_name     = $a_user['last_name'];
      $email       = $a_user['email'];
      $login       = $a_user['email'];
      $rut         = $a_user['rut'];
      $phone       = $a_user['phone'];
      $profileId    = $a_user['profile'];
      $password      = $a_user['password'];
      $enabled_suppplier = $a_user['enabled_supplier'];
      $blocked       = $a_user['blocked'];
      $organization    = $a_user['organization_id'];

      if (!empty($c_id) and ($c_id > 0)) {
        $contact = RNCPHP\Contact::fetch($c_id);

        // Campo bloqueado
        if (is_bool($blocked)) {
          $contact->CustomFields->c->blocked = $blocked;
        }
      } else {
        $contact               = new RNCPHP\Contact();
        $contact->Emails           = new RNCPHP\EmailArray();
        $contact->Emails[0]          = new RNCPHP\Email();
        $contact->Emails[0]->AddressType   = new RNCPHP\NamedIDOptList();
        $contact->Emails[0]->AddressType->ID = 1;
        //setear Organización
        $contact->Organization         = RNCPHP\Organization::fetch($organization);
      }

      $contact->Phones         = new RNCPHP\PhoneArray();
      $contact->Phones[0]      = new RNCPHP\Phone();
      $contact->Phones[0]->Number  = $phone;
      $contact->Emails[0]->Address   = $email;
      $contact->Phones[0]->PhoneType = new RNCPHP\NamedIDOptList();
      $contact->Phones[0]->PhoneType->LookupName = 'Teléfono de oficina';

      $contact->Name->First      = $name;
      $contact->Name->last       = $last_name;
      $contact->Login        = $login;
      $contact->CustomFields->c->rut = $rut;
      $contact->ContactType->ID    = 6;

      if (!empty($profileId))
      {
        $contact->CustomFields->PROF->ProfileType = RNCPHP\PROF\Type::fetch($profileId);
      }
      


      // if (empty($contacTypeID)) {
      //   $contact->ContactType->ID    = 6;
      // } else {
      //   $contact->ContactType->ID    = $contacTypeID;
      // }

      if (!empty($password)) {
        $contact->NewPassword = $password;
      }

      // if (is_bool($enabled_suppplier)) {
      //   $contact->CustomFields->c->enabled_supplier = $enabled_suppplier;
      // }

      $contact->save();

      return true;
    } catch (RNCPHP\ConnectAPIError $err) {
      $this->error['message'] = "Código: ".$err->getCode()." ".$err->getMessage();
      return false;
    }
  }

  public function getContactByLogin($login)
  {
    try {
      $contact_login = RNCPHP\Contact::first("Login = '$login'");

      if (!empty($contact_login)) {
        return $contact_login->ID;
      } else {
        return false;
      }
    } catch (RNCPHP\ConnectAPIError $err) {
      $this->error = "Codigo: ".$err->getCode()." ".getMessage();
      return false;
    }
  }

  public function getContactInstanceByLogin($login)
  {
    try {
      $contact_login = RNCPHP\Contact::first("Login = '$login'");

      if (!empty($contact_login)) {
        return $contact_login;
      } else {
        return false;
      }
    } catch (RNCPHP\ConnectAPIError $err) {
      $this->error = "Codigo: ".$err->getCode()." ".getMessage();
      return false;
    }
  }

  public function getContactByEmail($email)
  {
    try {
      $contact_email = RNCPHP\Contact::first("Emails.Address = '$email'");

      if (!empty($contact_email)) {
        return $contact_email->ID;
      } else {
        return false;
      }
    } catch (RNCPHP\ConnectAPIError $err) {
      $this->error['message'] = "Codigo: ".$err->getCode()." ".getMessage();
      return false;
    }
  }

  public function getContact($c_id)
  {
    try {
      $contact = RNCPHP\Contact::fetch($c_id);

      if (!empty($contact)) {
        return $contact;
      } else {
        return false;
      }
    } catch (RNCPHP\ConnectAPIError $err) {
      $this->error['message'] = "Codigo: ".$err->getCode()." ".getMessage();
      return false;
    }
  }

  public function getLastError()
  {
    return $this->error['message'];
  }
}
