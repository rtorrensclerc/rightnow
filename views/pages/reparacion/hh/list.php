<rn:meta title="#rn:msg:FIND_ANS_HDG#" template="dimacofi.php" clickstream="hh_list"/>
<rn:widget path="custom/login/LoginAccountRequired" />

<div class="rn_PageHeader">
    <div class="rn_PageHeaderInner">
      <h1>#rn:msg:CUSTOM_MSG_HH_SEARCH#</h1>
        <div class="rn_SearchControls">
            <form onsubmit="return false;">
                <div class="rn_SearchInput">
                    <rn:widget path="search/KeywordText" label_text="" initial_focus="true"/>
                </div>
                <rn:widget path="search/SearchButton" icon_path="images/layout/search_icon.png" force_page_flip="true"/>
            </form>
        </div>
    </div>
</div>

<div class="rn_Container">
    <div class="rn_PageContent rn_AnswerList">
        <div>
            <?
              $search = \RightNow\Utils\Url::getParameter('search'); // 1 cuando realiza la busqueda
              $kw = \RightNow\Utils\Url::getParameter('kw'); // Valor de la consulta

              $a_FiltersHH = array();
              $a_FiltersHH[] = array('name' => 'search', 'operator' => 'like', 'type' => 'STRING', 'value' => "%{$kw}%");
            ?>
            <rn:widget path="custom/reports/IntegerGrid" report_id="100709" per_page="20" default_ajax_endpoint="/cc/ajaxReports/resultPage"
            url_per_col="/app/reparacion/hh/detail/hh_id/" col_id_url="1"  display_type="list" json_filters="#rn:php:json_encode($a_FiltersHH)#" />
        </div>
    </div>

</div>
