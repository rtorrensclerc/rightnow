<?php
namespace Custom\Widgets\pdf;
use RightNow\Connect\v1_2 as RNCPHP;

class pdfAttacher extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData() {

      if ($_REQUEST['op'] && $_REQUEST['cr']) {
        $op_id = $_REQUEST['op'];
        $this->CI->load->library('fpdf2');

        $this->CI->fpdf2->AliasNbPages();
        $this->CI->fpdf2->AddPage();
        $this->CI->fpdf2->SetFont('Times','',12);

        $this->CI->load->model('custom/ws/OpportunityModel');

        $op = $this->CI->OpportunityModel->getOpportunity($op_id);
        //Creando Tabla
        $header = array('ID','Fecha de Creación','Nombre','Resumen','Dirección');
        $data = $op->ID.';'.$op->CreatedTime.';'.$op->Name.';'.$op->Summary.';'.$op->CustomFields->OP->Direccion;
        $data2 = array();
        $data2[] = explode(';',trim($data));

        $this->CI->fpdf2->AddPage();
        $this->CI->fpdf2->ImprovedTable($header,$data2);
        $pdfBuffer = $this->CI->fpdf2->Output('S');
        $name = "pdftry";

        if ($this->CI->OpportunityModel->attachPDF($op_id,$pdfBuffer,$name)) {
          echo "PDF Adjuntado Correctamente";
        }else {
          echo "Error al adjuntar PDF";
        }
      }


        return parent::getData();

    }

    /**
     * Handles the default_ajax_endpoint AJAX request
     * @param array $params Get / Post parameters
     */
    function handle_default_ajax_endpoint($params) {
        // Perform AJAX-handling here...
        // echo response
    }
}
