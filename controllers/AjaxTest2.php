<?php

namespace Custom\Controllers;
use RightNow\Connect\v1_3 as RNCPHP;


class AjaxTest2 extends \RightNow\Controllers\Base
{
  //This is the constructor for the custom controller. Do not modify anything within
  //this function.
  public function __construct()
  {
    parent::__construct();
  }

  function test_incident($id)
  {
    $incident = RNCPHP\Incident::fetch($id);

    echo '<pre>';
    echo ($incident->Source->ID === 3019)?'Es Web':'No es Web';
    echo '</pre>';

    echo '<pre>';
    print_r($incident->Source->LookupName);
    echo '</pre>';

    echo '<pre>';
    print_r($incident->Source->Parents);
    echo '</pre>';
  }


  function testToken()
  {
    load_curl();

    $postArray = array("grant_type" => "client_credentials");
    $url = "http://api-test.dimacofi.cl/token";
    $userpass = "05jZI6AAAbxfiKoXFtknZNCsBo0a:pEwAae6oMO73bUzqPK4FamCA908a";

    # Form data string
    if (is_array($postArray)) {
        $postString = http_build_query($postArray, '', '&');
    }

    //load_curl();
    $ch = curl_init($url);

    # Setting our options
    curl_setopt($ch, CURLOPT_USERPWD, $userpass);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $info = curl_getinfo($ch);
        echo "Error Curl ".curl_error($ch);
        curl_close($ch);
    }

    if ($response != false) {
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($statusCode != '200') {
            echo 'No se pudo resolver la petición a la URL, codigo de Error: '. $statusCode;
            return false;
        } else {
            echo $response;
        }
    }
    else {
        curl_close($ch);
        echo 'Pagina no devuelve nada '.$response;
        return false;
    }
  }

  
  function redirectWepPay()
  {
    // echo "Test";
    print_r($_POST);

    $url = $_POST['url'];

    // header('HTTP/1.1 307 Temporary Redirect');
    // header("Location: $url");
  }


  function testService()
  {
    $this->load->model('custom/ServicesIaxis');
    $this->load->helper('utils');

    $policyNumber = 30501687;

    $a_parameter['numeroPoliza']   = (empty($policyNumber))?null:$policyNumber;
    $a_parameter['numeroItem']     = null;
    $a_parameter['codEstado']      = null;
    $a_parameter['codRamo']        = null;
    $a_parameter['patente']        = null;
    $a_parameter['motor']          = null;
    $a_parameter['chasis']         = null;
    $a_parameter['rutAsegurado']   = ($rut)?$rut[0]:null;
    $a_parameter['dvAsegurado']    = ($rut)?$rut[1]:null;
    $a_parameter['rutContratante'] = null;
    $a_parameter['dvContratante']  = null;

    //$a_json = json_encode($a_parameter);

    header('Content-Type: application/json');
    $result = $this->ServicesIaxis->getListPolicy($a_parameter);

    // Formando estructura de respuesta
    $response          = new \stdClass();
    $response->success = ($result) ? true : false;
    $response->list    = $result;

    if ($result) {
        $response->message = "Lista obtenida con éxito";
    } else {
        $response->message = $this->ServicesIaxis->getError();
    }

    // Exponiendo la respuesta
    echo json_encode($response);
  }


}
