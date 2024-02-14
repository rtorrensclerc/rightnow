<?php
namespace Custom\Widgets\Info;
use RightNow\Connect\v1_2 as RNCPHP;
class BloquedStatus extends \RightNow\Libraries\Widget\Base {
    function __construct($attrs) {
        parent::__construct($attrs);

        $this->setAjaxHandlers(array(
            'SendMailRequest_ajax_endpoint' => array(
                'method'      => 'handle_SendMailRequest_ajax_endpoint',
                'clickstream' => 'custom_action',
            ),
        ));


    }

    function getData() {
        $incident_id = $_GET['p_iid'];
        $this->data['incident_id']=$incident_id ;
        $this->CI->load->model('custom/GeneralServices');

        $incident = RNCPHP\incident::fetch($incident_id);
        $this->data['incident']=$incident;
        //echo json_encode($incident->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut);
        
        $this->data['orgStatus'] = $this->CI->GeneralServices->getOrganizationStatusbyRut($incident->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut);
//echo json_encode($this->data['orgStatus'] );

        $Organizacion = RNCPHP\Organization::first( "CustomFields.c.rut = '".$this->data['orgStatus']->Customer->CustomerData->Customer->tRUT."'");


        $this->data['Organizacion']=$Organizacion;
       
        $message = '<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">

        <h2>
            <p>
                Existen restricciones para generar solicitudes: 
            </p>
        </h2>
    
        <div class="rn_ContentTab rn_ContentTab_LastInvoices" style="display:block;">
            <div class="rn_Grid">
                <table class="yui3-datatable-table rn_LastInvoices" border=1>
                    <tr>
                        <th class="rn_TextLeft">RUT</th>
                        <th class="rn_TextLeft">' . $this->data['orgStatus']->Customer->CustomerData->Customer->tRUT .'</th>
                    </tr>
                    <tr>
                        <th class="rn_TextLeft">Cliente</th>
                        <th class="rn_TextLeft">' . $this->data['Organizacion']->LookupName . '</th>
                    </tr>
                    ';
                   
                $bloqueado=0;
                if($this->data['orgStatus']->Customer->CustomerData->Customer->tbloqued=='Y') 
                {
                    $bloqueado=1;
                    $message =$message . '<tr>
                        <th class="rn_TextLeft">Bloqueado por Deuda Morosa</th>
                        <th class="rn_TextLeft">SI
                        <input type="hidden" id="id_mora" name="id_mora" value="' . $this->data['orgStatus']->Customer->CustomerData->Customer->tbloqued.' "></th>
    
                    </tr>' ;
                    
                }
                
                if($this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_FACTURACION=='SI') 
                {
                    $bloqueado=1;
           
                    $message =$message . '<tr>
                        <th class="rn_TextLeft">Bloqueado por Rechazo de Facturas</th>
                        <th class="rn_TextLeft">
                            '. $this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_FACTURACION .'
                            <input type="hidden" id="id_factura" name="id_factura" value="' . $this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_FACTURACION .' "></th>
                            </th>
                    </tr>';
                      
                }
                 if($this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_INFORMACION=='SI') 
                {
                    $bloqueado=1;
                    $message =$message . '
                    <tr>
                        <th class="rn_TextLeft">Bloqueado por Informacion Financiera incompleta</th>
                        <th class="rn_TextLeft">
                            ' . $this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_INFORMACION .'
                            <input type="hidden" id="id_info"  name="id_info" value="' . $this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_INFORMACION.'"></th>
                    </tr>';
                      
                }
                if($this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_RIESGO=='SI') 
                {
                    $bloqueado=1;
                    $message =$message . '
                    <tr>
                        <th class="rn_TextLeft">Bloqueado por Situacion de Riesgo</th>
                        <th class="rn_TextLeft">
                            '. $this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_RIESGO .'
                            <input type="hidden" id="id_riesgo" name="id_riesgo"  value="' . $this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_RIESGO.'"></th>
                    </tr>';
             
                }
                  if($this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_DEUDAS=='SI') 
                {
                    $bloqueado=1;
            ?>
                    <tr>
                        <th class="rn_TextLeft">Bloqueado por Castigo de Deudas Antiguas</th>
                        <th class="rn_TextLeft">
                            <?=$this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_DEUDAS .'
                            <input type="hidden" id="id_deuda" name="id_deuda" value="' . $this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_DEUDAS .'">
                            </th>
    
                    </tr>';
                  
                }
                $message =$message . '
                </table>
            </div>
        </div>';
    
        
        if($this->data['orgStatus']->BlockAddress->List->data)
        {
        $bloqueado=1;
        $message =$message . '
        <h2>
            <p>
            Sucursales Bloqueadas
            </p>
        </h2>';            
        $message = $message . '<table class="yui3-datatable-table rn_LastInvoices" border=1><tr><th class="rn_TextLeft">';
     
       foreach( $this->data['orgStatus']->BlockAddress->List->data as $Sucursal)
       {
           if($Sucursal->DIRECCION)
           {    
            $message = $message .$Sucursal->DIRECCION . '<br>'; 
            }
            else
            {
                $message = $message .  $Sucursal;
            }

        
       }
       $message = $message . '</th></tr></table>';
        
        
    
      }

        $message =$message . '
        <h2>
            <p>
                Listado de Facturas pendientes
            </p>
        </h2>
        <div class="rn_ContentTab rn_ContentTab_LastInvoices" style="display:block;">
            <div class="rn_Grid">
                <table class="yui3-datatable-table rn_LastInvoices" border=1>
                    <thead>
                        <tr>
                            <th class="rn_TextRight">Nro Factura</th>
                            <th class="rn_TextRight">Contrato</th>
                            <th class="rn_TextRight">Monto</th>
                            <th class="rn_TextRight">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>';
                   
        setlocale(LC_MONETARY, "es_CL");
    if(count($this->data['orgStatus']->Invoice->InvoiceData->Invoices)==1)
    {
        $message =$message . '
                    <tr>
                        <td class="rn_TextRight">
                        ' . $this->data['orgStatus']->Invoice->InvoiceData->Invoices->TRX_NUMBER .'</td>
                        <td class="rn_TextRight">';
                        if($Invoice->CT_REFERENCE)
                        {
                           $message =$message . $this->data['orgStatus']->Invoice->InvoiceData->Invoices->CT_REFERENCE;

                        }
                        else
                        {
                        $message =$message .'(Sin Valor)';
                        }
                        $message =$message .'</td>

                        <td class="rn_TextRight">'. money_format('%.0n', $this->data['orgStatus']->Invoice->InvoiceData->Invoices->AMOUNT) 
                        .'</td>
                        <td class="rn_TextRight">'.
                        $this->data['orgStatus']->Invoice->InvoiceData->Invoices->DUE_DATE .'
                        </td>
                    </tr>
                    ';
    }
    else
    {
        if(count($this->data['orgStatus']->Invoice->InvoiceData->Invoices)>1)
        {
            foreach ( $this->data['orgStatus']->Invoice->InvoiceData->Invoices as $Invoice)
            {
               
                $message =$message . '
                        <tr>
                            <td class="rn_TextRight">'. $Invoice->TRX_NUMBER .'</td>
                            <td class="rn_TextRight">';
                             if($Invoice->CT_REFERENCE)
                             {
                                $message =$message . $Invoice->CT_REFERENCE;

                             }
                             else
                             {
                             $message =$message .'(Sin Valor)';
                             }
                             $message =$message .'</td>
                            <td class="rn_TextRight">'. money_format('%.0n', $Invoice->AMOUNT) .'</td>
                            <td class="rn_TextRight">'. $Invoice->DUE_DATE .'</td>
                        </tr>';
                     

            }
       }
    }

    $message =$message . '
                </tbody>
            </table>
        </div>
    </div>
    
    </div><br>';
   


        if($_POST['enviar']=='Enviar Informe')
        {
            $data=array();
            $params=array();
           
            $data['bloqueo'] = 0;
            
            if ($this->data['orgStatus']->Customer->CustomerData->Customer->tbloqued=='Y')
            {
                $data['bloqueo'] = $data['bloqueo'] + 1;
            }
            if ($this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_FACTURACION=='SI')
            {
                $data['bloqueo'] = $data['bloqueo'] + 2;
            }
            if ($this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_INFORMACION=='SI')
            {
                $data['bloqueo'] = $data['bloqueo'] + 4;
            }
            if ($this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_RIESGO=='SI')
            {
                $data['bloqueo'] = $data['bloqueo'] + 8;
            }
            if ($this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_DEUDAS=='SI')
            {
                $data['bloqueo'] = $data['bloqueo'] + 16;
            }
            if($this->data['orgStatus']->BlockAddress->List->data)
            {
                $data['bloqueo'] = $data['bloqueo'] + 32;
            }
            $data['id_incidente']=$incident_id;
            $data['message']=$message;
            $params['data']=json_encode($data);
       
            $this->handle_SendMailRequest_ajax_endpoint($params);
            echo '<br><B>Notificacion enviada con Exito </B><br>';
        }

        return parent::getData();

    }

    /**
     * Handles the default_ajax_endpoint AJAX request
     * @param array $params Get / Post parameters
     */
    function handle_SendMailRequest_ajax_endpoint($params) {
        // Perform AJAX-handling here...
        // echo response
        //echo $params['id_mora'];

        
        $params['success']=true;
        $bloqueos=json_decode($params['data']);
       
        
       

        $obj_incident = RNCPHP\Incident::fetch( $bloqueos->id_incidente);
         
       /* $body =  "<br>nombre : " .$obj_incident->PrimaryContact->Name->First;
        $body = $body ."<br>apellido : " . $obj_incident->PrimaryContact->Name->Last;
        $body = $body ."<br>email : " . $obj_incident->PrimaryContact->Emails[0]->Address;
        $body = $body ."<br>telefono : " . $obj_incident->PrimaryContact->Phones[0]->Number;
        $body = $body ."<br>Incidente RigthNow : " . $obj_incident->ReferenceNumber;
        */

        $params['incident']=$obj_incident->ReferenceNumber;
        //$obj_incident->StatusWithType->Status->ID=;
        
        if($bloqueos->bloqueo && 1 || $bloqueos->bloqueo && 16)
        {
                $Mail = RNCPHP\Configuration::fetch( CUSTOM_CFG_MAIL_COBRANZA );
              
                $mm = new RNCPHP\MailMessage();
                $mm->To->EmailAddresses = array($obj_incident->PrimaryContact->Emails[0]->Address);
                $mm->CC->EmailAddresses = array($Mail->Value);
                $mm->Subject = "Problemas de Bloqueo Cliente : NUMERO TICKET["  . $obj_incident->ReferenceNumber ."]";
                $mm->Body->Html = $mm->Body->Html .  $bloqueos->message;
                $v=1;
                if($bloqueos->bloqueo & $v)
                {
                    
                    $StandardContent1 = RNCPHP\StandardContent::fetch(29);
                    $mm->Body->Html = $mm->Body->Html . $StandardContent1->ContentValues[1]->Value ;
                }
                $v=16;
                if($bloqueos->bloqueo & $v)
                {
                    $StandardContent16 = RNCPHP\StandardContent::fetch(31);
                    $mm->Body->Html = $mm->Body->Html . '<br>' . $StandardContent16->ContentValues[1]->Value ;
                }

              
                
                $mm->send();
                $params['Sent']=$mm->Status->Sent;
                if($mm->Status->Sent)
                {
                //Success
                }
                else
                {
                //Failure
                }
        }
        if($bloqueos->bloqueo & 2)
        {
                $Mail = RNCPHP\Configuration::fetch( CUSTOM_CFG_MAIL_FACTURACION );
               
                $StandardContent = RNCPHP\StandardContent::fetch(30);
                $mm = new RNCPHP\MailMessage();
                $mm->To->EmailAddresses = array($Mail->Value);
                $mm->Subject = "Porblemas de Bloqueo Cliente : NUMERO TICKET["  . $obj_incident->ReferenceNumber ."]";
                $mm->Body->Html = $mm->Body->Html .  $bloqueos->message;
                $mm->Body->Html = $mm->Body->Html . $StandardContent->ContentValues[1]->Value;
                $mm->Body->Html = $mm->Body->Html . $body;
           
                $mm->send();
                $params['Sent']=$mm->Status->Sent;
                if($mm->Status->Sent)
                {
                //Success
                }
                else
                {
                //Failure
                }
        }
        if($bloqueos->bloqueo & 4)
        {
                $Mail = RNCPHP\Configuration::fetch( CUSTOM_CFG_MAIL_CREDITO );
                $StandardContent = RNCPHP\StandardContent::fetch(33);
                $mm = new RNCPHP\MailMessage();
                $mm->To->EmailAddresses = array($Mail->Value);
                $mm->Subject = "Porblemas de Bloqueo Cliente: NUMERO TICKET["  . $obj_incident->ReferenceNumber ."]";
                $mm->Body->Html = $mm->Body->Html .  $bloqueos->message;
                $mm->Body->Html = $mm->Body->Html . $StandardContent->ContentValues[1]->Value;
                $mm->Body->Html = $mm->Body->Html . $body;
  
                $mm->send();
                $params['Sent']=$mm->Status->Sent;
                if($mm->Status->Sent)
                {
                //Success
                }
                else
                {
                //Failure
                }
        }
        if($bloqueos->bloqueo & 8)
        {
                $Mail = RNCPHP\Configuration::fetch( CUSTOM_CFG_MAIL_CREDITO );
                $StandardContent = RNCPHP\StandardContent::fetch(32);
                $mm = new RNCPHP\MailMessage();
                $mm->To->EmailAddresses = array($Mail->Value);
                $mm->Subject = "Porblemas de Bloqueo Cliente: NUMERO TICKET["  . $obj_incident->ReferenceNumber ."]";
                $mm->Body->Html = $mm->Body->Html .  $bloqueos->message;
                $mm->Body->Html = $mm->Body->Html . $StandardContent->ContentValues[1]->Value;
                $mm->Body->Html = $mm->Body->Html . $body;
    
                $mm->send();
                $params['Sent']=$mm->Status->Sent;
                if($mm->Status->Sent)
                {
                //Success
                }
                else
                {
                //Failure
                }
        }
       
        if($bloqueos->bloqueo & 32)
        {
                $Mail = RNCPHP\Configuration::fetch( CUSTOM_CFG_MAIL_CREDITO );
                $StandardContent = RNCPHP\StandardContent::fetch(35);
                $mm = new RNCPHP\MailMessage();
                //$mm->To->EmailAddresses = array('rtorrens@dimacofi.cl');
                $mm->To->EmailAddresses = array($Mail->Value);
                $mm->Subject = "Porblemas de Bloqueo Cliente: NUMERO TICKET["  . $obj_incident->ReferenceNumber ."]";
                $mm->Body->Html = $mm->Body->Html .  $bloqueos->message;
                $mm->Body->Html = $mm->Body->Html . $StandardContent->ContentValues[1]->Value;
                $mm->Body->Html = $mm->Body->Html . $body;
    
                $mm->send();
                $params['Sent']=$mm->Status->Sent;
                if($mm->Status->Sent)
                {
                //Success
                }
                else
                {
                //Failure
                }
        }
        
       // echo json_encode($params,false,3);
    }
}