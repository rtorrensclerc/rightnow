

<div id="rn_<?=$this->instanceID ?>" class="<?=$this->classList ?> form-request">
    <div class="rn_Padding">
        <rn:container>
        <img  url="images/layout/status_hh.png"></img>
            <div class="message">
            #rn:msg:CUSTOM_MSG_HH_STATUS#
            </div>
            <div id="rn_ErrorLocation" class="rn_MessageBox rn_ErrorMessage" hidden="hidden" style="display: none;">
	    
	    <div class="messages">
	    </div>
	  </div>
            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
                 <rn:widget path="custom/input/SelectField" id="rut_list" name="rut_list" label_input="RUT" value="#rn:php:$this->data['status_list']#" display_type="lista" required="false" wide="true" />
            </div>
            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
                <rn:widget path="custom/input/InputField" id="HH" name="HH" label_input="HH" value="" display_type="text" required="false" wide="true" />
            </div>
            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
                <rn:widget path="custom/input/InputField" id="Serie" name="Serie" label_input="Serie" value="" display_type="text" required="false" wide="true" />
            </div>
            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
            <rn:widget path="custom/input/SelectField" id="status_list" name="status_list" label_input="Status" value="#rn:php:$this->data['status_list']#" display_type="lista" required="false" wide="true" />
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
                                <th data-key="CONTRATO" class="rn_TextCenter">Nro Contrato</th>
                                <th data-key="HH" class="rn_TextCenter">HH</th>
                                <th data-key="SERIAL" class="rn_TextCenter">Serial</th>                                
                                <th data-key="MODELO" data-format="text" class="rn_TextCenter">Modelo</th>
                                <th data-key="ULTIMA_CONEXION" class="rn_TextCenter">Última Conexión</th>
                                <th data-key="DIAS_OFFLINE" class="rn_TextCenter" >Días Offline</th>
                                <th data-key="NUBEPRINT" class="rn_TextCenter" >Estado</th>
                                <th data-key="IP" class="rn_TextCenter" >IP</th>
                                <th data-key="ADDRESS" class="rn_TextCenter" >Dirección</th>
                              
                        
                  
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="template" style="display:none;">
                                <td data-key="CONTRATO" data-format="text" class="rn_TextCenter">XXXXXX</td>
                                <td data-key="HH" data-format="text" class="rn_TextCenter">XXXXXX</td>
                                <td data-key="SERIAL" data-format="node" class="rn_TextCenter">XXXXXXXXXXXXXXX</td>
                                <th data-key="MODELO" data-format="text" class="rn_TextCenter">Empresa S.A</th>
                                <td data-key="ULTIMA_CONEXION" data-format="date" class="rn_TextCenter">2022-07-25 18:59:36</td>
                                <td data-key="DIAS_OFFLINE" data-format="node" class="rn_TextCenter">XXXXXXXXX</td>
                                <td data-key="NUBEPRINT" data-format="node" class="rn_TextCenter">XXXXXXXXXXXX</td>
                                <td data-key="IP" data-format="node" class="rn_TextCenter">XXX</td>
                                <td data-key="ADDRESS" data-format="text" class="rn_TextCenter">XXXXXXXXXX</td>
             
                                
                
                                
                           
                            </tr>

                            <tr class="no_data">
                                <td data-key="_" colspan="8" class="rn_TextCenter">( Sin Registros )</td>
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