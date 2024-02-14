<rn:widget path="custom/redirect/RedirectBlocked" />
<rn:meta title="#rn:msg:CUSTOM_MSG_SUPPORT_CONTACT#" template="2020.php" clickstream="form_request_commercial" login_required="false" />


<div class="rn_PageContent rn_Wraper">
  <div class="rn_PageContentInner">
    <rn:condition logged_in="false">
      <div class="ask-in-login">
        <p>Si ya está registrado, inicie sesión</p>
        <rn:widget path="custom/login/LoginFormMouldable" redirect_url="/app/sv/request/ContactoSoporte" />
      </div>
    </rn:condition>
    <div id="rn_ErrorLocation" style="display:none;"></div>
    <form id="rn_QuestionSubmit" method="post" action="/ci/ajaxRequest/sendForm">

    #rn:msg:CUSTOM_MSG_SUPPORT_CONTACT2#
     

      <rn:condition logged_in="false">
        <fieldset>

          <legend>Datos de Contacto</legend>
          <div class="form-content">

            <div class="form-element">
              <rn:widget path="input/FormInput" name="Contact.Name.First" label_input="Nombre" required="true" />
            </div>

            <div class="form-element">
              <rn:widget path="input/FormInput" name="Contact.Name.Last" label_input="Apellido" required="true" />
            </div>
            <div class="form-element">
              <rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" initial_focus="true" label_input="Correo Electrónico"
                required="true" />
            </div>
            <div class="form-element">
              <rn:widget path="input/FormInput" name="Contact.Phones.HOME.Number" label_input="Teléfono" required="true" />
            </div>


          </div>

        </fieldset>
        <fieldset>
          <legend>Datos de Empresa </legend>
          <div class="form-content">
            <div class="form-element">
              <rn:widget path="input/FormInput" name="Contact.CustomFields.c.name_org" label_input="Nombre Empresa" required="true" />
            </div>
            <div class="form-element">
              <rn:widget path="custom/input/TextInputExtended" name="Contact.CustomFields.c.rut_org" required="true"
                label_input="RUT Empresa" placeholder="Ej: 12345678-9" display_type="rut" validate_on_blur="true" />
            </div>
            <div class="form-element form-element-wide">
              <rn:widget path="input/FormInput" name="Incident.CustomFields.c.direccion_correcta" label_input="Dirección" required="true" />
            </div>
            
            <div class="form-element form-element-wide">
              <rn:widget path="input/FormInput" name="Incident.CustomFields.c.region_crm" label_input="Región" required="true" />
            </div>
            

          </div>
        </fieldset>
      </rn:condition>

      <fieldset>
        <legend>Datos Requerimiento</legend>
        <div class="form-content">
          <div class="form-element" hidden>
            <rn:widget path="input/ProductCategoryInput" name="Incident.Product" required_lvl="1" default_value="68" label_input="Tipo"  />
          </div>

          <div class="form-element" hidden>
            <rn:widget path="input/ProductCategoryInput" name="Incident.Category" required_lvl="1" default_value="32" />
          </div>

          <!-- <div class="form-element" hidden>
            <rn:widget path="input/ProductCategoryInput" name="Incident.Category" required_lvl="1" default_value="73" />
          </div> -->

          <!-- <div class="form-element">
            <rn:widget path="input/FormInput" name="Incident.CustomFields.c.subject" label_input="Asunto" required="true" />
          </div> -->

          <div class="rn_Hidden" hidden="hidden" styles="display:none;">
            <rn:widget path="input/FormInput" name="Incident.Subject" required="true" label_input="Asunto" default_value="Contacto Monitoreo Cloud" />
            <br>
          </div>

          <div class="form-element form-element-wide">
            <rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="Comentarios" />
          </div>


        </div>
      </fieldset>
      <fieldset>
        <legend>Archivos Adjuntos</legend>
        <div class="form-content">
          <div class="form-element-wide">
            <rn:widget path="input/FileAttachmentUpload" />
          </div>
        </div>
      </fieldset>

      <div class="form-buttons">
        <div class="rn_FormSubmit">
          <rn:widget path="input/FormSubmit" label_button="Enviar Solicitud" on_success_url="/app/sv/request/confirm" error_location="rn_ErrorLocation" />
        </div>
      </div>

    </form>
  </div>
</div>
