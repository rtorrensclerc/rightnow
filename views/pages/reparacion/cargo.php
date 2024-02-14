<rn:meta title="#rn:msg:SHP_TITLE_HDG#" template="dimacofi.php" clickstream="home"/>
<rn:widget path="custom/login/LoginAccountRequired" />

<?php
$CI             =& get_instance();
$accountValues  = $CI->session->getSessionData('Account_loggedValues');
// Parche Uso de cookies
$accountValues  = unserialize($_COOKIE['Account_loggedValues']);

$a_Filters1     = array();
$a_Filters1[]   = array("name" => 'cuentaID', "operator" => '=' , "type" => 'INT' , "value" => "{$accountValues['ID']}");
?>

<div class="rn_PageHeader">
    <div class="rn_Container">
        <h1>#rn:msg:CUSTOM_MSG_TECHNICAL_PORTAL#</h1>
    </div>
</div>

<div id="rn_PageContent rn_Home">
    <div class="rn_Padding column">
      <h2>#rn:msg:CUSTOM_MSG_REQUEST_CARGO#</h2>
      <rn:widget path="custom/reports/IntegerGrid" report_id="100834" json_filters="#rn:php:json_encode($a_Filters1)#" per_page="8" show_paginator="false" url_per_col="/app/reparacion/request/ref_no/" col_id_url="1" />
      <a class="btn" href="/app/reparacion/c_part_request">#rn:msg:CUSTOM_MSG_SEEALL#</a>
      <h2>#rn:msg:CUSTOM_MSG_REQUEST_ACTIVE_C#</h2>
      <rn:widget path="custom/reports/IntegerGrid" report_id="100836" json_filters="#rn:php:json_encode($a_Filters1)#" per_page="5" show_paginator="false" />
      <a class="btn" href="/app/reparacion/c_active_parts">#rn:msg:CUSTOM_MSG_SEEALL#</a>
    </div>
</div>
