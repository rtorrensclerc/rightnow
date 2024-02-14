<rn:meta title="#rn:msg:SHP_TITLE_HDG#" template="dimacofi.php" clickstream="home"/>
<rn:widget path="custom/login/LoginAccountRequired" />
<div class="rn_PageHeader">
  <div class="rn_PageHeaderInner">
    <h1 class="rn_Summary" itemprop="name">Soporte Programado  <?= \RightNow\Utils\Url::getParameter('ref_no') ?></h1>
  </div>
</div>
<div id="rn_PageContent rn_Home">
    <div class="rn_Padding column">
      <rn:widget path="custom/integer/special_detail" ref_no="#rn:php:\RightNow\Utils\Url::getParameter('ref_no')#"/>
    </div>
</div>