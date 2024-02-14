<rn:meta title="Detalle HH" template="dimacofi.php" clickstream="hh_detail"/>
<rn:widget path="custom/login/LoginAccountRequired" />

<? if(!\RightNow\Utils\Url::getParameter('hh_id')) \RightNow\Utils\Url::redirectToErrorPage(4);

$id_hh          = \RightNow\Utils\Url::getParameter('hh_id');
$a_Filters1     = array();
$a_Filters1[]   = array("name" => 'id_hh', "operator" => '=' , "type" => 'INT' , "value" => "{$id_hh}");

?>
<article itemscope itemtype="http://schema.org/Article" class="rn_Container">
    <div class="rn_ContentDetail">
      <div class="rn_PageHeader">
        <div class="rn_PageHeaderInner">
          <h1 class="rn_Summary" itemprop="name">Detalle HH <?= \RightNow\Utils\Url::getParameter('hh_id') ?></h1>
        </div>
      </div>
        <div class="rn_PageContent">
          <rn:widget path="custom/integer/SummaryHH" hh_id="#rn:php:\RightNow\Utils\Url::getParameter('hh_id')#" />

          <h2>#rn:msg:CUSTOM_MSG_REPAIR_HISTORY#</h2>
          <rn:widget path="custom/reports/IntegerGrid" report_id="100710" json_filters="#rn:php:json_encode($a_Filters1)#"  per_page="10" show_paginator="false" />
          <input type="button" class="btn" value="Ver Todo">
          <h2>#rn:msg:CUSTOM_MSG_SPARE_PARTS_HISTORY#</h2>
          <rn:widget path="custom/reports/IntegerGrid" report_id="100711" json_filters="#rn:php:json_encode($a_Filters1)#"  per_page="100" show_paginator="true" />
          <input type="button" class="btn" value="Ver Todo">
        </div>
    </div>
</article>
