<?php
namespace Custom\Widgets\ppto;
require_once(get_cfg_var('doc_root').'/include/ConnectPHP/Connect_init.phph');
use RightNow\Connect\v1_3 as RNCPHP;

class viewItemsStock extends \RightNow\Libraries\Widget\Base {

    CONST KEY_BLOWFISH = "D3t1H6q0p6V7z8";
    //CONST URL_GET_HH   = "http://190.14.56.27/public/rn_integracion/rntelejson.php"; // TEST
    //CONST URL_GET_HH   = "http://190.14.56.27:8080/dts/rn_integracion/rntelejson.php"; // Produ
    CONST URL_GET_HH   = "http://129.151.120.248:8080/dts/rn_integracion/rntelejson.php"; // Prod OCI
    
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData()
    {
        $op_id = $_REQUEST['op'];
        $in_id = $_REQUEST['in'];
        if (empty($op_id) and empty($in_id))
        {
          $this->data['message'] = "Problemas en la recepción del ID ";
          return parent::getData();
        }

        if (!empty($_POST['enviar']))
        {
          if(empty($op_id))
          {
          $a_items = RNCPHP\OP\OrderItems::find( "Incident.ID = $in_id");
          }
          else {
            $a_items = RNCPHP\OP\OrderItems::find("Opportunity.ID = $op_id" );
          }
          if (count($a_items) > 0)
          {
            $result = $this->getStock($a_items);

            if ($result === false)
            {

              return parent::getData();
            }
            else
            {
              $a_viewStock = array();
              $a_resultList = $result["order_detail"]["list_products"];
              foreach ($a_resultList as $key => $item) {
                $product                       = $this->getInfoProduct($item["Inventory_item_id"]);
                $a_viewTemp["name"]            = $product->Name  ;
                $a_viewTemp["inventoryItemId"] = $product->CodeItem;
                $a_viewTemp["stock"]           = $item["stock"];

                $a_viewStock[] = $a_viewTemp;
              }

              $this->data["a_viewStock"] = $a_viewStock;

            }
          }
          else
          {
            $this->data['message'] = "No existen items asociados";
          }
        }
        else
        {
          $this->data['message'] = "Haga click en Botón 'Ver Stock' para tener una vista de los items junto al stock aproximado";
        }

        return parent::getData();
    }


    private function getStock($a_items)
    {

      foreach ($a_items as $item)
      {
        if ($item->Enabled === false )
          continue;
        $a_tmp_result['Inventory_item_id']  = $item->Product->InventoryItemId;
        $a_list_items[] = $a_tmp_result;
      }


      $this->CI->load->library('Blowfish', false); //carga Libreria de Blowfish

      $array_post     = array('usuario' => 'Integer',
                              'accion' => 'ItemsStock',
                              'order_detail'=> array('list_products'=> $a_list_items)
                              );

      $json_data_post = json_encode($array_post);


      $json_data_post = $this->CI->blowfish->encrypt($json_data_post, self::KEY_BLOWFISH, 10, 22, NULL);
      $json_data_post = base64_encode($json_data_post);

      $postArray = array ('data' => $json_data_post);
      $cfg = RNCPHP\Configuration::fetch( 1000003 ); //CUSTOM_CFG_WS_URL


      $result = $this->requestCURLByPost($cfg->Value, $postArray);

      if ($result === false)
      {
        return false;
      }
      else
      {
        $result = json_decode($result, true);
        if (!empty($result['respuesta']) and ($result['resultado'] == "true"))
        {
          $respuesta  = base64_decode($result['respuesta']);
          $json_resp  = $this->CI->blowfish->decrypt($respuesta, self::KEY_BLOWFISH, 10, 22, NULL);
          $array_data = json_decode(utf8_encode($json_resp), true);

          if (!is_array($array_data))
          {
              $this->data['message'] = "ERROR: Estructura JSON encriptado No valida ".PHP_EOL;
              $this->data['message'] .= "JSON: ".$json_hh;
              return false;
          }

          return $array_data;
        }
        else
        {
          $this->data['message'] = "Error: Estructura del servicio no esperada";
          return false;
        }
      }
    }

    private function requestCURLByPost($url, $postArray)
    {
        # Form data string
        if (is_array($postArray))
            $postString = http_build_query($postArray, '', '&');

        load_curl();
        $ch = curl_init($url);

        # Setting our options

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 500);
        //curl_setopt($ch, 156, 500);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        # Get the response
        $response = curl_exec($ch);

        if(curl_errno($ch))
        {
            $info = curl_getinfo($ch);
            $this->data['message']= curl_error($ch);
            //$this->data['message'].='<br>Tiempo ' . $info['total_time'] . ' segundos en recibir la respuesta de la siguiente URL: ' . $info['url'];
            curl_close($ch);
            return false;
        }

        if ($response != false)
        {
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($statusCode != '200')
            {
                $this->data['message']= 'No se pudo resolver la petición a la URL, codigo de Error: '. $statusCode;
                return false;
            }
            else
                return $response;
        }
        else
        {
            curl_close($ch);
            $this->data['message']= 'No se pudo resolver la petición a la URL';
            return false;
        }
    }

    private function getInfoProduct($inventoryItemId)
    {
      $product = RNCPHP\OP\Product::first("InventoryItemId = $inventoryItemId");
      return $product;
    }
}
