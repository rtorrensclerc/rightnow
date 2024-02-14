<?php
namespace Custom\Widgets\reports;

class DownloadReport extends \RightNow\Libraries\Widget\Base
{
    function __construct($attrs)
    {
        parent::__construct($attrs);

        $this->setAjaxHandlers(array(
            'download_ajax_endpoint' => array(
                'method'      => 'handle_download_ajax_endpoint',
                'clickstream' => 'download_ajax_endpoint',
            ),
        ));

        // $this->CI->load->Model('custom/Report');
        $this->CI->load->helper('utils_helper');
        // array_search(strtoupper($data['type']), $this->data['js']['valid_types']))
    }

    function getData()
    {
      $this->data['js']['valid_types'] = explode(',',strtoupper($this->data['attrs']['valid_types']));
      
      return parent::getData();
    }

    /**
     * Permite realizar la descarga del reporte indicado
     * 
     * @param array $params Get / Post parameters
     */
    function handle_download_ajax_endpoint($params)
    {
        $data = $params;

        if (empty($data))
        {
          $response['result']   = false;
          $response['id_error'] = 0;
          $response['message']  = 'Faltan datos.';

          echo json_encode($response);

          return false;
        }
        else if (empty($data['type']))
        {
          $response['result']   = false;
          $response['id_error'] = 1;
          $response['message']  = 'Debe definir el tipo.';

          echo json_encode($response);

          return false;
        }
        // else if (!array_search(strtoupper($data['type']), explode(',',strtoupper($this->data['attrs']['valid_types']))) !== -1)
        // {
        //   $response['result']   = false;
        //   $response['id_error'] = 2;
        //   $response['message']  = 'El formato solicitado no es valido.';

        //   echo json_encode($response);

        //   return false;
        // }

        // header('Content-Type: application/json');

        $type = strtoupper($data['type']);

        if($type === 'JSON')
        {
          header('Content-Type: application/json');
          echo getReportToJSON($this->data['attrs']['report_id']);
        }
        else if($type === 'CSV')
        {
          header('Content-Type: application/json');
          echo getReportToCSV($this->data['attrs']['report_id']);
        }
        else if($type === 'XLSX')
        {
          header('Content-Type: application/json');
          $data = getReportArrayToJSON($this->data['attrs']['report_id']);
          echo $data;
        }

        return false;
    }
    
    //
    //
    // function array2csv()
    // {
    //   $id_report      = $this->data["attrs"]["report_id"];
    //   $data           = $this->CI->Report->getInfoReport($id_report);
    //   $dataEncoded    = base64_encode(json_encode($data));
    //   header("Location: http://vivecmr.custhelp.com/cc/Download/csv/$dataEncoded");
    // }
    //
    //
    // public function forceDownload()
    // {
    //   $id_report      = $this->data["attrs"]["report_id"];
    //   $data           = $this->CI->Report->getInfoReport($id_report);
    //
    //   if (empty($data))
    //   {
    //     echo "no se pudo descargar CSV porque no se encontró información asociada al ID de Reporte";
    //     return;
    //   }
    //
    //   $lines          = '';      //$output         = fopen("php://output", 'w') or die("Can't open php://output");
    //   $force_download = true;
    //   $output = fopen("php://temp", "r+");
    //   //$output = tmpfile();
    //   if ($force_download)
    //   {
    //     header("Content-Type:application/csv");
    //     header("Content-Disposition:attachment;filename=archivo.csv");
    //   }
    //   else
    //   {
    //     header("Content-Type:text/plain");
    //   }
    //
    //   for ($i=0; $i < sizeof($data); $i++)
    //   {
    //       if ($i===0)
    //       {
    //         //echo fputcsv($output, array_keys($data[$i]));
    //         fputcsv($output, array_keys($data[$i]));
    //       }
    //
    //       //echo fputcsv($output, $data[$i]);
    //       fputcsv($output, $data[$i]);
    //   }
    //
    //
    //
    //   // fclose($output);
    //   // ob_clean();
    //   // ob_end_flush(); //Modify flush() to ob_end_flush();
    //   // readfile($output);
    //   // $data = ob_get_contents();
    //   // print_r($data);
    //   // $contLength = ob_get_length();
    //   // print_r($contLength);
    //   //header( 'Content-Length: '.$contLength);
    // }

}
