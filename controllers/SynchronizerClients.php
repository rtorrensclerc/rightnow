<?php
namespace Custom\Controllers;

//require_once APPPATH. 'libraries/Simplexml.php';

class SynchronizerClients extends \RightNow\Controllers\Base
{

    protected $typeFormat = 'json';


    function __construct()
    {
        parent::__construct();
        //$this->load->library('custom/Simplexml');
        $this->load->model('custom/ws/EbsOrganization');
    }

    public function execute()
    {
        if (!empty($_POST))
        {
            $array_data = $this->getdataPOST();

            if (is_array($array_data) and ($array_data!=false))
            {
                $IndiceClients = 'Clientes';
                $IndiceDirections = 'Direcciones';

                if (array_key_exists($IndiceClients, $array_data) and array_key_exists($IndiceDirections, $array_data))
                {
                    $array_result                               = array ('Resultado' => true, 'Respuesta' => array('Clientes' => '', 'Direcciones' => '' )) ;
                    $array_result['Respuesta']['Clientes']      = $this->updateClients($array_data[$IndiceClients]);
                    $array_result['Respuesta']['Direcciones']   = $this->updateDirections($array_data[$IndiceDirections]);
                    $response = $this->formatEncode($array_result);
                    $this->sendResponse($response);
                }
                else
                {
                    $response = $this->responseError(3);
                    $this->sendResponse($response);
                }
            }
            else{
                $response = $this->responseError(2);
                $this->sendResponse($response);
            }
        }
        else {
            $response = $this->responseError(1);
            $this->sendResponse($response);
        }
    }

    private function updateClients($array_clients)
    {
        foreach ($array_clients as $client)
        {
            $idCliente = $client['ID_Cliente'];
            $partyNumber = $client['Party_Number'];
            $rut = $client['RUT'];
            $razonSocial = $client['Razon_social'];

            $result = $this->EbsOrganization->modifyClient($idCliente, $partyNumber, $rut, $razonSocial);
            if ($result == false)
            {
                $array_result[] = array('ID_cliente' => $client['ID_Cliente'],  'Estado' => false, 'Glosa' => $this->EbsOrganization->getLastError());
            }
            else
            {
                $array_result[] = array('ID_cliente' => $client['ID_Cliente'],  'Estado' => true, 'Glosa' => 'Ingresado correctamente');
            }
        }
        return $array_result;
    }
    private function updateDirections($array_directions)
    {

        $array_result = array();
        foreach ($array_directions as $direction)
        {
            $id_parent_client = $direction["ID_Cliente"];
            $party_site_number = $direction["Party_Site_Number"];
            $id_direction = $direction["ID_Direccion"];
            $comuna = $direction["Comuna"];
            $dir_envio = $direction["Dir_Envio"];

            $is_facturacion = $direction["Es_Facturacion"];
            $is_envio = $direction["Es_Envio"];
            $activate = $direction["Activado"];
            $region = $direction["Region"];

            $result = $this->EbsOrganization->modifyDirection($id_direction, $id_parent_client, $party_site_number, substr($dir_envio,0,255), $region, $comuna, $is_facturacion, $is_envio,$activate);
            if ($result == false)
            {

                $array_result[] = array('ID_direccion' => $direction['ID_Direccion'],  'Estado' => false, 'Glosa' => $this->EbsOrganization->getLastError());
            }
            else
            {
                $array_result[] = array('ID_direccion' => $direction['ID_Direccion'],  'Estado' => true, 'Glosa' => 'Ingresado correctamente');
            }
        }
        return $array_result;
    }

    private function responseError($type, $message = false)
    {

        $array_error = array ('Resultado' => false, 'Respuesta' => array(), 'JSON ERROR'=> json_last_error(), 'POST' => $_POST['data']);
        //$array_error = array ('Resultado' => false, 'Respuesta' => array());
        switch ($type) {
            case 1:
                $array_error['Respuesta'] =  array('Error' => 1, 'Glosa' => 'No tiene los permisos para acceder a esta pagina');
                break;
            case 2:
                $array_error['Respuesta'] =  array('Error' => 2, 'Glosa' => 'Cadena inesperada, problemas al decodificar');
                break;
            case 3:
                $array_error['Respuesta'] =  array('Error' => 3, 'Glosa' => 'Estructura no válida en la variable enviada');
                break;
            default:
                $array_error['Respuesta'] =  array('Error' => 1, 'Glosa' => 'No tiene los permisos para acceder a esta pagina');
                break;
        }

        $responseEncode = $this->formatEncode($array_error);
        return $responseEncode;
    }

    private function getdataPOST()
    {
        $data = $_POST['data'];
        if (!empty($data)){
            $data = base64_decode($data);
            $data = utf8_encode($data);
            $array_data = $this->formatDecode($data);
            return $array_data;
        }
        return false;
    }

    private function formatEncode($cadena)
    {
        $CI = &get_instance();
        switch ($this->typeFormat) {
            case 'json':
                return json_encode($cadena);
                //return $CI->load->library('Simplexml')->test();
                break;
            case 'xml':
                return json_encode($cadena);
                //return  $Obj->xml_parse($cadena);
                //return  $CI->Simplexml->xml_parse($cadena);
                break;
            default:
                return json_encode($cadena);
                break;
        }
    }
    private function formatDecode($cadena)
    {
        switch ($this->typeFormat)
        {
            case 'json':
                return json_decode($cadena, true);
                break;
            case 'xml':
                return json_encode($cadena, true);
                break;
            default:
                return json_decode($cadena, true);
                break;
        }
    }
    private function sendResponse($response)
    {
        switch ($this->typeFormat) {
            case 'json':
                header('Content-Type: application/json');
                echo $response;
                break;
            case 'xml':
                header('Content-Type: application/xml');
                echo $response;
                break;
            default:
                header('Content-Type: application/json');
                echo $response;
                break;
        }
        die();
    }
}
