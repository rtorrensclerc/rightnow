<?php

namespace Custom\Controllers;

use RightNow\Connect\v1_3 as RNCPHP;

class Auth extends \RightNow\Controllers\Base
{
    public $patterns = array();

    public $definitionsKey = null;

    // public $definitionsKey = array(
    //   'length' => array(
    //       'bounds' => 'min',
    //       'count' => 8
    //   ),
    //   'occurrences' => array(
    //       'bounds' => 'max',
    //       'count' => 0
    //   ),
    //   'old' => array(
    //       'bounds' => 'max',
    //       'count' => 1
    //   ),
    //   'repetitions' => array(
    //       'bounds' => 'max',
    //       'count' => 0
    //   ),
    //   'lowercase' => array(
    //       'bounds' => 'min',
    //       'count' => 0
    //   ),
    //   'specialAndDigits' => array(
    //       'bounds' => 'min',
    //       'count' => 1
    //   ),
    //   'special' => array(
    //       'bounds' => 'min',
    //       'count' => 0
    //   ),
    //   'uppercase' => array(
    //       'bounds' => 'min',
    //       'count' => 1
    //   )
    // );

    function __construct()
    {
        parent::__construct();

        $contact = RNCPHP\Contact::fetch(140015);
        // $this->definitionsKey = 'hola';
        $this->definitionsKey = \RightNow\Utils\Validation::getPasswordRequirements($contact::getMetadata()->NewPassword);

        $this->patterns['lowercase']        = 'abcdefghijklmnopqrstuvwxyz';
        $this->patterns['uppercase']        = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $this->patterns['special']          = '!@#$%^&*()_-=+;:,.?';
        $this->patterns['specialAndDigits'] = '1234567890';


        $this->load->model('custom/Email');
        $this->load->model('custom/Contact');
    }

    /*
  * Método para generar un una contraseña aleatoria y enviarla al correo del usuario en base a su `Login`
  */
    public function recoveryPassword($login = null)
    {
        \RightNow\Libraries\AbuseDetection::check();

        if (!$login)
        {
            $json      = $this->input->post('form');
            $a_form    = json_decode($json, TRUE);
            $a_form    = $a_form[0];
            $a_json    = json_decode($a_form["value"], TRUE);
            $login     = trim($a_json["data"]["login"]);
        }

        $contact = $this->Contact->getContactInstanceByLogin($login);

        // var_dump($login);
        // var_dump($contact);
        // exit;
        if ($contact)
        {
            // $contact->CustomFields->c->temporal_key = 'S2YJJBG.';
            $contact->CustomFields->c->temporal_key = $this->generateRandomString();
            $contact->NewPassword                   = $contact->CustomFields->c->temporal_key;

            $dateVigency = new \DateTime();
            $dateVigency->modify('+1 days');
            $contact->CustomFields->c->temporal_key_vigency = $dateVigency->getTimestamp();
            
            $contact->Save();
            
            $this->Email->notifyTemporalKey($contact, "Restablecimiento de Contraseña - Mi Dimacofi [" . date("d-m-Y H:i:s", time()) . "]", TRUE);
            
            self::insertPrivateNote($contact, "Se ha enviado un correo electrónico con una contraseña temporal para iniciar sesión.");
        }

        // Por Ethical Hacking se debe enviar el mismo mensaje de éxito para evitar que un atacante pueda saber si el usuario existe o no.
        $a_response = array(
            "result"  => array(
                "message"          => "Se ha enviado un correo electrónico con una contraseña temporal para iniciar sesión.",
                "redirectOverride" => "/app/home/msg/recovery",
                "sessionParam"     => ""
            )
        );

        echo json_encode($a_response);
    }

    public function getRecoveryPassword()
    {

        try
        {
            \RightNow\Libraries\AbuseDetection::check();

            if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0)
            {
                throw new \Exception('El método de la solicitud no es válido', 6);
            }

            //Vertificar que CONTENT_TYPE sea json
            $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
            if (strcasecmp($contentType, 'application/json') != 0)
            {
                throw new \Exception('La información recibida no indica en la cabecera ser un JSON válido', 4);
            }

            // Recibe la información
            $content = trim(file_get_contents("php://input"));

            // Verificar si la información es un json correcto.
            $a_content = json_decode($content, true);
            // Verificar si el contenido es un array válido.
            if (!is_array($a_content))
                throw new \Exception('La información recibida no es de tipo JSON válido', 4);

            // Valida que el array contenga información    
            if (count($a_content) === 0)
            {
                throw new \Exception("El arreglo no puede estar vacío");
            }

            // Aplica una segunda validación para que los elementos del array no sean nulos
            foreach ($a_content as $elemento)
            {
                if (empty($elemento))
                {
                    throw new \Exception("El array no puede contener elementos nulos");
                }
            }

            // Consulta el servicio recoveryPassword con el valor obtenido mediante POST
            $contact =  RNCPHP\Contact::first("Login ='" . $a_content['login'] . "'");

            if ($contact->ID == null)
            {
                throw new \Exception("Ha ocurrido un error");
            }

            $response = $this->recoveryPassword($contact->Login);
           
            if ($response === false)
            {
                throw new \Exception("Ha ocurrido un error - Reintentar");
            }
            return $response;
        }
        catch (\Exception $e)
        {
            //header('Content-Type: application/json');
            $a_result = array("resultado" => false,  "respuesta" => array("codigo" => $e->getCode(), "mensaje" => $e->getMessage()));
            self::insertPrivateNote($contact, $a_result);
            //echo json_encode($a_result);
        }
    }
    static function insertPrivateNote($contact, $textoNP)
    {
        try
        {
            $contact->Notes = new RNCPHP\NoteArray();
            $contact->Notes[0] = new RNCPHP\Note();
            $contact->Notes[0]->Channel = new RNCPHP\NamedIDLabel();
            $contact->Notes[0]->Text = $textoNP;
            $contact->save(RNCPHP\RNObject::SuppressAll);
        }
        catch (RNCPHP\ConnectAPIError $err)
        {
            $contact->Subject = "Error" . $err->getMessage();
            $contact->Save(RNCPHP\RNObject::SuppressAll);
            return false;
        }
    }

    /**
     * Obtiene valores aleatoreos base a un diccionario
     * {}
     * Tipos de diccionario
     * 'lowercase'        : Caracteres en minúscula
     * 'uppercase'        : Caracteres en mayúscula
     * 'special'          : Caracteres especiales
     * 'specialAndDigits' : Números y caracteres especiales
     */
    public function generateRandomString()
    {
        $key = '';

        // access item lowercase of $this->definitionsKey
        // var_dump($this->definitionsKey['length']['count']);
        // var_dump($this->definitionsKey);
        // echo "<pre>";
        // print_r($this->definitionsKey);
        // echo "</pre>";
        // echo $this->definitionsKey;
        // exit;

        // recorrer $patterns
        foreach ($this->patterns as $pattern_key => $pattern_value)
        {
            $key .= $this->getRandomString($pattern_key, $this->definitionsKey[$pattern_key]['count']);
        }

        $sizeKey = strlen($key);
        $maxSize = $this->definitionsKey['length']['count'] - $sizeKey;
        $maxSize = ($maxSize < 0) ? $maxSize * -1 : $maxSize;

        // Relleno de valores
        for ($i = 0; $i < $maxSize; $i++)
        {
            $key .= $this->getRandomString('uppercase', $this->definitionsKey[$pattern_key]['count']);
        }

        // echo $key;

        return $key;
    }

    /**
     * Obtiene un string aleatoreo de un diccionario
     */
    public function getRandomString($pattern_name, $length)
    {
        $key = '';

        $patternSize  = strlen($this->patterns[$pattern_name]) - 1;

        for ($j = 0; $j < $length; $j++)
        {
            $key .= $this->patterns[$pattern_name][mt_rand(0, $patternSize)];
        }

        return $key;
    }
}
