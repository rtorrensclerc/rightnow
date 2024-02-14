<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">

<?php

        if(!empty($this->data['js']['voucher']))
        {
            //var_dump($this->data['js']['voucher']['Time']);


            //var_dump($this->data['js']['voucher']['Status']->LookupName);
            switch($this->data['js']['voucher']['Status']->ID)
            {
                case 1:
                    ?>
                <div class="rn_SuccessMesage"">
                    <div class="message">
                        <h2 style="text-align: center">Su pago fue realizado con éxito.</h2>
                        <div class="rn_FieldDisplay rn_Output"  style="text-align: left">
                            <span class="rn_DataLabel">CLIENTE</span>
                            <div class="rn_DataValue rn_LeftJustify"><?= $this->data['js']['voucher']['Organization']->Name ?></div>
                        </div>
                        <div class="rn_FieldDisplay rn_Output" style="text-align: left">
                            <span class="rn_DataLabel">RUT</span>
                            <div class="rn_DataValue rn_LeftJustify"><?= $this->data['js']['voucher']['Organization']->CustomFields->c->rut ?></div>
                        </div>
                        <div class="rn_FieldDisplay rn_Output" style="text-align: left">
                            <span class="rn_DataLabel">FECHA</span>
                            <div class="rn_DataValue rn_LeftJustify"><?= $this->data['js']['voucher']['Date'] ?></div>
                        </div>
                        <div class="rn_FieldDisplay rn_Output" style="text-align: left">
                            <span class="rn_DataLabel">HORA</span>
                            <div class="rn_DataValue rn_LeftJustify"><?= $this->data['js']['voucher']['Time'] ?></div>
                        </div>
                        <div class="rn_FieldDisplay rn_Output" style="text-align: left">
                            <span class="rn_DataLabel">FACTURA</span>
                            <div class="rn_DataValue rn_LeftJustify" ><?= $this->data['js']['voucher']['InvoiceNumber'] ?></div>
                        </div>
                        <div class="rn_FieldDisplay rn_Output" style="text-align: left">
                            <span class="rn_DataLabel">MONTO</span>
                            <div class="rn_DataValue rn_LeftJustify" >$ <?= number_format($this->data['js']['voucher']['Amount'],0,",",".") ?></div>
                        </div>
                        <div class="rn_FieldDisplay rn_Output" style="text-align: left">
                            <span class="rn_DataLabel">NUMERO DE CUOTAS</span>
                            <div class="rn_DataValue rn_LeftJustify"><?= $this->data['js']['voucher']['ShareNumber'] ?></div>
                        </div>
                        <div class="rn_FieldDisplay rn_Output" style="text-align: left">
                            <span class="rn_DataLabel">Estado de Transacción</span>
                            <div class="rn_DataValue rn_LeftJustify"><?= $this->data['js']['voucher']['ResponseCode'] ?></div>
                        </div>
                    </div>
                </div>
                    <?
                break;
            case 2:
              
                ?>
                <div class="rn_SuccessMesage">
                <div class="message">
                    <h2>Su pago no pudo ser procesado</h2>
                </div>
                </div>
                <?
                break;
            case 3:
              
                ?>
                <div class="rn_SuccessMesage"">
                    <div class="message">
                        
                        
                        <div class="rn_FieldDisplay rn_Output" style="text-align: left">
                            <span class="rn_DataLabel">Estado de Transacción</span>
                            <div class="rn_DataValue rn_LeftJustify"><?= $this->data['js']['voucher']['Status']->LookupName ?></div>
                        </div>
                    </div>
                </div>
                    <?
                break;
            }
        }
        else
        {
            ?>
                    
           
             <div class="rn_SuccessMesage">
             <div class="message">
                 <h2>Su pago no pudo ser procesado</h2>
             </div>
             </div>
             <?
        }
   ?>

</div>
