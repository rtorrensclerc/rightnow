<? if(!\RightNow\Utils\Url::getParameter('ref_no')) header("Location: /app/reparacion/home"); ?>
<rn:meta title="Detalle Solicitud" template="dimacofi.php" clickstream="request_detailAr"/>
<rn:widget path="custom/login/LoginAccountRequired" />

<div class="rn_PageHeader">
  <div class="rn_PageHeaderInner">
    <h1 class="rn_Summary" itemprop="name">Detalle Solicitud  <?= \RightNow\Utils\Url::getParameter('ref_no') ?></h1>
  </div>
</div>
<div class="rn_PageContent">
  <rn:widget path="custom/integer/SummaryTicketAr" ref_no="#rn:php:\RightNow\Utils\Url::getParameter('ref_no')#" />
</div>
