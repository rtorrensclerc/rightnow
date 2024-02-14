<?php
namespace Custom\Widgets\assistance;

class SpecialTicket extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);

        $this->setAjaxHandlers(array(
            'sendCSV_ajax_endpoint' => array(
                'method'      => 'handle_sendCSV_ajax_endpoint',
                'clickstream' => 'sendCSV_ajax_endpoint',
            )
        ));
        $this->CI->load->helper('utils');
        $this->CI->load->model('custom/GeneralServices');
    }
 /**
     * Recibe la información CSV de la carga masiva
     *
     * @param array $params Get / Post parameters
     */
    function handle_sendCSV_ajax_endpoint($params)
    {
        header('Content-Type: application/json');

        // Parámetros
        $data         = json_decode($params['data']);
        $csv          = $data->data;
        
        $a_data       = parserTextCSV($csv);
        $hhs= json_encode($a_data);
        $header       = $a_data["header"];
        $a_csv        = $a_data["csv"];
        $a_no_errors = array();
    foreach ($a_csv as $key => $line)
    {
      $a_temp['errors']        = array();
      $a_temp['ID_HH']            = $line["HH"];
      $a_temp['ID_TECNICO']            = $line["ID_TECNICO"];
      $a_no_errors[]             = $a_temp;
    }
    $response = new \stdClass;
    $response->no_errors = $a_no_errors;
    $response->success   = true;
    $DatosTickets = array();

  

    foreach($response->no_errors as $key => $equipo)
    {
        $DatosTicket = $this->CI->GeneralServices->CreateSpecialService($equipo['ID_HH'],$equipo['ID_TECNICO']);
        
        $DatosTickets[]=$DatosTicket;
    }
    $response->Tickets=$DatosTickets;

    echo json_encode($response);
}

    function getData() {
        return parent::getData();

    }
}