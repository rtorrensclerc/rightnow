<?php
namespace Custom\Widgets\pdf;
//PROD
use \RightNow\Connect\v1_3 as RNCPHP;

class pdfAttacher2 extends \RightNow\Libraries\Widget\Base {

    public $xPos   = '';
    public $yPos   = '';
    public $yspace = '';
    public $companyInfo1 = '';
    public $companyInfo2 = '';
    public $companyInfo3 = '';
    public $companyInfo4 = '';
    public $companyInfo5 = '';
    public $companyInfo6 = '';
    public $conditionsInfo1 = '';
    public $conditionsInfo2 = '';
    public $conditionsInfo3 = '';
    public $conditionsInfo4 = '';
    public $conditionsInfo5 = '';
    public $mail_to_ptto ='';

    function __construct($attrs) {
      //Carga de Datos
      $this->companyInfo1    = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_COMPANY_INFO_1);
      $this->companyInfo2    = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_COMPANY_INFO_2);
      $this->companyInfo3    = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_COMPANY_INFO_3);
      $this->companyInfo4    = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_COMPANY_INFO_4);
      $this->companyInfo5    = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_COMPANY_INFO_5);
      $this->companyInfo6    = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_COMPANY_INFO_6);
      $this->conditionsInfo1 = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_CONDITIONS_INFO_1);
      $this->conditionsInfo2 = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_CONDITIONS_INFO_2);
      $this->conditionsInfo3 = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_CONDITIONS_INFO_3);
      $this->conditionsInfo4 = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_CONDITIONS_INFO_4);
      $this->conditionsInfo5 = RNCPHP\MessageBase::fetch(CUSTOM_MSG_PDF_CONDITIONS_INFO_5);
      $this->mail_to_ptto    = RNCPHP\MessageBase::fetch(CUSTOM_MSG_MAIL_TO_PTTO);
      parent::__construct($attrs);
    }

    function getData()
    {

      $op_id_input = $_GET['op'];
      $this->data['op']=$op_id_input;
      if (empty($op_id_input))
      {
        $this->data['message'] = "Problemas en la recepción del ID de PPTO";
        return parent::getData();
      }
      

      //Se cargo el formilario
      if (!empty($_POST['enviar']))
      {
        $op_id = $op_id_input;

        //se carga la librería
        $this->CI->load->library('fpdf2');
        $this->CI->load->model('custom/ws/OpportunityModel');

        //Se trae la oportunidad
        $op = $this->CI->OpportunityModel->getOpportunity($op_id);

        //Codigo para renombrar archivo
        if (!empty($op->FileAttachments)) {
            $last_pdf_name = end($op->FileAttachments)->FileName;
            $last_name = substr($last_pdf_name,0,strlen($last_pdf_name)-4);
            $name_pdf = "Cotizacion - ".$op->ID;

            if (!(strlen($last_name) > strlen($name_pdf))) {
              $name = "Cotizacion - ".$op->ID." (1)";
            }else {
              $num = substr($last_name,strlen($last_name)-2,strlen($last_name)-1);
              $num = (int)(str_replace(')','',$num));
              $num++;
              $name = "Cotizacion - ".$op->ID." (".$num.")";
            }

        }
        else {
          $name = "Cotizacion - ".$op->ID;
        }
      
        //Creando Contenido de archivo
        $this->CI->fpdf2->AliasNbPages();
        $this->CI->fpdf2->AddPage();
        //$this->CI->fpdf2->SetFont('Times','',12);

        //Agregando Cabecera
        $this->xPos = $this->CI->fpdf2->GetPageWidth()-100-20; // Ancho Página - Ancho Texto - Margenes LR
        $this->yPos = $this->CI->fpdf2->GetY();
        $this->yspace = 3;
        $a_companyInformation = array();

        //$a_companyInformation[] = 'Dimacofi S.A. Giro:';
        $a_companyInformation[] = $this->companyInfo1->Value;
        //$a_companyInformation[] = 'Importadora y Distribuidora de Equipos de Oficina';
        $a_companyInformation[] = $this->companyInfo2->Value ;
        // $a_companyInformation[] = 'RUT: 92.083.000-5';
        $a_companyInformation[] = $this->companyInfo3->Value;
        // $a_companyInformation[] = 'Casa Matriz: Av. Vitacura 2939, Piso 15, Las Condes';
        $a_companyInformation[] = $this->companyInfo4->Value;
        // $a_companyInformation[] = 'Fono: (02) 2549 7777 - Fax: (02) 2549 7250';
        $a_companyInformation[] = $this->companyInfo5->Value;
        // $a_companyInformation[] = 'Santiago de Chile - www.dimacofi.cl';
        $a_companyInformation[] = $this->companyInfo6->Value;

        //Agregando Información de Compañia al PDF
        $this->CI->fpdf2->addCompanyInformation($a_companyInformation, $this->xPos, $this->yPos);

        //Agregando Titulo
        $this->xPos = 10;
        $this->yPos = $this->CI->fpdf2->GetY() + $this->yspace;
        $fechaActual = date("d/m/Y");
        $this->CI->fpdf2->addTitleInformation(["Número Presupuesto: {$op->ID}", "Fecha: {$fechaActual}"], $this->xPos, $this->yPos);

        //Información General del Cliente y del PPTO
        $this->yPos = $this->CI->fpdf2->GetY() + $this->yspace;

        $a_customerInformation   = array();
        $a_customerInformation[] = array("label" => 'Razón Social' , "value" => "{$op->Organization->Name}" );
        $a_customerInformation[] = array("label" => 'Nombre Contacto' , "value" => $op->PrimaryContact->Contact->Name->First." ".$op->PrimaryContact->Contact->Name->Last );
        $a_customerInformation[] = array("label" => 'Email' , "value" => $op->PrimaryContact->Contact->Emails[0]->Address );
        $a_customerInformation[] = array("label" => 'RUT' , "value" => "{$op->Organization->CustomFields->c->rut}" );
				$a_customerInformation[] = array("label" => 'Teléfono' , "value" => "{$op->PrimaryContact->Contact->Phones[0]->Number}" );
        $a_customerInformation[] = array("label" => 'Dirección Contacto' , "value" => $op->CustomFields->OP->Direccion->dir_envio );
        $a_customerInformation[] = array("label" => 'Terminos' , "value" => $op->CustomFields->c->payment_conditions->LookupName );
        //RTC 2017/03/13
        $a_customerInformation[] = array("label" => 'Vendedor' , "value" => $op->CustomFields->Comercial->Ejecutivo->name  );
        //$a_customerInformation[] = array("label" => 'Vendedor' , "value" => $op->CustomFields->Comercial->Vendedor->LookupName );
        $a_customerInformation[] = array("label" => 'Comuna' , "value" => $op->CustomFields->OP->Direccion->ebs_comuna);
        //$a_customerInformation[] = array("label" => 'Comuna' , "value" => $op->CustomFields->OP->Direccion->comuna->com_desc );
        
        $a_customerInformation[] = array("label" => 'Provincia' , "value" => $op->CustomFields->OP->Direccion->comuna->prov_id->prov_desc);
        
        //$a_customerInformation[] = array("label" => 'Región' , "value" => $op->CustomFields->OP->Direccion->comuna->prov_id->reg_id->reg_desc);
        $a_customerInformation[] = array("label" => 'Región' , "value" => $op->CustomFields->OP->Direccion->ebs_region);

        if (!empty($op->CustomFields->OP->IncidentService))
        {
          $a_hhInformation   = array();
          $a_hhInformation[] = array("label" => 'Modelo' , "value" => $op->CustomFields->OP->IncidentService->CustomFields->c->modelo_hh);
          $a_hhInformation[] = array("label" => 'HH' , "value" => $op->CustomFields->OP->IncidentService->CustomFields->c->id_hh );
          $a_hhInformation[] = array("label" => 'Serie' , "value" => $op->CustomFields->OP->IncidentService->CustomFields->c->serie_maq );
          $a_hhInformation[] = array("label" => 'Técnico' , "value" => $op->CustomFields->OP->IncidentReparation->AssignedTo->Account->Name->First." ".$op->CustomFields->OP->IncidentReparation->AssignedTo->Account->Name->Last );
        }
        else
        {
          $a_hhInformation = array();
        }
        //Agregar Información de Cliente y HH al PDF
        $this->CI->fpdf2->addTableCustomerInformation($a_customerInformation,$a_hhInformation, $this->xPos, $this->yPos);


        //Información de las lineas
        $a_items_pdf = array();
        $a_items = $this->CI->OpportunityModel->getItems($op_id);

        $totalNetValue = 0;
        //$a_items = array();
        //RTC 2016 10/03/2017   acumulador de total
        $descuentoTotal=0;
        foreach ($a_items as $key => $item)
        {
          $a_items_temp_pdf['quantity']    = $item->QuantitySelected;
          $a_items_temp_pdf['description'] = $item->Product->Name;
          $a_items_temp_pdf['unitValue']   = number_format($item->UnitTempSellPrice);
          $a_items_temp_pdf['netValue']    = number_format($item->ConfirmedSellPrice); //$item->ConfirmedSellPrice;
          //$a_items_temp_pdf['discount']    = (!empty($item->DiscountSellPrice)) ? $item->DiscountSellPrice:0 ;
          $a_items_pdf[] = $a_items_temp_pdf;

          $totalNetValue += $a_items_temp_pdf['netValue'];



          //RTC 2016 10/03/2017
          $descuentoTotal=$descuentoTotal + $item->QuantitySelected*$item->UnitTempSellPrice-$item->ConfirmedSellPrice;
        }

        //Iva aplicado al valor sin descuento
        /*
        $iva = ($totalNetValue * 19) / 100;
        $iva = round($iva);
        */

        //Iva aplicado al valor con Descuento
        $iva = ($op->ClosedValue->Value * 19) / 100;
        $iva = round($iva);

        $this->yPos = $this->CI->fpdf2->GetY() + $this->yspace;

        $a_totalValues   = array();
        $a_totalValues[] = array("label" => 'Valor Neto $' , "value" => number_format($op->ClosedValue->Value) );
        $discount        = $op->CustomFields->c->discount_selling;
        //RTC 2016 10/03/2017
        $a_totalValues[] = array("label" => '% Descuento' , "value" => (!empty($discount)) ? $discount:0 );
        //
        $a_totalValues[] = array("label" => 'Valor Descuento' , "value" => number_format($descuentoTotal));

        $a_totalValues[] = array("label" => 'I.V.A.' , "value" => number_format($iva));
        $finallyPrice    = $op->ClosedValue->Value + $iva;
        $a_totalValues[] = array("label" => 'Total' , "value" => number_format($finallyPrice));

        //Agregando los Items al PDF
        $this->CI->fpdf2->addTableLines($a_items_pdf, $a_totalValues , $this->xPos, $this->yPos);

				// Observaciones
				$this->yPos = $this->CI->fpdf2->GetY() + $this->yspace;
				$this->CI->fpdf2->addComment($op->Summary, $this->xPos, $this->yPos);

        $a_conditionsInformation = array();
        $a_inx = array();
        $a_conditionsInformation[] ="";
        $a_inx[]='Observaciones:';
        // $a_conditionsInformation[] = '1. Este presupuesto tiene vigencia de 20 días. En caso de cummplirse el plazo, se facturará 1 UF + IVA por concepto de visita doagnóstico.'; //CUSTOM_MSG_PDF_CONDITIONS_INFO_1
        $a_conditionsInformation[] = "";
        $a_inx[]='';
        // $a_conditionsInformation[] = '2. Si el cliente acepta el presupuesto, debe enviarlo firmado y timbrado al correo aconcepcion@dimacofi.cl o al Fax (02) 2549 7430.';
        $a_conditionsInformation[] = 'Los repuestos en stock nacional tardan 4 días hábiles en llegar en la zona de Santiago, y 7 días hábiles en otras Regiones.';
        $a_inx[]='-';
        // $a_conditionsInformation[] = '3. De no haber algún respuesto en stock, se debe informará el tiempo de disponibilidad, y se facturará una vez efecturada la repración.';
        $a_conditionsInformation[] = 'De no haber algún repuesto en stock, éstos entran en proceso de compra y tienen un tiempo de despacho de aproximadamente 30 a 60 días hábiles.';
        
        $a_inx[]='-';
        // $a_conditionsInformation[] = '4. La garantía de 3 meses sólo aplica en "Mano de Obra", siempre y cuando la segunda reparación tenga relación con la primera falla.';
        $a_conditionsInformation[] = 'Este tiempo de despacho no depende de Dimacofi, y será informado por medio de correo electrónico, que esta informado en la cotización.';
        $a_inx[]='';
        // $a_conditionsInformation[] = '5. Dimacofi podrá facturar a partir de la fecha en que se acepta el presupuesto por parte del cliente.';
        $a_conditionsInformation[] = 'Las fechas de llegadas pueden sufrir cambios sin previo aviso, y serán informadas oportunamente vía correo electrónico, al informado en la cotización.';
        $a_inx[]='-';
        $a_conditionsInformation[] = 'La instalación o mantención realizada tiene una garantía de 3 meses, siempre y cuando la segunda reparación tenga relación con la primera falla y';
        $a_inx[]='-';
        $a_conditionsInformation[] = 'no una nueva. Esta garantía no aplica para los repuestos e insumos.';
        $a_inx[]='';
        $a_conditionsInformation[] = 'Esta cotización tiene validez de 15 días hábiles para Empresas o Particulares, y 30 días para Entidades Públicas.';
        $a_inx[]='-';
        $a_conditionsInformation[] = 'Los gastos adicionales en que Dimacofi deba incurrir para reparar el equipo, serán debidamente acreditados al Cliente y cobrados al terminar la';
        $a_inx[]='-';
        
        $a_conditionsInformation[] = 'reparación.';
        $a_inx[]=' ';
        

        $a_conditionsInformation[] = 'Una vez aceptada la cotización, los repuestos no tienen derecho a devolución.';
        $a_inx[]='-';

        $a_conditionsInformation[] = 'Los pagos se realizan a:';
        $a_inx[]='-';

        $a_conditionsInformation[] = "";
        $a_inx[]='';
        //Información Final
        $this->yPos = $this->CI->fpdf2->GetY() + $this->yspace;

        //agregando las condiciones al PDF
        $this->CI->fpdf2->addConditions($a_conditionsInformation, $this->xPos, $this->yPos,$a_inx);

        $this->CI->fpdf2->addTexto('Dimacofi S.A. 92.083.000-5  Banco: Scotiabank       Cuenta Corriente: 71-10187-02', $this->xPos, $this->yPos);
        $this->CI->fpdf2->addTexto('Una vez realizada la transferencia favor enviar comprobante a arangel@dimacofi.cl', $this->xPos, $this->yPos);

        //Información Final
        $this->yPos = $this->CI->fpdf2->GetY() + $this->yspace;
        $this->CI->fpdf2->addSing($this->xPos, $this->yPos);

        $this->yPos = $this->CI->fpdf2->GetY() + $this->yspace;

        $a_ejecutiveInformation = array();
/*
        $a_ejecutiveInformation[] = array('label' => 'Ejecutiva:', 'value' => $op->AssignedToAccount->Name->First. " ". $op->AssignedToAccount->Name->Last );
        $a_ejecutiveInformation[] = array('label' => 'Teléfono Contacto $:', 'value' => $op->AssignedToAccount->Phones[0]->Number );
        $a_ejecutiveInformation[] = array('label' => 'Correo Electrónico:', 'value' => $op->AssignedToAccount->Emails[0]->Address );
        $a_ejecutiveInformation[] = array('label' => 'Ciudad:', 'value' => $op->AssignedToAccount->CustomFields->c->sector_tecnico );
*/
        $acc=RNCPHP\Account::fetch($op->CustomFields->Comercial->EjecutivoZona->ID_cuenta);
        $a_ejecutiveInformation[] = array('label' => 'Ejecutiva/o:', 'value' => $acc->LookupName );
        $a_ejecutiveInformation[] = array('label' => 'Teléfono Contacto :', 'value' => $acc->Phones[0]->Number );
        $a_ejecutiveInformation[] = array('label' => 'Correo Electrónico:', 'value' => $acc->Emails[0]->Address );
        $a_ejecutiveInformation[] = array('label' => 'Ciudad:', 'value' => $op->AssignedToAccount->CustomFields->c->sector_tecnico );
        //Agregando la información Ejecutiva al PDF
        $this->CI->fpdf2->addEjecutiveInformation($a_ejecutiveInformation, $this->xPos, $this->yPos);

        //cargar archivo a PPTO
        $pdfBuffer = $this->CI->fpdf2->Output('S');

        if ($this->CI->OpportunityModel->attachPDF($op_id,$pdfBuffer,$name))
        {
          $this->data['message'] =  "PDF para PTTO Nº " . self::send_mail($op_id)  .  " Creado Correctamente, Adjuntado a pestaña Anexos.<br> Recuerde actualizar el Espacio de Trabajo para visualizar el documento.";
        }
        else
        {
          $this->data['message'] = "Error en la creación del PDF, comuniquese con el administrador";
        }
      }
      else {
        $this->data['message'] = "Haga click en Botón 'Crear PDF' para generar una cotización y asociarla al PPTO";
      }

      return parent::getData();

    }


        public function send_mail($op_id)
        {
          require_once(get_cfg_var("doc_root") . "/ConnectPHP/Connect_init.php");
          //error_reporting(E_ALL);
          initConnectAPI();
          $oportunidad = RNCPHP\Opportunity::fetch( $op_id);

            try
            {
              $mm = new RNCPHP\MailMessage();
              $mm->To->EmailAddresses = array($this->mail_to_ptto->Value ); // Mail de Agente configurado en Valores de Configuracion

              //$mm->CC->EmailAddresses = array($email_2);
              //$mm->BCC->EmailAddresses = array($email_3);
              $mm->Subject = "DIMACOFI S.A. PTTO  ". $op_id . " " . date("Y/m/d");

              if (count($oportunidad->FileAttachments))
              {
                //$mm->Body->Html = '<TABLE BORDER=1 WIDTH=300><TD WIDTH=100></TD><TD WIDTH=100></TD><TD WIDTH=100></TD></TABLE>';
                //$mm->Body->Text = 'Cotizacion Generada para Presupuesto ' . $op_id;
                $mm->Body->Html = 'Estimado(a) Cliente:<br>
                Junto con saludar, adjunto presupuesto de su equipo ingresado a nuestro Servicio Técnico.<br>
                <br>
                En caso de aceptar el presupuesto, debe enviarlo firmado y timbrado al correo electrónico kmorales@dimacofi.cl .<br>
                
                
                <u><b>CONSIDERACIONES IMPORTANTES:</b></u><br>
                -	Los repuestos en stock nacional tardan 4 días hábiles en llegar en la zona de Santiago, y 7 días hábiles en otras Regiones.<br>
                -	De no haber algún repuesto en stock, éstos entran en proceso de compra y tienen un tiempo de despacho de aproximadamente 30 a 60 días hábiles. Este tiempo de despacho no depende de Dimacofi, y será informado por medio de correo electrónico, que esta informado en la cotización.<br>
                -	Las fechas de llegadas pueden sufrir cambios sin previo aviso, y serán informadas oportunamente vía correo electrónico, al informado en la cotización.<br>
                -	La instalación o mantención realizada tiene una garantía de 3 meses, siempre y cuando la segunda reparación tenga relación con la primera falla y no una nueva. Esta garantía no aplica para los repuestos e insumos.<br>
                -	Esta cotización tiene validez de 15 días hábiles para Empresas o Particulares, y 30 días para Entidades Públicas.<br>
                -	Los gastos adicionales en que Dimacofi deba incurrir para reparar el equipo, serán debidamente acreditados al Cliente y cobrados al terminar la reparación.<br>
                -	Una vez aceptada la cotización, los repuestos no tienen derecho a devolución.<br>
                -	Los pagos se realizan a:<br>
                <br>
                <b>Dimacofi S.A. 92.083.000-5        Banco: Scotiabank        Cuenta Corriente: 71-10187-02</b>  <br>
                <b>Una vez realizada la transferencia favor enviar comprobante a arangel@dimacofi.cl</b><br>
                Deseando un buen día, se despide 


                '  ;

                $mm->FileAttachments[] =  $oportunidad->FileAttachments[count($oportunidad->FileAttachments)-1];
              }
              else {
                $mm->Body->Text = "sin datos";
              }
              //$this->sendResponse(count($oportunidad->FileAttachments));

              $mm->send();

              if($mm->Status->Sent)
              {
                //Success
              }
              else
              {
                //Failure
              }
            }

            catch ( Exception $err )
            {
              echo "<br><b>Exception</b>: line ".__LINE__.": ".$err->getMessage()."</br>";
            }
          return $op_id;
        }
}
