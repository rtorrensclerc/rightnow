<?
$hidde_init = '';
if(!$this->data['attrs']['read_only'])
{
  $hidde_init = 'hidden="hidden" style="display: none;"';
}

?>


<div id="rn_<?=$this->instanceID?>" class="<?=$this->classList?> form-request">
	<!-- <div id="message_form"></div> -->

	<div class="rn_ScreenForm">
	
	  <div id="rn_ErrorLocation" class="rn_MessageBox rn_ErrorMessage" hidden="hidden" style="display: none;">
	    <h2 role="alert">#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_ERRORS#</h2>
	    <div class="messages">
	    </div>
	  </div>

	  <!-- Inicio del formulario -->
		<form id="form_supplier" method="post">
    <rn:widget path="custom/integer/Steps" />
     

		<fieldset class="rn_StepGroup rn_Step1">
            <legend>Datos Requerimiento</legend>
      
          <h2>Solicitud Logistica Inversa</h2>
      
               
               
                <div class="form-content">
                        <div class="form-element">
                            <rn:widget path="input/FormInput" name="Contact.Name.First" label_input="Nombre" required="true" />
                        </div>
                        <div class="form-element">
                            <rn:widget path="input/FormInput" name="Contact.Name.Last" label_input="Apellido" required="true" />
                        </div>

                        <div class="form-element">
                            <rn:widget path="input/FormInput" name="Contact.CustomFields.c.name_org" label_input="Nombre Empresa" required="false" />
                        </div>

                        <div class="form-element" >
                        <rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address" initial_focus="true"   label_input="Correo Electrónico" required="true" />
                        </div>
                        
                        
                        <div class="form-element">
                            <rn:widget path="input/FormInput" name="Contact.Phones.OFFICE.Number" label_input="Teléfono"
                                required="true" />
                        </div>

                        <div class="form-element" >
                        
                        </div>
                        <rn:widget path="custom/input/SelectField" id="hh_brand_list" name="hh_brand_list" label_input="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_SELECT_BRAND_HH#" required="true" disabled="false" />
                        <rn:widget path="custom/input/InputField" placeholder="0" 		id="Cantidad" 		name="Cantidad" 		label_input="Cantidad"  required="true" disabled="false" 	display_type="number" maxlength="4" max_value="1000" />

                        <!--rn:widget path="custom/input/SelectField" id="dispatch_address" name="dispatch_address" label_input="Direccion de Retiro"  required="true" disabled="false" wide="true" /-->
                        <rn:widget path="custom/input/InputField"  placeholder="Dirección, Nuemero, Comuna"  id="dispatch" 		name="dispatch" 		label_input="Direccion de Retiro"  required="true" disabled="false" 		display_type="textarea"  />
                        <!--rn:widget path="custom/input/SelectField" id="dispatch_address" name="dispatch_address" label_input="Direccion de Retiro"  required="true" disabled="false" wide="true" /-->
                        <rn:widget path="custom/input/InputField"  id="comuna" 		name="comuna" 		label_input="Comuna"  required="true" disabled="false" 		display_type="text"  />

                        <div class="form-element form-element-wide">  <!--style="display:none"-->
                          <!--rn:widget path="input/FormInput" name="Incident.CustomFields.c.marca_hh" label_input="Marca"required="true"-->
                          <!--rn:widget path="input/FormInput" name="Incident.CustomFields.c.gasto" label_input="Gasto"required="true" /-->
             
                          <!--rn:widget path="custom/input/ProductCategoryInputExtended" exclude="66,67,50,68" name="Incident.Product" required_lvl="1" default_value="68" label_input="Tipo" /-->
                          <!--rn:widget path="custom/input/ProductCategoryInputExtended" exclude="" default_value="122" name="Incident.Category" required_lvl="1"  /-->
                        </div>    
                        
                        <div class="form-element form-element-wide">
                          <rn:widget path="custom/input/InputField" placeholder="0" 		id="Comments" 		name="Comments" 		label_input="Comentario"  required="true" disabled="false" 	display_type="textarea"  rows="7" cols="260"/>
                        </div>

                    
                </div>
            </fieldset>

		
		    <div class="rn_FormSubmit">
		      <input type="button" id="btn_submit" name="btn_submit" value="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_REQUEST#" disabled="disabled">
		    </div>
		
		

		</form>
	</div>
  
	<div  class="rn_ScreenSuccess rn_StepGroup rn_Step2" style="display: none;" hidden="hidden">

		<div class="rn_SuccessMesage">
			<div class="message">
				<p>#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_SUCCESS_EMAIL#</p>
			</div>
			<div class="icon"></div>
		</div>
    <div class="form-buttons">
		    <div class="rn_FormSubmit">
		      <input type="button" id="btn_requests" 	name="btn_requests" 	value="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_SEE_MY_REQUEST#">
		      <input type="button" id="btn_new_request" name="btn_new_request" 	value="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_ANOTHER_REQUEST#">
		    </div>
		 </div>
		
	</div>
</div>
