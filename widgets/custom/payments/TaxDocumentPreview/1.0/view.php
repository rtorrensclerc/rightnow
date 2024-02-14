<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
  <div class="rn_Document">

    <div class="rn_HeaderDocument">
      <div class="rn_DocumentValue header_invoice_number"></div>
    </div>

    <div class="rn_ContentDocument">
      <div class="rn_HeaderContent">
        <div class="rn_DocumentValue header_customer_name"></div>
        <div class="rn_DocumentValue header_customer_number"></div>
        <div class="rn_DocumentValue header_giro"></div>
        <div class="rn_DocumentValue header_invoice_addr"></div>
        <div class="rn_DocumentValue header_due_date"></div>
      </div>

      <div class="rn_DetailContent">
        <table>
          <tr class="template">
            <td data-key="code" class="rn_DocumentTableValue summary_line_code">-</td>
            <td data-format="number" data-key="quantity" class="rn_DocumentTableValue summary_line_quantity">-</td>
            <td data-key="um" class="rn_DocumentTableValue summary_line_um">-</td>
            <td data-key="detail" class="rn_DocumentTableValue summary_line_detail">-</td>
            <td data-key="unit_price" class="rn_DocumentTableValue summary_line_unit_price">-</td>
            <td data-format="currency" data-key="total" class="rn_DocumentTableValue summary_line_total">-</td>
          </tr>
        </table>
      </div>

      <div class="rn_FooterContent">
        <div class="rn_DocumentValue header_amount_clp"></div>
        <div class="rn_DocumentValue header_iva"></div>
        <div class="rn_DocumentValue header_exempt">0</div>
        <div class="rn_DocumentValue header_total"></div>
      </div>

    </div>

    <div class="rn_FooterDocument">
    </div>
  </div>
  <a href="javascript: void(0);" class="btn btn_detail">Ver Detalle</a>
  <? if (!\RightNow\Utils\Config::getConfig(CUSTOM_CFG_HIDE_BTN_PAY) && isEnabled(5)) : ?>
    <a href="javascript: void(0);" class="btn btn_pay">Pagar Factura</a>
  <? endif; ?>
  <!-- <a href="javascript: void(0);" class="btn btn_pay">Pagar Factura</a> -->
  <a href="javascript: void(0);" target="_blank" class="btn btn_download">Descargar Documento</a>
</div>