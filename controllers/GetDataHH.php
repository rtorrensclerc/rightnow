<?php

namespace Custom\Controllers;
use RightNow\Connect\v1_2 as RNCPHP;
class GetDataHH extends \RightNow\Controllers\Base
{
    //This is the constructor for the custom controller. Do not modify anything within
    //this function.

    CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    // CONST URL_GET_HH   = "http://movil.dimacofi.cl/dts/rn_integracion/rntelejson.php";
    // CONST URL_GET_HH   = "http://200.68.12.190/dts/rn_integracion/rntelejson.php";
    //CONST URL_GET_HH   = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php";

    function __construct()
    {

        parent::__construct();
        //$this->__autoload('Blowfish');
        //$this->__autoload('ConnectUrl');

    }

    public function getHH()
    {
        $cfg2 = RNCPHP\Configuration::fetch( CUSTOM_CFG_WS_URL );

       
        $this->load->library('Blowfish', false);
        $this->load->library('ConnectUrl');
        //echo "test";
        $array_post     = array('usuario' => 'appmind',
                                'accion' => 'info_hh',
                                'datos'=> array('id_hh'=> '1348551')
                                );
        $json_data_post = json_encode($array_post);
        $json_data_post = $this->blowfish->encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
        //echo $json_data_post;
        $json_data_post = base64_encode($json_data_post);
        $postArray      = array('data' => $json_data_post);

        $result         = $this->connecturl->requestPost($cfg2->Value, $postArray);
        if ($result != false)
            echo $result;
        else
            echo $this->connecturl->getResponseError();
        //echo $this->connecturl->getResponseError();

    }

}
