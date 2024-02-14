<?
$hidde_init = '';
if(!$this->data['attrs']['read_only'])
{
  $hidde_init = 'hidden="hidden" style="display: none;"';
}
?>

<div id="rn_<?=$this->instanceID?>" class="<?=$this->classList?> form-request">
	<div class="rn_ScreenForm">
		<div class="content-title wrapper">
		  <? if(!$this->data['attrs']['read_only']): ?>
		  	<h1>#rn:msg:CUSTOM_MSG_ASSISTANT_TECNICAL_TITLE#</h1>
			<p>#rn:msg:CUSTOM_MSG_NEW_INPUT_R_REQUEST#</p>
			<br>
		  <? else: ?>
		  	<h1>#rn:msg:CUSTOM_MSG_ASSISTANT_TECNICAL_TITLE#</h1>
		  <? endif; ?>
		</div>

	  <div id="rn_ErrorLocation" class="rn_MessageBox rn_ErrorMessage" hidden="hidden" style="display: none;">
	    <h2 role="alert">#rn:msg:CUSTOM_MSG_ERROR#</h2>
	    <div class="messages">
	    </div>
	  </div>

	  <!-- Inicio del formulario -->
		<form id="form_supplier" method="post">

			<? if(!$this->data['attrs']['read_only']): ?>
				<rn:widget path="custom/integer/Steps" />
			<? endif; ?>


			<fieldset class="rn_StepGroup rn_Step1"<? ?>>
				<div class="form-content">
					<legend></legend>
					<!-- <rn:widget path="custom/input/SelectField" id="dispatch_address" name="dispatch_address" label_input="Dirección" value="#rn:php:$this->data['dispatch_address']#" required="true" disabled="true" wide="true" /> -->
					<rn:widget path="custom/input/InputField" id="dispatch_address" name="dispatch_address" label_input="Dirección" value="#rn:php:$this->data['dispatch_address']#" required="true" disabled="true" display_type="text" />
					<rn:widget path="custom/input/InputField" id="direccion_incorrecta" name="direccion_incorrecta" label_input="¿Dirección Incorrecta?" options="Sí,No" value="#rn:php:$this->data['equipo_detenido_cliente']#" display_type="boolean" required="true" disabled="false" />
					<rn:widget path="custom/input/InputField" id="direccion_correcta" name="direccion_correcta" label_input="Dirección Correcta" value="#rn:php:$this->data['direccion_correcta']#" required="true" display_type="text" visible="false" />
				</div>
			</fieldset>

			<fieldset class="rn_StepGroup rn_Step1"<? ?>>
				<div class="form-content">
					<legend>#rn:msg:CONTACT_INFO_LBL#</legend>
					
					<rn:widget path="custom/input/InputField" placeholder="Nombre Apellido" id="contact_name" name="contact_name" label_input="Nombre Contacto" value="#rn:php:$this->data['contact_name']#" required="true" disabled="true" maxlength="15" />
					<rn:widget path="custom/input/InputField" placeholder="90000000" id="contact_phone" name="contact_phone" label_input="Teléfono" value="#rn:php:$this->data['contact_phone']#" required="true" disabled="false" maxlength="9" display_type="number" />
					<rn:widget path="custom/input/InputField" id="contact_email" name="contact_email" label_input="Correo Electrónico" value="#rn:php:$this->data['contact_email']#" required="true" disabled="true" display_type="text" />
				</div>		
			</fieldset>

			<fieldset class="rn_StepGroup rn_Step1"<? ?>>
					<div class="form-content">
						<legend>Información de asistencia</legend>
						<rn:widget path="custom/input/SelectField" id="suggested_type" name="suggested_type" label_input="Motivo" value="#rn:php:$this->data['suggested_type']#" required="true" disabled="false" wide="false" />
						<rn:widget path="custom/input/InputField" placeholder="000-0" id="codigo_error" name="codigo_error" label_input="Código de Error" value="" display_type="text" />
						<rn:widget path="custom/input/InputField" id="equipo_detenido_cliente" name="equipo_detenido_cliente" label_input="Equipo Detenido" options="Sí,No" value="#rn:php:$this->data['equipo_detenido_cliente']#" display_type="boolean" required="true" disabled="false" />
						<rn:widget path="custom/input/InputField" placeholder="" id="contact_detail" name="contact_detail" label_input="Cuentenos su problema" value="" display_type="textarea" wide="true" />
					</div>
			</fieldset>

			<?if (isset($this->data['attrs']['read_only']) || $this->data['attrs']['read_only']): ?>
				<div class="form-buttons rn_StepGroup rn_Step2"<?= $hidde_init ?>>
					<div class="rn_FormSubmit">
					<input type="button" id="btn_cancel" name="btn_cancel" value="Cancelar">
					<input type="button" id="btn_submit" name="btn_submit" value="Solicitar">
					</div>
				</div>
			<?endif;?>

		</form>

		<fieldset class="rn_StepGroup rn_Step2"<?= $hidde_init ?>>
				<div class="form-content">
					<legend>#rn:msg:CUSTOM_MSG_HH_INFORMATION#</legend>
					<? if(!$this->data['attrs']['read_only']): ?>
						<div class="rn_Group rn_Info">Si su HH no se encuentra en la lista, favor ingrese su requerimiento en el siguiente enlace ( <a href="/app/sv/request/contact/p/68/c/76">Solicitud de Actualización</a> ).</div>
					<? endif; ?>
					<? if(!$this->data['attrs']['read_only']): ?>
						<rn:widget path="custom/input/SelectField" id="hh_brand_list" name="hh_brand_list" label_input="Seleccionar Marca de HH" required="true" disabled="false" />
						<rn:widget path="custom/input/SelectField" id="hh_selector" name="hh_selector" label_input="Seleccionar HH" required="true" disabled="true" colapsible="true" multiple="true" filter="true" />
						<rn:widget path="custom/input/InputField" placeholder="000000" id="hh_selected" name="hh_selected" label_input="HH Seleccionada" value="#rn:php:$this->data['hh_selector']#" required="true" disabled="true" display_type="number" visible="false" />
						<rn:widget path="custom/input/InputField" placeholder="0" id="hh_counter_bw" name="hh_counter_bw" label_input="Contador B/N" value="#rn:php:$this->data['hh_counter_bw']#" disabled="false" display_type="number" maxlength="8" max_value="15000000" />
						<rn:widget path="custom/input/InputField" placeholder="0" id="hh_counter_color" name="hh_counter_color" label_input="Contador Color" value="#rn:php:(($this->data['hh_counter_color'])?$this->data['hh_counter_color']:'')#" disabled="false" display_type="number" maxlength="8" max_value="15000000" />
					<? else: ?>
						<rn:widget path="custom/input/InputField" placeholder="000000" id="hh_selected" name="hh_selected" label_input="HH Seleccionada" value="#rn:php:$this->data['hh_selector']#" required="true" disabled="true" display_type="number" />
						<rn:widget path="custom/input/InputField" placeholder="" id="hh_brand" name="hh_brand" label_input="Marca" value="#rn:php:$this->data['marca_hh']#" disabled="true" />
						<rn:widget path="custom/input/InputField" placeholder="" id="hh_model" name="hh_model" label_input="Modelo" value="#rn:php:$this->data['model_hh']#" disabled="true" />
						<rn:widget path="custom/input/InputField" placeholder="0" id="hh_counter_bw" name="hh_counter_bw" label_input="Contador B/N" value="#rn:php:$this->data['hh_counter_bw']#" disabled="false" display_type="number" maxlength="8" max_value="15000000" />
						<rn:widget path="custom/input/InputField" placeholder="0" id="hh_counter_color" name="hh_counter_color" label_input="Contador Color" value="#rn:php:(($this->data['hh_counter_color'])?$this->data['hh_counter_color']:'')#" disabled="false" display_type="number" maxlength="8" max_value="15000000" />
						
					<? endif; ?>
					<? if(!$this->data['attrs']['read_only']): ?>
						<div class="form-element-wide form-buttons">
							<div class="rn_FormSubmit">
							<input type="button" id="btn_get_hh" name="btn_get_hh" value="Siguiente Paso">
							</div>
						</div>
				|	<? endif; ?>
				</div>
			</fieldset>
    	<div class="rn_ScreenSuccess rn_StepGroup rn_Step2" style="display: none;" hidden="hidden">
			<div class="rn_PageContent rn_Home rn_Wraper">
				<div class="rn_SuccessMesage">
					<div class="message">
						<h2>Gracias por registrar su solicitud</h2>
						<p>Su solicitiud <strong>Asistencia Técnica</strong> ha sido registrada</p>
						<p id="p_refNo">y asociada al número de referencia <strong><a href=""></a></strong>.</p>
						<p id="p3">#rn:msg:NEED_UPD_EXP_OR_M_GO_TO_HIST_O_UPD_IT_MSG# <a href="">Enlace</a></p>
						<br>
						<p class="message_phone" style="display:none;" hidden="hidden">#rn:msg:CUSTOM_MSG_REQ_PHO_SUP_REQUEST_MSG#</p>
					</div>
          			<div class="icon"></div>
        		</div>
      		</div>
      		<div class="form-buttons">
          		<div class="rn_FormSubmit">
            		<input type="button" id="btn_requests" name="btn_requests" value="Ver Mis Solicitudes">
            		<input type="button" id="btn_new_request" name="btn_new_request" value="Realizar Otra Solicitud">
          		</div>
      		</div>
    	</div>
	</div>
</div>
