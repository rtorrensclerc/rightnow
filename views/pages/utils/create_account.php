<rn:meta title="#rn:msg:CREATE_NEW_ACCT_HDG#" template="2020.php" login_required="false" redirect_if_logged_in="/app/home" force_https="true" />
<div class="standard-bg">
    <div class="content-title">
        <h1>#rn:msg:CREATE_AN_ACCOUNT_CMD#</h1>
        <p>#rn:msg:CUSTOM_MSG_SUPPLIES_REQUEST_REGISTER#</p>
    </div>
</div>

<div class="content-body">
    <div class="rn_PageContent rn_CreateAccount wrapper clearfix">
        <form id="rn_CreateAccount" onsubmit="return false;">
            <div id="rn_ErrorLocation" class="rn_MessageBox rn_ErrorMessage rn_Hidden"></div>

            <fieldset>
                <div class="form-content">
                    <legend>#rn:msg:CUSTOM_MSG_COMPANY_INFO#</legend>
                </div>
                <!--<div class="form-element-wide">
                <rn:widget path="custom/input/selectOrganization" name="Contact.CustomFields.c.temp_org_id" label_input="Organización" />
                </div>-->
                <div class="form-element">
                  <!-- <rn:widget path="input/FormInput" name="Contact.CustomFields.c.rut_org" label_input="Rut Empresa" required="true"/> -->
                  <rn:widget path="custom/input/TextInputExtended" name="Contact.CustomFields.c.rut_org" required="true" label_input="RUT Empresa" placeholder="Ej: 12345678-9" display_type="rut" validate_on_blur="true" />
                </div>
                <div class="form-element">
                  <rn:widget path="input/FormInput" name="Contact.CustomFields.c.name_org" label_input="Nombre Empresa" required="true"/>
                </div>
            </fieldset>

            <fieldset>
                <div class="form-content">
                    <legend>#rn:msg:CUSTOM_MSG_PERSONAL_INFO#</legend>
                </div>
                <div class="form-element">
                  <rn:widget path="input/FormInput" name="Contact.Name.First" label_input="#rn:msg:FIRST_NAME_LBL#" required="true"/>
                </div>
                <div class="form-element">
                  <rn:widget path="input/FormInput" name="Contact.CustomFields.c.company_address" label_input="Dirección" required="true"/>
                </div>
                <div class="form-element">
                  <rn:widget path="input/FormInput" name="Contact.Name.Last" label_input="#rn:msg:LAST_NAME_LBL#" required="true"/>
                </div>
                <div class="form-element">
                    <rn:widget path="custom/input/TextInputExtended" name="Contact.Emails.PRIMARY.Address" required="true" label_input="#rn:msg:EMAIL_ADDR_LBL#" placeholder="usuario@empresa.cl" display_type="email" validate_on_blur="true" />
                </div>
                <div class="form-element">
                    <rn:widget path="custom/input/TextInputExtended" name="Contact.CustomFields.c.rut" required="false" label_input="RUT" placeholder="Ej: 12345678-9" display_type="rut" validate_on_blur="true" />
                </div>
                <div class="form-element">
                    <rn:widget path="input/FormInput" name="Contact.Phones.OFFICE.Number" required="true" label_input="#rn:msg:OFFICE_PHONE_LBL#"/>
                </div>
                <div class="form-element">
                    <rn:widget path="input/FormInput" name="Contact.Phones.MOBILE.Number" label_input="#rn:msg:MOBILE_PHONE_LBL#"/>
                </div>
            </fieldset>
            <fieldset>
                <div class="form-content">
                    <legend>#rn:msg:CUSTOM_MSG_REFERENCE_INFO#</legend>
                </div>
                <div class="form-element rn_OriginReference">
                  <rn:widget path="input/FormInput" name="Contact.CustomFields.c.origin_reference" label_input="¿Cómo nos conociste?" required="false"/>
                </div>
                <div class="form-element rn_OriginReferred">
                  <rn:widget path="input/FormInput" name="Contact.CustomFields.c.origin_referred" label_input="Referencia"  placeholder="Ej: Juan Perez"  required="false"/>
                </div>
                <div class="form-element rn_SelectReferred">
                  <rn:widget path="custom/input/SelectField" id="select_reference" name="select_reference" label_input="Seleccione" required="false" disabled="false" />
                </div>
               
            </fieldset>
            <fieldset>
                <div class="form-content">
                    <legend>#rn:msg:CUSTOM_MSG_SECURITY_INFO#</legend>
                </div>
                <div class="form-element-wide">
                    <rn:widget path="input/FormInput" name="Contact.NewPassword" require_validation="true" label_input="#rn:msg:PASSWORD_LBL#" label_validation="#rn:msg:VERIFY_PASSWD_LBL#"/>
                </div>
            </fieldset>

            <div class="form-buttons">
                <rn:widget path="input/FormSubmit" label_button="#rn:msg:CREATE_ACCT_CMD#" on_success_url="/app/home" error_location="rn_ErrorLocation"/>
             </div>

        </form>
    </div>
</div>
