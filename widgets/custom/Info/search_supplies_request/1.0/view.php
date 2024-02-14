

<div id="rn_<?=$this->instanceID ?>" class="<?=$this->classList ?> form-request">
    <div class="rn_Padding">
        <rn:container>
        <div class="message">
        #rn:msg:CUSTOM_MSG_SUPPLIER_STATUS#
        </div>
        

            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
                <rn:widget path="custom/input/InputField" id="HH" name="HH" onkeypress="this.chequer();" label_input="HH" value="" display_type="text" required="false" wide="true" />
            </div>
            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
                <rn:widget path="custom/input/InputField" id="Serie" name="Serie" label_input="Serie" value="" display_type="text" required="false" wide="true" />
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
                                <th data-key="ID" class="rn_TextCenter" >N° Referencia</th>
                                <th data-key="RUT" class="rn_TextCenter" >RUT Empresa</th>
                                <th data-key="Fecha" class="rn_TextCenter">Fecha de solicitud</th>                                
                                <th data-key="disp_id" class="rn_TextCenter">Tipo de solicitud</th>
                                <th data-key="subject" class="rn_TextCenter">Asunto</th>
                                <th data-key="HH" class="rn_TextCenter" >HH</th>
                                <th data-key="Estado" class="rn_TextCenter" >Estado</th>
                                <th data-key="contacto" class="rn_TextCenter" >Contacto</th>
                                <th data-key="Serie" class="rn_TextCenter" >Serie</th>
                                <th data-key="FechaC" class="rn_TextCenter" >Fecha Cierre</th>
                 
                        
                  
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="template" style="display:none;">
                                <td data-key="ID" data-format="text" class="rn_TextCenter">XXXXXX</td>
                                <td data-key="RUT" data-format="text" class="rn_TextCenter">123411115-5</td>
                                <td data-key="Fecha" data-format="date" class="rn_TextCenter">2022-07-25 18:59:36</td>
                                <td data-key="disp_id" data-format="text" class="rn_TextCenter">XXXXXX</td>
                                <td data-key="subject" data-format="text" class="rn_TextCenter">XXXXXXXXXXXXXX</td>
                                <td data-key="HH" data-format="node" class="rn_TextCenter">XXXXXXXXXXXXXXX</td>
                                <th data-key="Estado" class="rn_TextCenter">Conectado</th>
                                <td data-key="contacto" data-format="text" class="rn_TextCenter">Juan</td>
                                <td data-key="Serie" data-format="node" class="rn_TextCenter">12345</td>
                                <td data-key="FechaC" data-format="date" class="rn_TextCenter">2022-07-25 18:59:36</td>
                                

                            </tr>

                            <tr class="no_data">
                                <td data-key="_" colspan="10" class="rn_TextCenter">( Sin Registros )</td>
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