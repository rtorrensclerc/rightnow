<?php

namespace Custom\Models;

use RightNow\Connect\v1_3 as RNCPHP;

/**
 * Clase para enviar correos electrónicos.
 */
class Email extends \RightNow\Models\Base
{
  public $error = '';

  /**
   * Constructor
   */
  public function __construct()
  {
      parent::__construct();
  }

  /**
   * Establece la plantilla para el correo electrónico.
   * 
   * @param object $contact
   * @param string $subject_name
   * @param string $text
   * 
   * @return boolean
   */
  function setTemplateNonIncident($text, $contact = NULL)
  {
    if($contact)
    {
      $html_final =  "<div style='FONT-FAMILY:Arial,sans-serif'>
        <div>
          <table style='FONT-FAMILY:Arial,sans-serif' cellspacing='0' cellpadding='0' width='550' align='center' border='0'>
            <tbody>
              <tr>
                <td><img alt='Image' border='0' height='109' src='" . \RightNow\Utils\Url::getOriginalUrl(false) . "/euf/assets/images/email/header.png'
                    width='550' class='CToWUd'></td>
              </tr>
              <tr>
                <td style='BACKGROUND-COLOR:#fff'>
                  <table style='FONT-FAMILY:Arial,sans-serif;WIDTH:530px' cellspacing='0' cellpadding='0' bgcolor='white'>
                    <tbody>
                      <tr>
                        <td>
                          <p style='MARGIN:0px 0px 0px 15px'><span></span></p>
                          {$text}
                          <div><span>&nbsp;</span></div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
              <tr>
                <td><img alt='Image' height='47' src='" . \RightNow\Utils\Url::getOriginalUrl(false) . "/euf/assets/images/email/footer.png'
                    width='550' class='CToWUd'>&nbsp;</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>";

      return $html_final;
    }
  }

  /**
   * Notifica una contraseña temporal
   */
  public function notifyTemporalKey($contact, $subject_name)
  {
    try 
    {
      if (count($contact->Emails) > 0) {
        $mm        = new RNCPHP\MailMessage();
        $to_emails = array();
        
        foreach ($contact->Emails as $email) 
        {
          array_push($to_emails, $email->Address);
        }
  
        $mm->To->EmailAddresses = $to_emails;
        $mm->Subject            = $subject_name;
  
        // Establece el mensaje
        $text = "<div><span>Se estableció una contraseña temporal, para ingresar a la plataforma, favor utilice esta contraseña en el portal para establecer una nueva contraseña</span></div>";
        $text .= "<span>&nbsp;</span>";
        $text .= "<div><span style=\"font-size: 23px;\"><strong>{$contact->CustomFields->c->temporal_key}</strong></span></div>";
        $text .= "<span>&nbsp;</span>";
        $text .= "<div><span>Tenga en cuenta que esta contraseña caducará a las 24 horas de su envío.</span></div>";
  
        $mm->Body->Html                      = $this->setTemplateNonIncident($text, $contact);
        $mm->Options->IncludeOECustomHeaders = FALSE;
  
        $mm->send();
  
        return TRUE;
      } else {
        $contact->Notes = new RNCPHP\NoteArray();
        $contact->Notes[0] = new RNCPHP\Note();
        $contact->Notes[0]->Channel = new RNCPHP\NamedIDLabel();
        $contact->Notes[0]->Text = "Restablecer contraseña: El contacto no tiene ninguna dirección de correo electrónico asociada.";
        $contact->save();

        return FALSE;
      }
    } 
    catch (\Exception $e) 
    {
      $this->lastError = $e->getMessage();
      return FALSE;
    }
  }

}
