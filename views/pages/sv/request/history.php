<rn:widget path="custom/redirect/RedirectBlocked" />
<rn:meta title="Mis Solicitudes" template="2020.php" clickstream="form_request" login_required="true"/>


<div class="message">
#rn:msg:CUSTOM_MSG_COVID_WARNING#
</div>

<!-- ?php
  $CI             = get_instance();
  $accountValues  = $CI->session->getSessionData('Account_loggedValues');
  echo json_encode($accountValues);
  // Parche Uso de cookies
  $accountValues  = unserialize($_COOKIE['Account_loggedValues']);
  $a_Filters1     = array();
  $a_Filters1[]   = array("name" => 'cuentaID', "operator" => 'list' , "type" => 'INT' , "value" => "'82878900-7','69041400-7','76365546-6','69070400-5'");
?>
<div id="rn_PageContent rn_Home">
    <div class="rn_Padding column">
      <rn:widget path="custom/reports/IntegerGrid" report_id="102271" json_filters="#rn:php:json_encode($a_Filters1)#" per_page="20" show_paginator="true" url_per_col="/app/account/questions/detail/i_id/" col_id_url="1" />
    </div>
</div-->




<div class="content-body">
    <rn:container report_id="101572">
    <div class="wrapper">
        <h2 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_RESULTS_CMD#</h2>
        <rn:widget path="reports/ResultInfo"/>
        <rn:widget path="reports/Grid" label_caption="<span class='rn_ScreenReaderOnly'>#rn:msg:SEARCH_YOUR_SUPPORT_HISTORY_CMD#</span>"/>
        <rn:widget path="reports/Paginator"/>
    </div>

    </rn:container>
</div>
