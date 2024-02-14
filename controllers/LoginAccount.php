<?php

namespace Custom\Controllers;

class LoginAccount extends \RightNow\Controllers\Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function connect()
    {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (empty($username) or empty($password)) {
            $this->data['errorLogin'] = 'Usuario y contraseña son obligatorios';
            echo json_encode($this->data);
            return false;
        }

        $this->load->model('custom/AccountLogin');
        $result = $this->AccountLogin->loginSessionAccount($username, $password);
        $CI = get_instance();
        $CI->session->setSessionData(array("username" =>$username)) ;
        $CI->session->setSessionData(array("password" =>$password)) ;

        if ($result === true)
        {
          $account_values = $this->session->getSessionData('Account_loggedValues');
          echo json_encode($result);
          return false;
        }
        else
        {
            $numberError = $this->AccountLogin->getNumberError();
            switch ($numberError) {
              case 1:
              case 2:
              case 3:
                $this->data['errorLogin'] = 'Su Usuario o Password no es el correcto';
                break;
              case 4:
                $this->data['errorLogin'] = 'Error inesperado, favor comunicarse con el administrador';
                break;
              case 5:
                $this->data['errorLogin'] = 'Su cuenta se encuentra Bloqueada, favor comunicarse con el administrador';
                break;
              case 6:
                $this->data['errorLogin'] = 'Su cuenta se encuentra Bloqueada, favor comunicarse con el administrador';
                break;
              case 7:
              case 8:
                $this->data['errorLogin'] = 'No tiene contacto asignado a su cuenta, favor comunicarse con el administrador';
                break;
              case 9:
                $this->data['errorLogin'] = 'Su perfil no tiene acceso al portal de reparación';
                break;
              default:
                $this->data['errorLogin'] = 'Error inesperado, favor comunicarse con el administrador';
                break;
            }
            echo json_encode($this->data);
            return false;
        }


    }

    public function disconnect()
    {
      $this->load->model('custom/AccountLogin');
      $this->AccountLogin->disconnect();
      echo json_encode(true);
      return false;
    }
}
