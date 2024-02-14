<?php
namespace Custom\Models\ws;
use RightNow\Connect\v1_2 as RNCPHP;

class TecnicosModel extends \RightNow\Models\Base
{
    public $error = '';

    function __construct()
    {
        parent::__construct();
    }

    public function getTickets($id_tecnico)
    {
        try
        {
            //Inicio - Lineas Nuevas
            $account_tecnico = RNCPHP\Account::first("CustomFields.c.resource_id = ".$id_tecnico);
            $array_obj       = RNCPHP\Incident::find("AssignedTo.Account.ID = {$account_tecnico->ID} And StatusWithType.StatusType.ID != 2");
            //Fin - lineas Nuevas

            //$array_obj = RNCPHP\Incident::find("AssignedTo.ParentAccount.CustomFields.c.resource_id = ".$id_tecnico. " And StatusWithType.StatusType.ID != 2");
            return $array_obj;
        }
        catch ( RNCPHP\ConnectAPIError $err )
        {

            $this->error = "Codigo : ".$err->getCode()." ".$err->getMessage();
            return false;
        }
    }

    public function getLastError()
    {
        return $this->error;
    }


}
