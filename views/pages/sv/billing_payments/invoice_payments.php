


<rn:widget path="custom/redirect/RedirectBlocked" module_id="6" error_page="/app/sv/error.php">
<rn:meta title="Últimos Documentos Emitidos" template="2020.php" clickstream="invoice_payments" login_required="true"/>

<div class="rn_PageContent rn_Wraper">
  
  <div class="rn_PageContentInner rn_ListPaymentsAndInvoices">
    <div id="rn_ErrorLocatio" style="display:none;"></div>
    <rn:widget path="custom/payments/ListPaymentsAndInvoices" />
  </div>

  <div class="rn_PageContentInner rn_BillDetailContent" style="display: none;">
    <div class="rn_ContentTab rn_ContentTab_LastInvoices">
      <a href="#" class="btn btn_summary">Volver al Resumen</a>
      <a href="#" class="btn btn_downloadCSV">Descargar CSV</a>
      <a href="#" class="btn btn_downloadXLSX">Descargar Excel</a>
      <div class="rn_Grid">
        <table class="yui3-datatable-table rn_BillDetail">
          <thead>
            <tr>
              <th data-key="hh" class="rn_TextCenter">HH</th>
              <th data-key="serie" class="rn_TextCenter">Serie</th>
              <th data-key="model" class="rn_TextCenter">Modelo</th>
              <th data-key="fixed_amount" class="rn_TextRight">Cargo Fíjo</th>
              <th data-key="last_date" class="rn_TextCenter">Fecha Anterior</th>
              <th data-key="last_read" class="rn_TextRight">Lectura Anterior</th>
              <th data-key="actual_read" class="rn_TextCenter">Fecha lectura</th>
              <th data-key="actual_date" class="rn_TextRight">Lectura Actual</th>
              <th data-key="counter_type" class="rn_TextLeft">Tipo de Contador</th>
              <th data-key="credit" class="rn_TextRight">Crédito</th>
              <th data-key="real_quantity" class="rn_TextRight">Clicks Reales</th>
              <th data-key="billed_quantity" class="rn_TextRight">Clicks Facturados</th>
              <th data-key="rate" class="rn_TextRight">Tarifa</th>
              <th data-key="amount" class="rn_TextRight">Monto</th>
              <th data-key="address" class="rn_TextCenter">Dirección</th>
            </tr>
          </thead>
          <tbody>
            <tr class="template" style="display:none;">
              <td data-key="hh" class="rn_TextCenter">-</td>
              <td data-key="serie" class="rn_TextCenter">-</td>
              <td data-key="model" class="rn_TextCenter">-</td>
              <td data-key="fixed_amount" data-format="currency" class="rn_TextRight">-</td>
              <td data-key="last_date" data-format="date" class="rn_TextCenter">-</td>
              <td data-key="last_read" data-format="date" class="rn_TextRight">-</td>
              <td data-key="actual_date" data-format="date" class="rn_TextCenter">-</td>
              <td data-key="actual_read" data-format="number" class="rn_TextRight">-</td>
              <td data-key="counter_type" data-format="number" class="rn_TextLeft">-</td>
              <td data-key="credit" data-format="currency" class="rn_TextRight">-</td>
              <td data-key="real_quantity" data-format="number" class="rn_TextRight">-</td>
              <td data-key="billed_quantity" data-format="number" class="rn_TextRight">-</td>
              <td data-key="rate" data-format="point2dot" class="rn_TextRight">-</td>
              <td data-key="amount" data-format="currency" class="rn_TextRight">-</td>
              <td data-key="address" class="rn_TextCenter">-</td>
            </tr>
            <tr class="no_data">
              <td data-key="trx_number" colspan="14" class="rn_TextCenter">( Sin Registros )</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
