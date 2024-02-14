<rn:meta title="#rn:msg:CHANGE_YOUR_PASSWORD_CMD#" template="2020.php" login_required="true" force_https="true"/>

<div id="rn_PageContent" class="rn_Account">
    <div class="rn_Padding">
        <div class="rn_Required rn_LargeText">#rn:url_param_value:msg#</div>
        <div id="rn_ErrorLocation"></div>
        <form id="rn_ChangePassword" onsubmit="return false;">
            
            <div class="form-element-wide">
                <rn:widget path="input/PasswordInput" name="Contact.NewPassword" require_validation="true" require_current_password="true" label_input="#rn:msg:PASSWORD_LBL#" label_validation="#rn:msg:VERIFY_PASSWD_LBL#" initial_focus="true"/>
            </div>

            <div class="form-buttons">
                <rn:widget path="input/FormSubmit" on_success_url="/app/utils/submit/password_changed" error_location="rn_ErrorLocation"/>
            </div>
        </form>
    </div>
</div>
