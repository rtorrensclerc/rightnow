<rn:meta title="#rn:msg:RESET_PASSWORD_CMD#" template="2020.php" login_required="false" force_https="true" />
<?
/**
 * This page is navigated to by following an email link when:
 * user or agent triggers 'reset password' routine
 */
?>
<div class="rn_PageHeader rn_Account">
    <div class="rn_Container">
        <h1>#rn:msg:RESET_YOUR_PASSWORD_CMD#</h1>
    </div>
</div>

<div id="rn_PageContent">
    <div class="rn_Padding">
        <rn:widget path="login/ResetPassword" />
    </div>
</div>
