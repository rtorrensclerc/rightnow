

<div id="rn_<?=$this->instanceID ?>" class="<?=$this->classList ?> form-request">
    <div class="rn_Padding">
        <rn:container>

            <h1>Búsqueda de Rápida de Facturas</h1>
            <div class="rn_FieldDisplay rn_Output">
                    <rn:widget path="custom/input/InputField" id="invoice_number" name="invoice_number" label_input="Número de Factura" value="" display_type="number" required="false" wide="false" />
            </div>
            <div class="rn_FieldDisplay rn_Output">
                    <rn:widget path="custom/input/InputField" maxlength ="20" id="invoice_rut" name="invoice_rut" label_input="RUT" value="" display_type="number" required="false" wide="false" />
            </div>
            <!--div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
            <rn:widget path="custom/input/SelectField" id="contract_list" name="contract_list" label_input="Contrato" value="#rn:php:$this->data['js']['contract_list']#" display_type="lista" required="false" wide="true" />
            </div-->
            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
                <rn:widget path="custom/input/InputField" id="invoice_from" name="invoice_from" label_input="Fecha Desde" value="" display_type="date" required="false" wide="true" />
            </div>
            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
                <rn:widget path="custom/input/InputField" id="invoice_to" name="invoice_to" label_input="Fecha Hasta" value="" display_type="date" required="false" wide="true" />
            </div>
            

            <div class="rn_FieldDisplay rn_Output">
                <input type="button" id="btn_get_invoice" name="btn_get_invoice" value="Buscar Factura">
                <input type="button" id="btn_erase" name="btn_erase" value="Limpiar">

            </div>

            <div class="rn_ContentTab rn_ContentTab_LastInvoices" style="display:block;">
                <div class="rn_Grid">
                    <table class="yui3-datatable-table rn_LastInvoices">
                        <thead>
                            <tr>
                                <th data-key="trx_number" class="rn_TextCenter">Nº Documento</th>
                                <th data-key="rut" class="rn_TextCenter" style="width:140px">RUT</th>
                                <th data-key="contrat" class="rn_TextCenter">Contrato</th>
                                <th data-key="trx_date" class="rn_TextCenter">Fecha Emisión</th>
                                <th data-key="due_date" class="rn_TextCenter">Fecha Vencimiento</th>
                                <th data-key="amount" class="rn_TextRight">Monto</th>
                                <th data-key="ammount_remaining" class="rn_TextRight">Saldo</th>
                                <th data-key="url_dte" class="rn_TextCenter">Estado</th>
                                <th data-key="url_dte" class="rn_TextCenter">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="template" style="display:none;">
                                <td data-key="trx_number" data-format="text" class="rn_TextCenter">0</td>
                                <td data-key="rut" data-format="text" class="rn_TextCenter">123456789-0</td>
                                <th data-key="contrat" class="rn_TextCenter">Contrato</th>
                                <td data-key="trx_date" data-format="date" class="rn_TextCenter">00/00/0000</td>
                                <td data-key="due_date" data-format="date" class="rn_TextCenter">00/00/0000</td>
                                <td data-key="amount" data-format="currency" class="rn_TextRight">$ 000</td>
                                <td data-key="ammount_remaining" data-format="currency" class="rn_TextRight">$ 000</td>
                                <td data-key="trx_paid" data-format="text" class="rn_TextCenter">-</td>
                                <td data-key="trx_urls" data-format="node" class="rn_TextCenter">-</td>
                            </tr>

                            <tr class="no_data">
                                <td data-key="_" colspan="7" class="rn_TextCenter">( Sin Registros )</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>




            <div class="rn_ContentTab rn_ContentTab_detail" style="display:none;">
                

                <div class="rn_Grid">
                <a href="#" class="btn btn_downloadCSV">Descargar CSV</a>
                <a href="#" class="btn btn_downloadXLSX">Descargar Excel</a>
                    <table class="yui3-datatable-table rn_detail">
                        <thead>
                            <tr>
                            <th data-key="hh" class="rn_TextRight">HH</th>
                            <th data-key="serie" class="rn_TextRight">Serie</th>
                            <th data-key="model" class="rn_TextLeft">Modelo</th>
                            <th data-key="divisa" class="rn_TextLeft">divisa</th>
                            <th data-key="exchange_rate" class="rn_TextLeft">Tasa de cambio</th>                   
                            <th data-key="fixed_amount" class="rn_TextRight">Cargo Fíjo</th>
                            <th data-key="last_date" class="rn_TextCenter">Fecha Anterior</th>
                            <th data-key="last_read" class="rn_TextRight">Lectura Anterior</th>
                            <th data-key="actual_read" class="rn_TextRight">Lectura Actual</th>
                            <th data-key="counter_type" class="rn_TextLeft">Tipo de Contador</th>
                            <th data-key="credit" class="rn_TextRight">Crédito</th>
                            <th data-key="real_quantity" class="rn_TextRight">Clicks Reales</th>
                            <th data-key="billed_quantity" class="rn_TextRight">Clicks Facturados</th>
                            <th data-key="rate" class="rn_TextRight">Tarifa</th>
                            <th data-key="amount" class="rn_TextRight">Monto</th>
                            <th data-key="address" class="rn_TextLeft">Dirección</th>              
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="template" style="display:none;">
                            <td data-key="hh" class="rn_TextRight">-</td>
                            <td data-key="serie" class="rn_TextRight">-</td>
                            <td data-key="model" class="rn_TextLeft">-</td>
                            <td data-key="divisa" class="rn_TextLeft">-</td>
                            <td data-key="exchange_rate" class="rn_TextLeft">-</td>              
                            <td data-key="fixed_amount" data-format="point2dot" class="rn_TextRight">-</td>
                            <td data-key="last_date" data-format="date" class="rn_TextCenter">-</td>
                            <td data-key="last_read" data-format="date" class="rn_TextRight">-</td>
                            <td data-key="actual_read" data-format="number" class="rn_TextRight">-</td>
                            <td data-key="counter_type" data-format="number" class="rn_TextLeft">-</td>
                            <td data-key="credit" data-format="currency" class="rn_TextRight">-</td>
                            <td data-key="real_quantity" data-format="number" class="rn_TextRight">-</td>
                            <td data-key="billed_quantity" data-format="number" class="rn_TextRight">-</td>
                            <td data-key="rate" data-format="point2dot" class="rn_TextRight">-</td>
                            <td data-key="amount" data-format="currency" class="rn_TextRight">-</td>
                            <td data-key="address" class="rn_TextLeft">-</td>
                            </tr>

                            <tr class="no_data">
                                <td data-key="_" colspan="7" class="rn_TextCenter">( Sin Registros )</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="rn_ContentTab rn_ContentTab_Loading" style="display:none;">
            <rn:widget path="custom/Info/waiting" />
            </div>
        </rn:container>

    </div>
</div>