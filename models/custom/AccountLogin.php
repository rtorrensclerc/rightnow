<?php
namespace Custom\Models;
use RightNow\Connect\v1_3 as RNCPHP;

class AccountLogin extends \RightNow\Models\Base
{
   //  private $idAccount = null;
   //  private $idContact = null;
    private $messageError     = array("id" => null, "message" => null);
    private $organizationID   = 9056; //ID de Rightnow para Dimacofi Servicios S.A
    private $a_validProfileID = array(4,7, 31, 6, 21, 15, 13, 35, 32, 5,41,12,64,65,69,70,71,76,84,86); // ID de perfiles validos para el Acceso al Portal de Reparación.

    function __construct()
    {
        //Load CURL .so file

        //Verificar si esta función esta bien
        if (!function_exists("\curl_init")){
           \load_curl();
        }
        //\load_curl();
        parent::__construct();
    }

    public function loginSessionAccount($user, $password)
    {
      $is_logged = $this->CI->session->getSessionData('Account_isLogged');
      if ($is_logged !== true )
      {
        if ($this->validAccount($user, $password) == true)
        {
          $a_values = $this->getSessionValuesAccount($user);
          if ($a_values != false)
          {
            //Si la cuenta esta bloqueda no se permite el Login
            if ($a_values['locked']  === true)
            {
              $this->messageError['id']           = 5;
              $this->messageError['message']      = 'Cuenta desactivada, bloqueada o sin permisos';
              return false;
            }

            // Si el Perfil, no se encuentra en la lista de perfiles validos
            if (in_array($a_values['ProfileID'], $this->a_validProfileID ) != true)
            {
              $this->messageError['id']           = 9;
              $this->messageError['message']      = 'Perfil de Acceso no identificado';
              return false;
            }

            // Si no se encontró contacto relacionado, este se Crea.
            if (empty($a_values['ContactID']))
            {
              $result = $this->AssignContactToAccount($a_values['Login']);
              if ($result !== false)
              {
                $a_values['ContactID'] = $result;
              }
              else
              {
                return false;
              }
            }

            $this->CI->session->setSessionData(array('Account_isLogged'       => true));
            $this->CI->session->setSessionData(array('Account_loggedValues'   => $a_values ));
            //$this->CI->session->setFlashData('Account_loggedValues', $a_values);
            setcookie('Account_loggedValues', serialize($a_values) , 0, '/');
            setcookie('Account_isLogged', '1', 0, '/');

            return true;
          }
          else {
            $this->CI->session->setSessionData(array('Account_isLogged' => false));
            setcookie('Account_isLogged', '0', 0, '/');
            $this->messageError['id']      = 4; //Error 4: Query no encontro informaicón del contacto, o la Query Fallo
            return false;
          }
        }
        else {
          $this->CI->session->setSessionData(array('Account_isLogged' => false));
          setcookie('Account_isLogged', '0', 0, '/');
          return false;
        }
      }
      else
      {
        $this->messageError['id']      = 6; //Error 2: Json No valido
        $this->messageError['message'] = 'Ya se encuentra Logueado';
        return true;
      }
    }

    public function validAccount($user, $password)
    {
      if (!empty($user) and !empty($password)){
        $result = $this->loginCurl($user, $password);
        $array_value = json_decode($result, true);
        if ($array_value != null and is_array($array_value))
        {
          if (array_key_exists('result', $array_value))
          {
            if ($array_value['result'] == true)
              return true;
            else {
              $this->messageError['id']      = 1; //Error 1: Cuenta no valida
              $this->messageError['message'] = $array_value['message'];
              return false;
            }
          }
          else
          {
            $this->messageError['id']      = 2; //Error 2: Json No valido
            $this->messageError['message'] = 'Json no valido';
            return false;
          }
        }
        else
        {
          $this->messageError['id']      = 3; //Error 3: Cuenta no valida, Hereda el Message Error de Curl
          return false;
        }
      }
    }

    private function getSessionValuesAccount($user)
    {
      try
      {
        $Account = RNCPHP\Account::first("Account.Login = '{$user}'");

        if (is_object($Account) and $Account instanceof RNCPHP\Account )
        {
          $locked    =  $Account->Attributes->AccountLocked;
          $disabled1 =  $Account->Attributes->StaffAssignmentDisabled;
          $disabled2 =  $Account->Attributes->PermanentlyDisabled;
          if ($locked == true or $disabled1 == true or $disabled2 == true)
            $a_result['locked']  = true;
          else
            $a_result['locked']  = false;
          $a_result['FullName']  = $Account->LookupName;
          $a_result['Login']     = $Account->Login;
          $a_result['ID']        = $Account->ID;
          $a_result['ContactID'] = $Account->CustomFields->OP->Contact->ID;
          $a_result['ProfileID'] = $Account->Profile->ID;
          $a_result['Email']     = '';
          if (is_object($Account->Emails[0]))
            $a_result['Email'] = $Account->Emails[0]->Addr;
          else
            $a_result['Email'] = null;
          return $a_result;
        }
        else
        {
          $this->messageError['message'] = "No se encontraron resultados";
          return false;
        }
      }
      catch ( RNCPHP\ConnectAPIError $err )
      {
        $this->messageError['message'] = $err->getMessage();
        return false;
      }

    }

    public function AssignContactToAccount($Login)
    {
      $objAccount = RNCPHP\Account::first("Account.Login = '{$Login}'");

      if (is_object($objAccount) and ($objAccount instanceof RNCPHP\Account) and !empty($Login))
      {
        try
        {
          $contact = RNCPHP\Contact::first("Login = '{$Login}'");
          if (is_object($contact) and ($contact instanceof RNCPHP\Contact))
          {
            if ($contact->ContactType->ID != 1) //Distinto de contacto de tipo Cuenta.
            {
              $contact                  = new RNCPHP\Contact();
              $contact->Login           = $Login."_dimacofi";
              $contact->Name            = new RNCPHP\PersonName();
              $contact->Name->First     = $objAccount->Name->First;
              $contact->Name->Last      = $objAccount->Name->Last;
              $contact->Organization    = RNCPHP\Organization::fetch($this->organizationID);
              $contact->ContactType->ID = 1;
              if (!empty($objAccount->Emails[0]->Address)){
                $contact->Emails[0] = new RNCPHP\Email();
                $contact->Emails[0]->AddressType=new RNCPHP\NamedIDOptList();
                $contact->Emails[0]->AddressType->ID = 0; //Alt-1
                $contact->Emails[0]->Address = $objAccount->Emails[0]->Address;
              }
              $contact->save(RNCPHP\RNObject::SuppressAll);

              $objAccount->CustomFields->OP->Contact = $contact;
              $objAccount->Save(RNCPHP\RNObject::SuppressAll);
              return $objAccount->CustomFields->OP->Contact->ID;
            }
            else {
              return $contact;
            }
          }
          else
          {
            $contact                  = new RNCPHP\Contact();
            $contact->Login           = $Login;
            $contact->ContactType->ID = 1;
            $contact->Name            = new RNCPHP\PersonName();
            $contact->Name->First     = $objAccount->Name->First;
            $contact->Name->Last      = $objAccount->Name->Last;
            $contact->Organization    = RNCPHP\Organization::fetch($this->organizationID);
            if (!empty($objAccount->Emails[0]->Address)){
              $contact->Emails[0] = new RNCPHP\Email();
              $contact->Emails[0]->AddressType=new RNCPHP\NamedIDOptList();
              $contact->Emails[0]->AddressType->ID = 0; //Alt-1
              $contact->Emails[0]->Address = $objAccount->Emails[0]->Address;
            }
            $contact->save(RNCPHP\RNObject::SuppressAll);

            $objAccount->CustomFields->OP->Contact = $contact;
            $objAccount->Save(RNCPHP\RNObject::SuppressAll);
            return $objAccount->CustomFields->OP->Contact->ID;
          }
        }
        catch (RNCPHP\ConnectAPIError $err)
        {
          $this->messageError['id']      = 8; //Error 8: Algo fallo en la connect al asignar el contacto
          $this->messageError['message'] = $err->getMessage();
          //echo "ERRO: ".$err->getMessage();
          return false;
        }
      }
      else
      {
        $this->messageError['id']        = 7; //Error 9: Error en asignación de contacto.
        $this->messageError['message']   = 'Parametro de Cuenta o Login invalidos';
        return false;
      }
    }

    private function loginCurl($user, $password)
    {

      //$user = "Integer1";
      //$password = "_d1m4c0F11%";

      $ca_path = sprintf('/cgi-bin/%s.db/certs/ca.pem', \RightNow\Utils\Config::getConfig(DB_NAME));
      $url = "https://soportedimacoficl.custhelp.com/cgi-bin/soportedimacoficl.cfg/php/custom/accountlogin.php";
      //$url = \RightNow\Utils\Url::getOriginalUrl(false) . "/cgi-bin/soportedimacoficl.cfg/php/custom/accountlogin.php";
      $curl   = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, array('login'=> $user ,'password'=> $password));
      curl_setopt($curl, CURLOPT_CAINFO, $ca_path); //from above
      //curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
      $result = curl_exec($curl);
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

      if($result === false)
      {
        if ($httpCode != 200 and $httpCode != 302) {
            $this->messageError['message'] = "The Web Page Cannot Be Found, Code: ".$httpCode ;
            return false;
        }
        $this->messageError['message'] = "Error Number:".curl_errno($curl)." , Error String:".curl_error($curl);
        return false;
      }
      curl_close($curl);
      return $result;

    }

    public function getMessageError()
    {
      return $this->messageError['message'];
    }

    public function getNumberError()
    {
      return $this->messageError['id'];
    }

    public function disconnect()
    {
      $this->CI->session->setSessionData(array('Account_loggedValues' => ''));
      $this->CI->session->setSessionData(array('Account_isLogged' => false));
      //Parche cookies
      if (isset($_COOKIE['Account_loggedValues'])) {
          unset($_COOKIE['Account_loggedValues']);
          unset($_COOKIE['Account_isLogged']);
          setcookie('Account_loggedValues', null, -1, '/');
          setcookie('Account_isLogged', null, -1, '/');
          return true;
      } else {
          return false;
      }

    }



}
