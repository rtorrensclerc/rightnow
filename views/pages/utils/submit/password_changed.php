<?
$CI = get_instance();
$CI->session->setSessionData(array('temporal_key' => false));
header('Location: ' . \RightNow\Utils\Url::getOriginalUrl(false) . '/app/home');
?>
<rn:meta title="#rn:msg:PASSWORD_CHANGE_SUCCEEDED_LBL#" template="2020.php" login_required="true"/>

<div class="rn_PageHeader">
    <div class="rn_Container">
        <h1>#rn:msg:THANK_YOU_LBL#</h1>
    </div>
</div>

<div id="rn_PageContent rn_Home">
    <div class="rn_Padding">
        #rn:msg:YOUR_PASSWORD_HAS_BEEN_CHANGED_MSG#
    </div>
</div>
