<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">


<rn:container>
        <div class="message">
        #rn:msg:CUSTOM_MSG_SUPPLIER_STATUS#
        </div>

            <div class="rn_ContentTab rn_ContentTab_search_transaccions" style="display:block;">
                <div class="rn_Grid">
                    <table class="yui3-datatable-table rn_search_transaccions">          
                        <thead>
                            <tr>
                                <th data-key="EVENT_DATE_O" class="rn_TextCenter" >Fecha</th>  
                                <th data-key="STATUS_DESC_O" class="rn_TextCenter" >Estado</th>                  
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="template" style="display:none;">
                                <td data-key="EVENT_DATE_O" data-format="date" class="rn_TextCenter">XXXXXX</td>
                                <td data-key="STATUS_DESC_O" data-format="text" class="rn_TextCenter">estado</td>
                            </tr>
                            <tr class="no_data">
                                <td data-key="_" colspan="11" class="rn_TextCenter">( Sin Registros )</td>
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