<?
$hidde_init = '';
if(!$this->data['attrs']['read_only'])
{
  $hidde_init = 'hidden="hidden" style="display: none;"';
}

?>


<?
$p_p = 68;
$p_c = 122;
$p_d = 124;
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
        <?if ($p_c=='124') : ?>
          <h2>Solicitud Logistica Inversa</h2>
        <?endif;?>
               
               
                <div class="form-content">
                <div class="form-element" >
                            <rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" initial_focus="true"
                                label_input="Correo Electrónico" required="true" />
                        </div>
                        
                        <div class="form-element">
                            <rn:widget path="input/FormInput" name="Contact.Phones.OFFICE.Number" label_input="Teléfono"
                                required="true" />
                        </div>
                        <?if ($p_c=='122') : ?>
                       
                        
                        <?endif;?>
                        <rn:widget path="custom/input/SelectField" id="hh_brand_list" name="hh_brand_list" label_input="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_SELECT_BRAND_HH#" required="true" disabled="false" />
                        <rn:widget path="custom/input/InputField" placeholder="0" 		id="Cantidad" 		name="Cantidad" 		label_input="Cantidad"  required="true" disabled="false" 	display_type="number" maxlength="4" max_value="1000" />
                        <rn:widget path="custom/input/SelectField" id="dispatch_address" name="dispatch_address" label_input="Direccion de Retiro"  required="true" disabled="false" wide="true" />


                      
                        <div class="form-element form-element-wide"> <!-- style="display:none"-->
                          <rn:widget path="input/FormInput" name="Incident.CustomFields.c.marca_hh" label_input="Marca"required="true" />
                          <rn:widget path="input/FormInput" name="Incident.CustomFields.c.gasto" label_input="Gasto"required="true" />
             
                          <rn:widget path="custom/input/ProductCategoryInputExtended" exclude="66,67,50,68" name="Incident.Product" required_lvl="1" default_value="68" label_input="Tipo" />
                          <rn:widget path="custom/input/ProductCategoryInputExtended" exclude="" default_value="122" name="Incident.Category" required_lvl="1"  />
                        </div>    
                        
                    

                    <div class="form-element form-element-wide">
                        <rn:widget path="input/FormInput" name="Incident.Threads" required="true"   label_input="Comentarios" />
                    </div>
                </div>
            </fieldset>

            

            <div class="form-buttons">
                <div class="rn_FormSubmit">
                    <!--rn:widget path="input/FormSubmit" label_button="Enviar Solicitud"
                        on_success_url="/app/sv/request/confirm" error_location="rn_ErrorLocation" /-->
                    <input type="button" id="btn_submit" name="btn_submit" value="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_REQUEST#" disabled="disabled"  >  
                </div>
            </div>

        </form>
    </div>
</div>
