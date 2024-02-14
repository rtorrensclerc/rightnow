

<div id="rn_<?=$this->instanceID ?>" class="<?=$this->classList ?> form-request">
    <div class="rn_Padding">
        <rn:container>

            <h1>Búsqueda de Información Contadores Historicos</h1>
            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
                <rn:widget path="custom/input/InputField" id="HH" name="HH" label_input="HH" value="" display_type="text" required="false" wide="true" />
            </div>
            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
                <rn:widget path="custom/input/InputField" id="Serie" name="Serie" label_input="Serie" value="" display_type="text" required="false" wide="true" />
            </div>
            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
                <rn:widget path="custom/input/InputField" id="invoice_from" name="invoice_from" label_input="Fecha Desde" value="" display_type="date" required="false" wide="true" />
            </div>
            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
                <rn:widget path="custom/input/InputField" id="invoice_to" name="invoice_to" label_input="Fecha Hasta" value="" display_type="date" required="false" wide="true" />
            </div>
            
		       

            <div class="rn_FieldDisplay rn_Output">
                <input type="button" id="btn_get_trx" name="btn_get_trx" value="Buscar">
                <input type="button" id="btn_erase" name="btn_erase" value="Limpiar">
                <a href="#" class="btn btn_downloadXLSX" >Descargar Excel</a>

            </div>

            <div class="rn_ContentTab rn_ContentTab_search_transaccions" style="display:block;">
                <div class="rn_Grid">
                    <table class="yui3-datatable-table rn_search_transaccions">
                        <thead>
                            <tr>
                                
                               
                                <th data-key="HH" class="rn_TextCenter" style="width:140px">HH</th>
                                <th data-key="SERIAL" class="rn_TextCenter">Serial</th>
                                <th data-key="MODELO" class="rn_TextCenter">Modelo</th>
                                <th data-key="FECHA_ACTUALIZACION" class="rn_TextCenter">Fecha última actualización</th>
                                <th data-key="CONTADOR_BN" class="rn_TextCenter">Ultima Lectura BN</th>
                                <th data-key="CONTADOR_COLOR" class="rn_TextCenter">Última Lectura Color</th>
                                <th data-key="CONTADOR_A3_BN" class="rn_TextCenter">Última Lectura A3 BN</th>
                                <th data-key="CONTADOR_A3_COLOR" class="rn_TextCenter">Última Lectura A3 Color</th>
                                <th data-key="CONTADOR_B4_BN" class="rn_TextCenter">Última Lectura B4 BN</th>
                                <th data-key="CONTADOR_B4_COLOR" class="rn_TextCenter">Última Lectura B4 Color</th>
                                <th data-key="CONTADOR_DUPL" class="rn_TextCenter">Última Lectura Dupl</th>
                                <th data-key="CONTADOR_METRO" class="rn_TextCenter">Última Lectura  Metro</th>
                                <th data-key="CONTADOR_SCANNER_BN" class="rn_TextCenter">Última Lectura Scanner BN</th>
                                <th data-key="CONTADOR_SCANNER_COLOR" class="rn_TextCenter">Última Lectura Scanner Color</th>
                               
                                <!-- th data-key="ORIGEN" class="rn_TextCenter">Origen</th -->
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="template" style="display:none;">
                                
                                <td data-key="HH" data-format="text" class="rn_TextCenter">XXXXXX</td>
                                <td data-key="SERIAL" data-format="date" class="rn_TextCenter">XXXXXXXXXXXXXXX</td>
                                <th data-key="MODELO" class="rn_TextCenter">Empresa S.A</th>
                                <td data-key="FECHA_ACTUALIZACION" data-format="node" class="rn_TextCenter"></td>
                                <td data-key="CONTADOR_BN" data-format="node" class="rn_TextCenter"></td>
                                <td data-key="CONTADOR_COLOR" data-format="node" class="rn_TextCenter"></td>
                                <td data-key="CONTADOR_A3_BN" data-format="node" class="rn_TextCenter"></td>
                                <td data-key="CONTADOR_A3_COLOR" data-format="node" class="rn_TextCenter"></td>
                                <td data-key="CONTADOR_B4_BN" data-format="node" class="rn_TextCenter"></td>
                                <td data-key="CONTADOR_B4_COLOR" data-format="node" class="rn_TextCenter"></td>
                                <td data-key="CONTADOR_DUPL" data-format="node" class="rn_TextCenter"></td>
                                <td data-key="CONTADOR_METRO" data-format="node" class="rn_TextCenter"></td>
                                <td data-key="CONTADOR_SCANNER_BN" data-format="node" class="rn_TextCenter"></td>
                                <td data-key="CONTADOR_SCANNER_COLOR" data-format="node" class="rn_TextCenter"></td>
                                
                            
                                <!-- td data-key="ORIGEN" data-format="node" class="rn_TextCenter"></td -->
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