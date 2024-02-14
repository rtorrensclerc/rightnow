<?php

/**
 * Skeleton incident cpm handler.
 */

namespace Custom\Libraries\CPM\v1;

use RightNow\Connect\v1_4 as RNCPHP;

require_once "ConnectUrl.php";

class RecoveryPasswordHandler
{
    public static function execute($runMode, $action, $contact, $cycle)
    {
        if ($cycle !== 0)
        {
            return;
        }

        try
        {

            $url    = "https://{$_SERVER['SERVER_NAME']}/cc/Auth/getRecoveryPassword";
            $a_params = array(
                "login" => $contact->Login
            );

            $json_params = json_encode($a_params, true);

            $contact->CustomFields->c->recovery_password_check = false;
            $contact->save(RNCPHP\RNObject::SuppressAll);
            //$response = ConnectUrl::requestCURLJsonRawDesarrollo($url, $json_params,1,true);//dev
            $response = ConnectUrl::requestCURLJsonRaw($url, $json_params);//Produccion
            self::insertPrivateNote($contact, "[" . date("Y/m/d H:i:s") . "][Contact: " . $contact->ID . "]: Se ha iniciado un cambio de clave");
           // self::insertPrivateNote($contact, "error:". ConnectUrl::getResponseError());
        }
        catch (RNCPHP\ConnectAPIError $err)
        {
            self::insertPrivateNote($contact, $err->getMessage() . "- Linea:" . $err->getLine());
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
}
