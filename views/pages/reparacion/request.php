<? if(!\RightNow\Utils\Url::getParameter('ref_no')) header("Location: /app/reparacion/home"); ?>
<rn:meta title="Detalle Solicitud" template="dimacofi.php" clickstream="request_detail"/>
<rn:widget path="custom/login/LoginAccountRequired" />

<div class="rn_PageHeader">
  <div class="rn_PageHeaderInner">
    <h1 class="rn_Summary" itemprop="name">Detalle Reparación <?= \RightNow\Utils\Url::getParameter('ref_no') ?></h1>
  </div>
</div>




<div class="rn_PageContent">
  <rn:widget path="custom/integer/SummaryRequest" ref_no="#rn:php:\RightNow\Utils\Url::getParameter('ref_no')#" />

  <?
  // $a_requestInfo = $this->session->getSessionData('Request_info');
  // $a_requestInfo = $this->data['Request_info'];
  $a_requestInfo = unserialize($this->session->getFlashData('Request_info'));
  ?>


  <?php if (empty($a_requestInfo) or $a_requestInfo['idStatus'] == 1) : ?>
    <h2>#rn:msg:CUSTOM_MSG_REQUESTS_PRODUCT#</h2>
    <rn:widget path="custom/integer/RequestList" ref_no="#rn:php:\RightNow\Utils\Url::getParameter('ref_no')#" />
	<!-- Agregado por NV  10/12/2019 -->
	<h2>#rn:msg:CUSTOM_MSG_PREDICTION_REQUEST#</h2>
	<rn:widget path="custom/dimacofi/repuestosRecomendados" ref_no="#rn:php:\RightNow\Utils\Url::getParameter('ref_no')#" />
	<rn:widget path="custom/dimacofi/PredictionList/PredictionList" />
	<!-- ----------------- -->
    <h2>#rn:msg:CUSTOM_MSG_SEARCH_REQUEST#</h2>
    <rn:widget path="custom/parts/SearchBox" ref_no="#rn:php:\RightNow\Utils\Url::getParameter('ref_no')#" />
    <rn:widget path="custom/parts/SearchList" />
  <? else: ?>


    <h2>#rn:msg:CUSTOM_MSG_REQUESTS_PRODUCT#</h2>
    <?
    $refNo                 = $a_requestInfo['ref_no']; // se necesita obtener este valor
    $a_FiltersIncident     = array();
    $a_FiltersIncident[]   = array("name" => 'ref_no', "operator" => '=' , "type" => 'INT' , "value" => "{$refNo}")
    ?>
    <rn:widget path="custom/reports/IntegerGrid" report_id="100713" json_filters="#rn:php:json_encode($a_FiltersIncident)#" per_page="20" show_paginator="false" />

    <h2>#rn:msg:CUSTOM_MSG_PRODUCTS_NOTFOUND#</h2>
    <?
    $refNo                 = $a_requestInfo['ref_no']; // se necesita obtener este valor
    $a_FiltersIncident     = array();
    $a_FiltersIncident[]   = array("name" => 'ref_no', "operator" => '=' , "type" => 'INT' , "value" => "{$refNo}")
    ?>
    <rn:widget path="custom/reports/IntegerGrid" report_id="100833" json_filters="#rn:php:json_encode($a_FiltersIncident)#" per_page="10" show_paginator="false" />

  <? endif;?>
  <?

  $refNoFather            = \RightNow\Utils\Url::getParameter('ref_no'); // se necesita obtener este valor
  $a_FiltersIncident2     = array();
  $a_FiltersIncident2[]   = array("name" => 'ref_no', "operator" => '=' , "type" => 'INT' , "value" => "{$refNoFather}")

  ?>
  <?php if (!empty($a_requestInfo)): ?>
  <h2>#rn:msg:CUSTOM_MSG_SPARE_PARTS_HISTORY#</h2>
  <rn:widget path="custom/reports/IntegerGrid" report_id="100712" json_filters="#rn:php:json_encode($a_FiltersIncident2)#" per_page="8" show_paginator="false" />
  <?php endif;?>
</div>
