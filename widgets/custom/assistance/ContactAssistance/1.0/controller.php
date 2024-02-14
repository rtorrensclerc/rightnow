<?php
namespace Custom\Widgets\assistance;

class ContactAssistance extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);
        $this->setAjaxHandlers(array(
            'getTipoDataSelected_ajax_endpoint' => array(
              'method'    => 'handle_getTipoDataSelected_ajax_endpoint',
              'clickstream' => 'getTipoDataSelected_ajax_endpoint',
            )
          ));
    }

    function getData() {
        $this->data['p_p'] =$this->data['attrs']['p_p'];
        $this->data['p_c'] = $this->data['attrs']['p_c'];
        $this->data['kw'] = $this->data['attrs']['kw'];
        
        return parent::getData();

    }
     /**
   * Obtiene la información del HH
   *
   * @param array $params Get / Post parameters
   */
  function handle_getTipoDataSelected_ajax_endpoint($params)
  {
    header('Content-Type: application/json');
    $response          = new \stdClass();
    $response->success = TRUE;
    $response->message  = "Información HH obtenida con éxito";

    // Exponiendo la respuesta
    echo json_encode($response);
  }
}