<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">

    <h2>
        <p>
            Nuestros sistemas indican que existen restricciones para generar solicitudes
        </p>
    </h2>

    <div class="rn_ContentTab rn_ContentTab_LastInvoices" style="display:block;">
        <div class="rn_Grid">
            <table class="yui3-datatable-table rn_LastInvoices">
                <tr>
                    <th class="rn_TextLeft">RUT</th>
                    <th class="rn_TextLeft"><?=$this->data['orgStatus']->Customer->CustomerData->Customer->tRUT?></th>
                </tr>
                <tr>
                    <th class="rn_TextLeft">Cliente</th>
                    <th class="rn_TextLeft"><?=$this->data['Organizacion']->LookupName?></th>
                </tr>
                <?  $bloqueado=0;
            
            if($this->data['orgStatus']->BlockAddress->List->data)
            {
                ?><tr>
                <th class="rn_TextLeft">Sucursales Bloqueadas</th><?
                ?><th class="rn_TextLeft"><?

                $bloqueado=1;
               foreach( $this->data['orgStatus']->BlockAddress->List->data as $Sucursal)
               {
                   if($Sucursal->DIRECCION)
                   {
                    echo $Sucursal->DIRECCION . '<br>';
                    }
                    else
                    {
                        echo  $Sucursal;
                    }


               }
               ?></th><?
                ?>
                </tr>
                <?


            }
            if($this->data['orgStatus']->Customer->CustomerData->Customer->tbloqued=='Y') 
            {
                $bloqueado=1;
        ?>
                <tr>
                    <th class="rn_TextLeft">Bloqueado por Deuda Morosa</th>
                    <th class="rn_TextLeft">SI
                    <input type="hidden" id="id_mora" name="id_mora" value="<?=$this->data['orgStatus']->Customer->CustomerData->Customer->tbloqued?>"></th>

                </tr>
                <?  
            }
        ?>

                <?  if($this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_FACTURACION=='SI') 
            {
                $bloqueado=1;
        ?>
                <tr>
                    <th class="rn_TextLeft">Bloqueado por Rechazo de Facturas</th>
                    <th class="rn_TextLeft">
                        <?=$this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_FACTURACION?>
                        <input type="hidden" id="id_factura" name="id_factura" value="<?=$this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_FACTURACION?>"></th>
                        </th>
                </tr>
                <?  
            }
        ?>
                <?  if($this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_INFORMACION=='SI') 
            {
                $bloqueado=1;
        ?>
                <tr>
                    <th class="rn_TextLeft">Bloqueado por Informacion Financiera incompleta</th>
                    <th class="rn_TextLeft">
                        <?=$this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_INFORMACION?>
                        <input type="hidden" id="id_info"  name="id_info" value="<?=$this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_INFORMACION?>"></th>
                </tr>
                <?  
            }
        ?>

                <?  if($this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_RIESGO=='SI') 
            {
                $bloqueado=1;
        ?>
                <tr>
                    <th class="rn_TextLeft">Bloqueado por Situacion de Riesgo</th>
                    <th class="rn_TextLeft">
                        <?=$this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_RIESGO?>
                        <input type="hidden" id="id_riesgo" name="id_riesgo"  value="<?=$this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_RIESGO?>"></th>
                </tr>
                <?  
            }
        ?>
                <?  if($this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_DEUDAS=='SI') 
            {
                $bloqueado=1;
        ?>
                <tr>
                    <th class="rn_TextLeft">Bloqueado por Castigo de Deudas Antiguas</th>
                    <th class="rn_TextLeft">
                        <?=$this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_DEUDAS?>
                        <input type="hidden" id="id_deuda" name="id_deuda" value="<?=$this->data['orgStatus']->Customer->CustomerData->Customer->tBLOQUEO_DEUDAS?>">
                        </th>

                </tr>
                <?  
            }
        ?>
            </table>
        </div>
    </div>

    <?
    if( $bloqueado==1)
    {
    ?>
    <br>
    <th>
        <? if ($_POST['enviar']!='Enviar Informe') 
        {
        ?>
        <!--input type="button" id="btn_Notif" name="btn_Notif" value="Enviar Notificación Interna"-->
        <form id="rn_QuestionSubmit" action="" method="post">
            <input  name="enviar" type="submit" value="Enviar Informe">
        </form>
        <?
        }
    ?>
    <input type="hidden" id="id_incidente" name="id_incidente" value="<?=$this->data['incident_id']?>">
    </th>
    <?
    }
    ?>
    <h2>
        <p>
            Listado de Facturas pendientes
        </p>
    </h2>
    <div class="rn_ContentTab rn_ContentTab_LastInvoices" style="display:block;">
        <div class="rn_Grid">
            <table class="yui3-datatable-table rn_LastInvoices">
                <thead>
                    <tr>
                        <th class="rn_TextRight">Nro Factura</th>
                        <th class="rn_TextRight">Contrato</th>
                        <th class="rn_TextRight">Monto</th>
                        <th class="rn_TextRight">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?
    //facturas inpagas
setlocale(LC_MONETARY, 'es_CL');
    if(count($this->data['orgStatus']->Invoice->InvoiceData->Invoices)==1)
    {
        ?>
                    <tr>
                        <td class="rn_TextRight">
                            <?=$this->data['orgStatus']->Invoice->InvoiceData->Invoices->TRX_NUMBER?></td>
                        <td class="rn_TextRight">
                            <?=($this->data['orgStatus']->Invoice->InvoiceData->Invoices->CT_REFERENCE)?$this->data['orgStatus']->Invoice->InvoiceData->Invoices->CT_REFERENCE:'(Sin Valor)' ?>
                        </td>
                        <td class="rn_TextRight">
                            <?= money_format('%.0n', $this->data['orgStatus']->Invoice->InvoiceData->Invoices->AMOUNT) ?>
                        </td>
                        <td class="rn_TextRight"><?=$this->data['orgStatus']->Invoice->InvoiceData->Invoices->DUE_DATE?>
                        </td>
                    </tr>
                    <?
    }
    else
    {
        if(count($this->data['orgStatus']->Invoice->InvoiceData->Invoices)>1)
        {
            foreach ( $this->data['orgStatus']->Invoice->InvoiceData->Invoices as $Invoice)
            {

            ?>
                        <tr>
                            <td class="rn_TextRight"><?=$Invoice->TRX_NUMBER?></td>
                            <td class="rn_TextRight"><?=($Invoice->CT_REFERENCE)?$Invoice->CT_REFERENCE:'(Sin Valor)' ?>
                            </td>
                            <td class="rn_TextRight"><?= money_format('%.0n', $Invoice->AMOUNT) ?></td>
                            <td class="rn_TextRight"><?=$Invoice->DUE_DATE?></td>
                        </tr>

                        <?
            }
       }
    }

    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    </div>