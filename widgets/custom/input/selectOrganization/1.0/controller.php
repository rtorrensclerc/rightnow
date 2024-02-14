<?php
namespace Custom\Widgets\input;

class selectOrganization extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);

        $this->setAjaxHandlers(array(
            'getOrganizationByName_ajax_endpoint' => array(
                'method'      => 'handle_getOrganizationByName_ajax_endpoint',
                'clickstream' => 'getOrganizationByName_ajax_endpoint',
            ),
        ));

        $this->CI->load->model('custom/Organization');
    }

    function getData() {

        $dataOrgs = $this->CI->Organization->getListByName('Chile');
        if ($dataOrgs === false)
        {
          echo $this->CI->Organization->getLastError();
        }
        else
        {
          $dataOrgs;
          /*
          echo "<pre>";
          print_r($dataOrgs);
          echo "</pre>";
          */
          $this->data['listOrganization'] = $dataOrgs;
        }
        return parent::getData();
    }

    /**
     * Handles the default_ajax_endpoint AJAX request
     * @param array $params Get / Post parameters
     */
    function handle_getOrganizationByName_ajax_endpoint($params)
    {
      header('Content-Type: application/json');
      // Parámetros
      $data                = json_decode($params['data']);
      $name                = $data->name;
      // Acción en el Modelo - get models
      $result =  $this->CI->Organization->getListByName($name);


      // Formando estructura de respuesta
      $response          = new \stdClass();
      $response->success = ($result)?true:false;
      $response->list    =  $result;

      if ($result != false)
        $response->message = "Lista obtenida con éxito";
      else
        $response->message = $this->CI->Organization->getLastError();

      // Exponiendo la respuesta
      echo json_encode($response);
    }
}
