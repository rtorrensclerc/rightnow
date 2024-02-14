<rn:widget path="custom/redirect/RedirectBlocked" module_id="1,9,10,11,12,13,16" error_page="/app/sv/error.php" />
<rn:meta title="Solicitud de Soporte" template="2020.php" clickstream="form_request_contact"
    login_required="true" />

<?
$p_p = \RightNow\Utils\Url::getParameter('p');
$p_c = \RightNow\Utils\Url::getParameter('c');
$p_d = \RightNow\Utils\Url::getParameter('d');
?>

<div class="rn_PageContent rn_Wraper">
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
        <?if ($p_c=='89') : ?>
          <h2>Solicitud de Soporte RPA</h2>
        <?endif;?>
        <?if ($p_c=='91') : ?>
          <h2>Solicitud de Soporte Aula Digital</h2>
        <?endif;?>
        <?if ($p_c=='92') : ?>
          <h2>Solicitud de Soporte Firma Digital</h2>
        <?endif;?>
        <?if ($p_c=='90') : ?>
          <h2>Solicitud de Soporte Gestión Documental</h2>
        <?endif;?>
        <?if ($p_c=='93') : ?>
          <h2>Solicitud de Soporte Digitalización</h2>
        <?endif;?>
        <?if ($p_c=='96') : ?>
          <h2>Solicitud de Soporte DAAS</h2>
        <?endif;?>
               
               
                <div class="form-content">
                <div class="form-element" >
                            <rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" initial_focus="true"
                                label_input="Correo Electrónico" required="true" />
                        </div>
                        
                        <div class="form-element">
                            <rn:widget path="input/FormInput" name="Contact.Phones.HOME.Number" label_input="Teléfono"
                                required="true" />
                        </div>
                        <div class="form-element">
                            <rn:widget path="input/FormInput" name="Incident.subject" label_input="Asunto"
                                required="true" />
                        </div>
                        <?if ($p_c=='96') : ?>
                        <div class="form-element">
                            <rn:widget path="input/FormInput" name="Incident.CustomFields.c.id_hh" label_input="HH"
                                required="true" />
                        </div>
                        <div class="form-element">
                            <rn:widget path="input/FormInput" name="Incident.CustomFields.c.serie_maq" label_input="Serie"
                                required="true" />
                        </div>
                        <div class="form-element">
                            <rn:widget path="input/FormInput" name="Incident.CustomFields.c.external_reference" label_input="Referencia Externa"
                                required="false" />
                        </div>
                        
                        <?endif;?>
                        <div class="form-element">
                            <rn:widget path="input/FormInput" name="Incident.CustomFields.c.codigo_error" label_input="Código de Error"
                                required="false" />
                        </div>
                      
                        
                        
                    <div style="display:block" >
                    <?if (!empty($p_p)) { ?>
                    <div class="form-element form-element-wide" style="display:none">
                    <?}else{?>
                    <div class="form-element form-element-wide">
                    <?};?>
                            <rn:widget path="input/ProductCategoryInputExtended" exclude="" name="Incident.Product" 
                                required_lvl="1"  label_input="Tipo"/>
                        </div>
                        <?if (!empty($p_p)) { ?>
                    <div class="form-element form-element-wide" style="display:none">
                    <?}else{?>
                    <div class="form-element form-element-wide">
                    <?};?>
                            <rn:widget path="input/ProductCategoryInputExtended" exclude="" name="Incident.Category"
                                required_lvl="2" />
                        </div>
                    </div>
                         <?if ($p_c!='96') : ?>
                         <div class="form-element" style="background-color: #FFFFFF; color: black;">
                            <rn:widget path="input/FormInput"  name="Incident.CustomFields.c.equipo_detenido" label_input="Operacion detenida"
                                required="true" />
                        </div>
                        
                        <div class="form-element">
                            <rn:widget path="input/FormInput" id="detenidoq" name="Incident.CustomFields.c.severity" label_input="Severidad"
                                required="true" />
                        </div>
                        <?endif;?>

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