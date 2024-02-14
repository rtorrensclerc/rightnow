<rn:widget path="custom/redirect/RedirectBlocked" module_id="6" error_page="/app/sv/error.php" />
<rn:meta title="Busqueda de Informacion de Factura" template="2020.php" clickstream="invoice_payments" login_required="true"/>

<div class="rn_PageContent rn_Wraper">
  <div class="rn_PageContentInner rn_ListPaymentsAndInvoices">
    <div id="rn_ErrorLocatio" style="display:none;"></div>
    <rn:widget path="custom/payments/SearchInvoice" />
  </div>
</div>
