<rn:meta title="#rn:msg:FIND_ANS_HDG#" template="dimacofi.php" clickstream="hh_list"/>
<rn:widget path="custom/login/LoginAccountRequired" />


<?php
$CI             =& get_instance();
$accountValues  = $CI->session->getSessionData('Account_loggedValues');
// Parche Uso de cookies
$accountValues  = unserialize($_COOKIE['Account_loggedValues']);

$a_Filters1     = array();
$a_Filters1[]   = array("name" => 'cuentaID', "operator" => '=' , "type" => 'INT' , "value" => "{$accountValues['ID']}");
?>


<?switch($a_accountValues['ProfileID'])
 {
    case 76:?>
        <div class="rn_PageHeader">
            <div class="rn_Container">
                <h1>Portal Logística Insumos</h1>
            </div>
        </div>
        <div class="rn_PageHeader">
            <div class="rn_PageHeaderInner">
              <h1>Nro de Guia</h1>
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
                    <rn:widget path="custom/reports/IntegerGrid" report_id="102170" per_page="20" default_ajax_endpoint="/cc/ajaxReports/resultPage"
                    url_per_col="/app/reparacion/logistica_detail/ref_no/" col_id_url="1"  display_type="list" json_filters="#rn:php:json_encode($a_FiltersHH)#" />
                </div>
            </div>
        </div>
      <?
     
      break;
      
     case 41:?>
   <div class="rn_PageHeader">
       <div class="rn_Container">
           <h1>#rn:msg:CUSTOM_MSG_LOGISTICS_PORTAL# 1111</h1>
       </div>
   </div>
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
               <rn:widget path="custom/reports/IntegerGrid" report_id="101070" per_page="20" default_ajax_endpoint="/cc/ajaxReports/resultPage"
               url_per_col="/app/reparacion/logistica_detail/ref_no/" col_id_url="1"  display_type="list" json_filters="#rn:php:json_encode($a_FiltersHH)#" />
           </div>
       </div>
   </div>
 <?

 break;
 default:
?>
<div class="rn_PageHeader">
    <div class="rn_Container">
        <h1>#rn:msg:CUSTOM_MSG_TECHNICAL_PORTAL#</h1>
    </div>
</div>

<div id="rn_PageContent rn_Home">
    <rn:widget path="custom/integer/SummaryBudget" />
    <div class="rn_Padding column">
      <h2>#rn:msg:CUSTOM_MSG_REQUEST_SPARE_PARTS#</h2>
      <rn:widget path="custom/reports/IntegerGrid" report_id="100714" json_filters="#rn:php:json_encode($a_Filters1)#" per_page="8" show_paginator="false" url_per_col="/app/reparacion/request/ref_no/" col_id_url="1" />
      <a class="btn" href="/app/reparacion/parts_request">#rn:msg:CUSTOM_MSG_SEEALL#</a>
      <h2>#rn:msg:CUSTOM_MSG_REQUEST_ACTIVE_PARTS#</h2>
      <rn:widget path="custom/reports/IntegerGrid" report_id="100716" per_page="5" show_paginator="false" />
      <a class="btn" href="/app/reparacion/active_parts">#rn:msg:CUSTOM_MSG_SEEALL#</a>
      <h2>#rn:msg:CUSTOM_MSG_PEND_REPORTS_REQUEST#</h2>
        <rn:widget path="custom/reports/IntegerGrid" report_id="101478" json_filters="#rn:php:json_encode($a_Filters1)#" per_page="8" show_paginator="false" url_per_col="/app/reparacion/informe_detail/ref_no/" col_id_url="6" />
        <a class="btn" href="/app/reparacion/pending_reports">#rn:msg:CUSTOM_MSG_SEEALL#</a>
    </div>
</div>
<?
   break;}
?>