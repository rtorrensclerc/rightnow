<!-- use RightNow\Connect\v1_2 as RNCPHP; -->
<rn:widget path="custom/redirect/RedirectBlocked" module_id="4" error_page="/app/sv/error.php" />
<rn:meta title="Solicitud de Insumos Múltiples" template="2020.php" login_required="true" clickstream="supplier_web_multiple"/>

<? if(!\RightNow\Utils\Config::getConfig(CUSTOM_CFG_DISABLED_SUPPLIES_MULTIPLE)): ?>
  <?
  $id = \RightNow\Utils\URL::getParameter('i_id');
  $read_only = ($id)?true:false;
  ?>

  <div class="content-body wrapper">
    <? if($read_only): ?>
      <rn:widget path="custom/supplier/SupplierRequestMultiple" read_only="true" />
    <? else: ?>
      <rn:widget path="custom/supplier/SupplierRequestMultiple" />
    <? endif; ?>
  </div>
<? else: ?>
  <? echo \RightNow\Utils\Config::getMessage(CUSTOM_MSG_TEMPORARILY_DISABLED); ?>
<? endif; ?>