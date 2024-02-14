<rn:meta title="#rn:msg:SUPPORT_LOGIN_HDG#" template="2020.php" login_required="false" redirect_if_logged_in="account/overview" force_https="true" />
<?
$is_sv = (getUrlParm('redirect') && explode('%2F', getUrlParm('redirect'))[0] === 'sv')?true:false;

if($is_sv)
{
    header('Location: /app/sv/utils/login_form/redirect/' . getUrlParm('redirect'));
}
?>
<div class="rn_PageHeader">
    <div class="rn_Container">
        <h1>#rn:msg:LOG_IN_UC_LBL#</h1>
    </div>
</div>

<div id="rn_PageContent rn_LoginForm rn_Container">

    <div class="rn_StandardLogin">
      <h2>#rn:msg:LOG_IN_WITH_AN_EXISTING_ACCOUNT_LBL#</h2><br/>
      <rn:widget path="login/LoginForm" redirect_url="/app/sv/supplier/form" initial_focus="true"/>
      <p><a href="/app/#rn:config:CP_ACCOUNT_ASSIST_URL##rn:session#">#rn:msg:FORGOT_YOUR_USERNAME_OR_PASSWORD_MSG#</a></p>
      <p><a href="/app/utils/create_account">#rn:msg:CUSTOM_MSG_NEW_ACOUNT_REQUEST#</a></p>
    </div>
</div>
