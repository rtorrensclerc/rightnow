<?php
namespace Custom\Widgets\login;

class RecoveryCredentials extends \RightNow\Widgets\EmailCredentials {
    function __construct($attrs) 
    {
        parent::__construct($attrs);

        $this->CI->load->model('custom/Contact');

        $this->setAjaxHandlers(array(
            'email_credentials_ajax' => array(
                'method' => 'sendEmailCredentials',
                'clickstream' => 'emailCredentials',
                'exempt_from_login_requirement' => true,
            ),
        ));

    }

    function getData() 
    {
        $credentialType = $this->data['attrs']['credential_type'];
        $this->data['js']['request_type'] = 'email' . ucfirst($credentialType);
        if ($credentialType === 'password') {
            // honor config: don't output password form
            if (!\RightNow\Utils\Config::getConfig(EU_CUST_PASSWD_ENABLED))
                return false;

            $this->data['js']['field_required'] = \RightNow\Utils\Config::getMessage(A_USERNAME_IS_REQUIRED_MSG);
        }
        else {
            $this->data['js']['field_required'] = \RightNow\Utils\Config::getMessage(AN_EMAIL_ADDRESS_IS_REQUIRED_MSG);
            if ($previouslySeenEmail = $this->CI->session->getSessionData('previouslySeenEmail')) {
                $this->data['email'] = $previouslySeenEmail;
            }
            else if ($urlParm = \RightNow\Utils\Url::getParameter('Contact.Emails.PRIMARY.Address')) {
                $this->data['email'] = $urlParm;
            }
        }

        $this->data['js'] = array(
                    'f_tok' => \RightNow\Utils\Framework::createTokenWithExpiration(0, $this->data['attrs']['challenge_required']),
                    //warn of form expiration five minutes (in milliseconds) before the token expires or the profile cookie or sessionID needs to be refreshed
                    'formExpiration' => 1000 * (min(60 * \RightNow\Utils\Config::getConfig(SUBMIT_TOKEN_EXP), $idleLength) - 300)
                );
        if (true) 
        {
            $this->data['js']['challengeProvider'] = \RightNow\Libraries\AbuseDetection::getChallengeProvider();
        }
    }

    /**
     * AJAX endpoint para el restablecimiento de contraseña
     * 
     * @param array $parameters
     */
      // function sendEmailCredentials($parameters) 
      // {
      //     \RightNow\Libraries\AbuseDetection::check();

      //     $method = ($this->data['attrs']['credential_type'] === 'password') ? 'sendResetPasswordEmail' : 'sendLoginEmail';

      //     // Modificación
      //     if($method === "sendResetPasswordEmail")
      //     {
      //         $a_login   = explode("-", $parameters["value"]);
      //         $contact = $this->CI->Contact->getContactByLogin($a_login[0]);

      //         if($contact === FALSE)
      //         {
      //             $response = array(
      //                 "message" => $this->CI->Contact->getLastError()
      //             );
      //             echo json_encode($response);

      //             return;
      //         }

      //         // Envía correo electrónico
      //         $email_sended = $this->CI->Auth->recoveryPassword($contact);

      //         if($email_sended === TRUE)
      //         {
      //             $response = array(
      //                 "message" => "Se ha enviado una contraseña temporal a su correo electrónico."
      //             );
      //             echo json_encode($response);
      //         }
      //     }
      // }
}
