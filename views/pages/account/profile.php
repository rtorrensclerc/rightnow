<rn:meta title="#rn:msg:ACCOUNT_SETTINGS_LBL#" template="2020.php" login_required="true" force_https="true" />

<div class="rn_PageHeader rn_Account">
  <div class="rn_Container">
    <h1>#rn:msg:ACCOUNT_SETTINGS_LBL#</h1>
  </div>
</div>

<div id="rn_PageContent" class="rn_Profile">
  <div class="rn_Padding">
    <rn:container>
      <div class="rn_Required rn_LargeText">#rn:url_param_value:msg#</div>
      <form id="rn_CreateAccount" onsubmit="return false;">
        <div id="rn_ErrorLocation" style="display:none;"></div>
        <fieldset>
          <legend>Datos Generales</legend>
          <div class="form-element"><rn:widget path="input/FormInput" name="Contact.Name.First" label_input="#rn:msg:FIRST_NAME_LBL#" required="true" read_only="false" /></div>
          <div class="form-element"><rn:widget path="input/FormInput" name="Contact.Name.Last" label_input="#rn:msg:LAST_NAME_LBL#" required="true" read_only="false" /></div>
          <div class="form-element"><rn:widget path="input/FormInput" name="Contact.Login" required="true" validate_on_blur="true" initial_focus="true" label_input="#rn:msg:USERNAME_LBL#" read_only="true" /></div>
        </fieldset>
        <fieldset>
          <legend>#rn:msg:CONTACT_INFO_LBL#</legend>
          <div class="form-element"><rn:widget path="input/FormInput" name="Contact.Phones.HOME.Number" label_input="#rn:msg:HOME_PHONE_LBL#" read_only="false" /></div>
          <div class="form-element"><rn:widget path="input/FormInput" name="Contact.Phones.OFFICE.Number" label_input="#rn:msg:OFFICE_PHONE_LBL#" read_only="false" /></div>
          <div class="form-element"><rn:widget path="input/FormInput" name="Contact.Phones.MOBILE.Number" label_input="#rn:msg:MOBILE_PHONE_LBL#" read_only="false" /></div>
          <div class="form-element"><rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" required="true" validate_on_blur="true" label_input="#rn:msg:EMAIL_ADDR_LBL#" read_only="true" /></div>
        </fieldset>
        <rn:condition external_login_used="false" is_social_user="false" is_active_social_user="true">
          <rn:widget path="input/FormSubmit" label_button="#rn:msg:SAVE_CHANGE_CMD#" on_success_url="/app/home" label_on_success_banner="#rn:msg:PROFILE_UPDATED_SUCCESSFULLY_LBL#" error_location="rn_ErrorLocation"/>
        </rn:condition>
        
      </form>
    </rn:container>
  </div>
</div>
