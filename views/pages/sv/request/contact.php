





<?
$p_p = \RightNow\Utils\Url::getParameter('p');
$p_c = \RightNow\Utils\Url::getParameter('c');
$kw= \RightNow\Utils\Url::getParameter('kw');

$sufix = '';

if ($p_p=='67'):
  $sufix = ' Felicitaciones';
endif;
if ($p_p=='66'):
  $sufix = ' Reclamo';
endif;
if ($p_p=='50'):
  $sufix = ' Sugerencia';
endif;


?>
<rn:meta title="#rn:msg:CUSTOM_MSG_FORM_OTHER_TITLE#" template="2020.php" clickstream="form_request_contact" login_required="false" />
<rn:container source_id="KFSearch" per_page="3">
<rn:widget path="searchsource/SourceResultListing"
 label_heading="#rn:msg:PUBLISHED_ANSWERS_LBL#" hide_when_no_results="true" truncate_size="100" />

 
<div class="rn_PageContent rn_Wraper" >
  <h2><b><?=$sufix?></b></h2>
  <div class="message">
  #rn:msg:CUSTOM_MSG_FORM_OTHER_TXT#
  </div>

  <div class="rn_PageContentInner" >

    <rn:condition logged_in="false">
      <div class="ask-in-login" style="display: flex; justify-content: center;">
        <p>Si ya está registrado, inicie sesión</p>
        <rn:widget path="custom/login/LoginFormMouldable" redirect_url="/app/sv/request/contact" />
      </div>
    </rn:condition>
  </div>
<br>
    
  <div class="rn_PageContentInner">
    <div id="rn_ErrorLocation" style="display:none;"></div>
    <form id="rn_QuestionSubmit" method="post" action="/ci/ajaxRequest/sendForm">

      <rn:condition logged_in="false">
        <fieldset>
          <legend>Datos de Contacto</legend>

          <div class="form-content">

            <div class="form-element">
              <rn:widget path="input/FormInput" name="Contact.Name.First" label_input="Nombre"
                required="true" />
            </div>

            <div class="form-element">
              <rn:widget path="input/FormInput" name="Contact.Name.Last" label_input="Apellido"
                required="true" />
            </div>

            <div class="form-element">
              <rn:widget path="input/FormInput" name="Contact.CustomFields.c.name_org"
                label_input="Nombre Empresa" required="false" />
            </div>

            <div class="form-element">
              <rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" initial_focus="true"
                label_input="Correo Electrónico" required="true" />
            </div>

            <div class="form-element">
              <rn:widget path="input/FormInput" name="Contact.Phones.HOME.Number" label_input="Teléfono"
                required="true" />
            </div>
          </div>
        </fieldset>
      </rn:condition>

      <fieldset>
        <legend>Datos Requerimiento</legend>

        <div class="form-content">
          <?if (!empty($p_p)) { ?>
          <div class="form-element" style="display:none">
          <?}else{?>
          <div class="form-element">
          <?};?>
            <rn:widget path="custom/input/ProductCategoryInputExtended" exclude="68" name="Incident.Product" 
              required_lvl="1" default_value="50" label_input="Tipo"  />
              <!--hide_on_load="#rn:php:json_encode(($p_p)?TRUE:FALSE)#" /-->
          </div>
          <br>
          <?if (!empty($p_c)) { ?>
          <div class="form-element form-element-wide" style="display:none">
          <?}else{?>
          <div class="form-element form-element-wide">
          <?};?>
            <rn:widget path="custom/input/ProductCategoryInputExtended" exclude="34" default_value=""
              name="Incident.Category" required_lvl="2" />
              <!-- hide_on_load="#rn:php:json_encode(($p_c)?TRUE:FALSE)#" /-->
          </div>
           

          
          <div class="form-element form-element-wide">
            <rn:widget path="input/FormInput" name="Incident.Threads" required="true"
              label_input="Comentarios" />
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
          <rn:widget path="input/FormSubmit" label_button="Enviar Solicitud"
            on_success_url="/app/sv/request/confirm" error_location="rn_ErrorLocation" />
        </div>
      </div>

    </form>
  </div>
</div>