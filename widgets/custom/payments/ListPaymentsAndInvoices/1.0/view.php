<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
  <!--rn:widget path="custom/input/SelectField" id="ruts_list" name="ruts_list" label_input="Ruts relacionados" value="#rn:php:$this->data['js']['StatusSai.Ruts']#" display_type="lista" required="true" /-->
  <rn:widget path="custom/input/SelectField" id="contract_list" name="contract_list" label_input="Contrato" value="#rn:php:$this->data['js']['contract_list']#" display_type="lista" required="true" />
  

  <div class="rn_Tabs">
    <div class="rn_MenuTabs">
      <ul>
        <li data-name="LastInvoices" class="rn_MenuItemTab rn_LastInvoices active">
          <a href="#">Últimas Facturas</a>
        </li>
        <li data-name="LastPayments" class="rn_MenuItemTab rn_LastPayments">
          <a href="#">Histórico de Facturas Pagadas</a>
        </li>
      </ul>
    </div>
    <div class="rn_ContentTabs">
      <div class="rn_ContentTab rn_ContentTab_LastInvoices">
        <div class="rn_Grid">
          <table class="yui3-datatable-table rn_LastInvoices">
            <thead>
              <tr>
                <th data-key="trx_text" class="rn_TextCenter">Nº Documento</th>
                <th data-key="trx_date" class="rn_TextCenter">Fecha Emisión</th>
                <th data-key="due_date" class="rn_TextCenter">Fecha Vencimiento</th>
                <th data-key="trx_amount" class="rn_TextRight">Monto</th>
                <th data-key="trx_amount" class="rn_TextRight">Saldo</th>
                <th data-key="url_dte" class="rn_TextCenter">Estado</th>
                <th data-key="url_dte" class="rn_TextCenter">Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr class="template" style="display:none;">
                <td data-key="trx_number" data-format="text" class="rn_TextCenter">0</td>
                <td data-key="trx_date" data-format="date" class="rn_TextCenter">00/00/0000</td>
                <td data-key="due_date" data-format="date" class="rn_TextCenter">00/00/0000</td>
                <td data-key="trx_amount" data-format="currency" class="rn_TextRight">$ 000</td>
                <td data-key="amount_remaining" data-format="currency" class="rn_TextRight">$ 000</td>
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

      <div class="rn_ContentTab rn_ContentTab_LastPayments" style="display:none;">
        <div class="rn_Grid">
          <table class="yui3-datatable-table rn_LastPayments">
            <thead>
              <tr>
                <th data-key="trx_date" class="rn_TextCenter">Nº Factura</th>
                <th data-key="trx_number" class="rn_TextCenter">Fecha Transacción</th>
                <th data-key="trx_amount" class="rn_TextRight">Monto</th>
              </tr>
            </thead>
            <tbody>
              <tr class="template" style="display:none;">
                <td data-key="trx_number" data-format="number" class="rn_TextCenter">0</td>
                <td data-key="trx_date" data-format="date" class="rn_TextCenter">00/00/0000</td>
                <td data-key="trx_amount" data-format="currency" class="rn_TextRight">$ 000</td>
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
    </div>
  </div>

</div>