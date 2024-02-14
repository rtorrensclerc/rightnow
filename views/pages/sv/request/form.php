<rn:widget path="custom/redirect/RedirectBlocked" />
<rn:meta title="Formulario de Requerimientos" template="2020.php" clickstream="form_request" login_required="false" />

<div class="rn_PageContent rn_Wraper">
  <div class="rn_PageContentInner">
    <div id="rn_ErrorLocation"></div>
    <form id="rn_QuestionSubmit" method="post" action="/ci/ajaxRequest/sendForm">
      <div id="rn_ErrorLocation"></div>

      <fieldset>
        <legend>Datos de Contacto</legend>
        <div class="form-content">
          <rn:condition logged_in="false">
            <div class="form-element">
              <rn:widget path="input/FormInput" name="Contact.Name.First" label_input="Nombre"
                required="true" />
            </div>

            <div class="form-element">
              <rn:widget path="input/FormInput" name="Contact.Name.Last" label_input="Apellido" required="true" />
            </div>

            <div class="form-element">
              <!-- <rn:widget path="custom/input/TextRUT" label_input="RUT" required="true" /> -->
            </div>

          </rn:condition>
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
        <legend>Datos Requerimiento</legend>
        <div class="form-content">
          <div class="form-element">
            <rn:widget path="input/ProductCategoryInput" name="Incident.Product" required_lvl="1" default_value=50 />
          </div>
          <div class="form-element">
            <rn:widget path="input/ProductCategoryInput" name="Incident.Category" required_lvl="1" label_input="Tipo de Solicitud"/>
          </div>
          <div class="form-element form-element-wide">
            <rn:widget path="input/FormInput" name="Incident.Threads" required="true" label_input="Comentarios" />
          </div>

          <div class="form-element-wide">
            <rn:widget path="input/FileAttachmentUpload" />
          </div>
        </div>
      </fieldset>

      <rn:widget path="input/FormSubmit" label_button="Enviar" on_success_url="/app/sv/request/form_confirm" error_location="rn_ErrorLocation" />
    </form>
  </div>
</div>
