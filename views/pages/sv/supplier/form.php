<rn:widget path="custom/redirect/RedirectBlocked" module_id="3" error_page="/app/sv/error.php" />
<rn:meta title="Solicitud de Insumos" template="2020.php" login_required="true" clickstream="supplier_web"/>

<?
$id = \RightNow\Utils\URL::getParameter('i_id');
$read_only = ($id)?true:false;

?>




<div class="content-body wrapper">
<? if($read_only): ?>
	<rn:widget path="custom/supplier/SupplierRequest" read_only="true" />
<? else: ?>
	<rn:widget path="custom/supplier/SupplierRequest" />
<? endif; ?>
</div>
