<rn:widget path="custom/redirect/RedirectBlocked" module_id="2,12,3,4,9" error_page="/app/sv/error.php" />
<rn:meta title="Solicitud de Contacto" template="2020.php" login_required="true" clickstream="supplier_web"/>


<?
$p_p = \RightNow\Utils\Url::getParameter('p');
$p_c = \RightNow\Utils\Url::getParameter('c');
$kw= \RightNow\Utils\Url::getParameter('kw');
$read_only = ($id) ? true : false;
?>
<div class="content-body wrapper">
<? if($read_only): ?>
	<rn:widget path="custom/assistance/ContactAssistance" read_only="true"  p_p="<?=$p_p?>" p_c="<?=$p_c?>" kw="<?=$kw?>"/>
<? else: ?>
	<rn:widget path="custom/assistance/ContactAssistance" p_p="<?=$p_p?>" p_c="<?=$p_c?>" kw="<?=$kw?>"/>
<? endif; ?>
</div>
