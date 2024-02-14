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
        <h1>#rn:msg:CUSTOM_MSG_MY_ATTENTIONS_REQUEST#</h1>
    </div>
</div>

<div id="rn_PageContent rn_Home">
    <div class="rn_Padding column">
      <rn:widget path="custom/reports/IntegerGrid" report_id="100714" json_filters="#rn:php:json_encode($a_Filters1)#" per_page="40" url_per_col="/app/reparacion/request/ref_no/" col_id_url="1" />
    </div>
</div>
