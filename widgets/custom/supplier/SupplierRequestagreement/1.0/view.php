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
		<div class="content-title wrapper">
		  <? if(!$this->data['attrs']['read_only']): ?>
			
		  	<h1>#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_SUPPLIER_REQUEST#</h1>
			 
			  <p>#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_NEW_INPUT_REQUEST#</p>
			  <br>
		  <? else: ?>
		
		  	<h1>#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_SUPPLIER_REQUEST_DETAIL#</h1>
		  <? endif; ?>
		</div>
		<div class="message">
		#rn:msg:CUSTOM_MSG_COVID_WARNING#
        </div>
	  <div id="rn_ErrorLocation" class="rn_MessageBox rn_ErrorMessage" hidden="hidden" style="display: none;">
	    <h2 role="alert">#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_ERRORS#</h2>
	    <div class="messages">
	    </div>
	  </div>

	  <!-- Inicio del formulario -->
		<form id="form_supplier" method="post">

      <? if(!$this->data['attrs']['read_only']): ?>
        <rn:widget path="custom/integer/Steps" />
      <? endif; ?>

			<fieldset class="rn_StepGroup rn_Step1">

					    <div class="form-content">
		      <legend>#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_HH_INFORMATION#</legend>
			 
		      <? if(!$this->data['attrs']['read_only']): ?>
            <div class="rn_Group rn_Info">#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_UPDATE_LINK#</div>
		      <? endif; ?>
			  <div class="pending_request_list">
					      			
									  <span class="test">123456</span>
									  
		     </div>
          <? if(!$this->data['attrs']['read_only']): ?>
          <rn:widget 		path="custom/input/SelectField" 					id="hh_brand_list" 		name="hh_brand_list" 		label_input="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_SELECT_BRAND_HH#" 																								required="true" disabled="false" />
		      <rn:widget 	path="custom/input/SelectField" 					id="hh_selector" 		name="hh_selector" 			label_input="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_SELECT_HH#" 																									required="true" disabled="true"		colapsible="true" multiple="true" filter="true" />
		      <rn:widget 	path="custom/input/InputField" placeholder="000000" id="hh_selected" 		name="hh_selected" 			label_input="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_SELECTED_HH#" 		value="#rn:php:$this->data['hh_selector']#" 												required="true" disabled="true" 	display_type="number" visible="false" />
          <? else: ?>
            <rn:widget 		path="custom/input/InputField" placeholder="000000" id="hh_selected" 		name="hh_selected" 			label_input="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_SELECTED_HH#" 		value="#rn:php:$this->data['hh_selector']#" 												required="true" disabled="true" 	display_type="number" />
            <rn:widget 		path="custom/input/InputField" placeholder="" 		id="hh_brand" 			name="hh_brand" 			label_input="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_BRAND#" 			value="#rn:php:$this->data['marca_hh']#" 																	disabled="true" />
            <rn:widget 		path="custom/input/InputField" placeholder="" 		id="hh_model" 			name="hh_model" 			label_input="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_MODEL#" 				value="#rn:php:$this->data['model_hh']#" 																	disabled="true" />
		  <? endif; ?>
		      <? if(!$this->data['attrs']['read_only']): ?>
			      <div class="form-buttons form-element-wide">
					    <div class="rn_FormSubmit">
					      <input type="button" id="btn_get_hh" name="btn_get_hh" value="Siguiente Paso">
					    </div>
					  </div>
					<? endif; ?>
		    </div>
		  </fieldset>

		  <fieldset class="rn_StepGroup rn_Step2">
		    <div class="form-content">
		      <legend>#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_OFFICE_INFO#</legend>
		      <rn:widget path="custom/input/SelectField" id="dispatch_address" name="dispatch_address" label_input="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_ADDRESS_LBL#" value="#rn:php:$this->data['dispatch_address']#" required="true" disabled="true" wide="true" />
          <!-- <rn:widget path="custom/input/InputField" id="dispatch_address" name="dispatch_address" label_input="Dirección" value="#rn:php:$this->data['dispatch_address']#" required="true" disabled="true" display_type="text" /> -->
          <!-- <rn:widget path="custom/input/InputField" id="direccion_incorrecta" name="direccion_incorrecta" label_input="¿Dirección Incorrecta?" options="Sí,No" value="#rn:php:$this->data['equipo_detenido_cliente']#" display_type="boolean" required="true" disabled="false" /> -->
          <!-- <rn:widget path="custom/input/InputField" id="direccion_correcta" name="direccion_correcta" label_input="Dirección Correcta" value="#rn:php:$this->data['direccion_correcta']#" required="true" display_type="text" visible="false" /> -->

		      <div class="rn_Group rn_Info">
	          <p>#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_ADDRES_NOT_FOUND#</p>
	        </div>

		    </div>
		  </fieldset>

		  <fieldset class="rn_StepGroup rn_Step2">
		    <div class="form-content">
		      <legend>#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_CONTACT_INFO_LBL#</legend>
		      <? if(!$this->data['attrs']['read_only']): ?>
            <div class="rn_Group rn_Info">
              <p>#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_CUSTOMER_WHO_RECIEVE_INFO#</p>
            </div>
		      	<rn:widget 	path="custom/input/InputField" placeholder="#rn:msg:CUSTOM_MSG_SUPPLIER_RESQUEST_SURNAME#"		id="contact_name" 		name="contact_name" 	label_input="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_CONTACT_NAME#" 	value="#rn:php:$this->data['contact_name']#" 							required="true" disabled="true" maxlength="15" />
		      	<rn:widget 	path="custom/input/InputField" placeholder="90000000" 											id="contact_phone" 		name="contact_phone" 	label_input="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_ENUM_PHONE_LBL#"	value="#rn:php:$this->data['contact_phone']#" 	display_type="number"	required="true" disabled="true" maxlength="9" />
		      <? endif; ?>
		      <rn:widget 	path="custom/input/InputField" placeholder="#rn:msg:CUSTOM_MSG_SUPPLIER_RESQUEST_EXAMPLE#" 		id="contact_comment"	name="contact_comment" 	label_input="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_COMMENT_LBL#"		value="#rn:php:$this->data['contact_comment']#" display_type="text" 	required="true" disabled="true" maxlength="25" show_count="true" />
		    </div>
		  </fieldset>

      <?if (!isset($this->data['attrs']['read_only']) || !$this->data['attrs']['read_only']): ?>
		  <div class="form-buttons rn_StepGroup rn_Step2">
		    <div class="rn_FormSubmit">
          <input type="button" id="btn_get_list_supplier" name="btn_get_list_supplier" value="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_GET_SUPPLIER_LIST#" disabled="disabled">
		    </div>
		  </div>
			<?endif;?>

		  <fieldset class="rn_StepGroup rn_Step3">
		    <div class="form-content">
		      <legend>#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_SUPPLY_INFO#</legend>
		      <div class="rn_Table">
		      	<table id="supplierItems">
			      	<thead>
			      		<tr>
				      		<th class="rn_TextRight">#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_PART_NUMBER#</th>
				      		<th class="rn_TextLeft">#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_DESCRIPTION_LBL#</th>
				      		<th class="rn_TextCenter">#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_QUANTITY_LBL#</th>
				      	</tr>
			      	</thead>
			      	<tbody>
			      		<? if(!$this->data['attrs']['read_only']): ?>
				      		<tr class="templateRow" style="display: none;" hidden="hidden">
					      		<td class="rn_TextRight">---</td>
					      		<td class="rn_TextLeft">---</td>
					      		<td class="rn_TextCenter">
					      			<div class="rn_Quantity">
					      				<span class="action subtract">
					      					<span class="icon rn_Assets">-</span>
					      				</span>
					      				<span class="qty">0</span>
					      				<span class="action add">
					      					<span class="icon rn_Assets">+</span>
					      				</span>
					      			</div>
					      		</td>
					      	</tr>
					      	<tr class="initialRow">
					      		<td colspan="3" class="rn_TextCenter">
					      			-
					      		</td>
					      	</tr>
					      <? else: ?>
					      	<? foreach($this->data['items'] as $item): ?>
						      	<tr class="templateRow">
						      		<td class="rn_TextRight"><?= $item->part_number ?></td>
						      		<td class="rn_TextLeft"><strong><?= $item->name ?></strong><br><?= $item->alias ?></td>
						      		<td class="rn_TextCenter"><?= $item->quantity_selected ?></td>
						      	</tr>
						      <? endforeach; ?>
					      <? endif; ?>
			      	</tbody>
		      </table>
		      </div>
		    </div>
		  </fieldset>

		  <?if (!isset($this->data['attrs']['read_only']) || !$this->data['attrs']['read_only']): ?>
		  <div class="form-buttons rn_StepGroup rn_Step3">
		    <div class="rn_FormSubmit">
		      <input type="button" id="btn_cancel" name="btn_cancel" value="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_CANCEL#">
		      <input type="button" id="btn_submit" name="btn_submit" value="#rn:msg:CUSTOM_MSG_SUPPLIER_REQUEST_REQUEST#" disabled="disabled">
		    </div>
		  </div>
			<?endif;?>

		</form>
	</div>
	<div  class="rn_ScreenSuccess rn_StepGroup rn_Step4" style="display: none;" hidden="hidden">

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
