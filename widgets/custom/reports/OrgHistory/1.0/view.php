
<?
 // echo json_encode($this->data['js']['incidents']);

?>

<div id="rn_<?=$this->instanceID ?>" class="<?=$this->classList ?> form-request">
    <div class="rn_Padding">
        <rn:container>

            <h1>Búsqueda de Tickets</h1>
            <div class="rn_FieldDisplay rn_Output">
                    <rn:widget path="custom/input/InputField" maxlength ="20" id="ticket_number" name="ticket_number" label_input="Nro Ticket" value="" display_type="Text" required="false" wide="false" />
            </div>
            <!--div class="rn_FieldDisplay rn_Output">
                    <rn:widget path="custom/input/InputField" maxlength ="20" id="ticket_estado" name="ticket_estado" label_input="Estado" value="" display_type="number" required="false" wide="false" />
            </div>
            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
                <rn:widget path="custom/input/InputField" id="ticket_sol" name="ticket_sol" label_input="Solicitante" value="" display_type="Text" required="false" wide="true" />
            </div-->
            

            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
                <rn:widget path="custom/input/InputField" id="ticket_from" name="ticket_from" label_input="Desde" value="" display_type="date" required="false" wide="true" />
            </div>
            

            <div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
                <rn:widget path="custom/input/InputField" id="ticket_to" name="ticket_to" label_input="Hasta" value="" display_type="date" required="false" wide="true" />
            </div>
            

            <div class="rn_FieldDisplay rn_Output">
                <input type="button" id="btn_get_tickets" name="btn_get_tickets" value="Buscar Tickets">
                <input type="button" id="btn_erase" name="btn_erase" value="Limpiar">

            </div>

            <div class="rn_ContentTab rn_ContentTab_Tickets" style="display:block;">
                <div class="rn_Grid">
                    <table class="yui3-datatable-table rn_LastTickets">
                        <thead>
                            <tr>
                                <th data-key="tiket" class="rn_TextRight">Nro Ticket</th>
                                <th data-key="Fecha" class="rn_TextRight" style="width:140px">Fecha de creación</th>
                                <th data-key="trx_date" class="rn_TextCenter">Tipo Soporte </th>
                                <th data-key="trx_date" class="rn_TextCenter">HH</th>
                                <th data-key="trx_date" class="rn_TextCenter">Estado</th>

                            </tr>
                        </thead>
                        <tbody>
                            <tr class="template" style="display:none;">
                                <td data-key="trx_number" data-format="text" class="rn_TextRight">0</td>
                                <td data-key="rut" data-format="text" class="rn_TextRight">123456789-0</td>
                                <th data-key="contrat" class="rn_TextCenter">Contrato</th>
                                <td data-key="trx_date" data-format="date" class="rn_TextCenter">00/00/0000</td>
                                
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