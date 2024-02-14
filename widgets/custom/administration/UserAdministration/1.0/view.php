<?
  $user_id = getUrlParm('u_id');
?>

<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
  <div class="rn_PageHeader rn_Wraper">
    <div class="rn_PageHeaderInner">
      <div class="rn_PageHeaderCopy">
        <h1>Craer Usuario</h1>
      </div>
    </div>
  </div>
  
  <div class="rn_PageContent rn_Wraper">
    <div class="rn_PageContentInner">

      <div id="rn_ErrorLocation" style="display:none;">
        <div class="messages"></div>
      </div>

      <fieldset>
        <legend>Información de Personal y Contacto</legend>
        <div class="form-content">
          <rn:widget path="custom/input/InputField" name="user_name" id="user_name" required="true" label_input="Nombre" value="#rn:php:$this->data['js']['user']['name']#" />
          <rn:widget path="custom/input/InputField" name="user_last" id="user_last" label_input="Apellido" required="true" value="#rn:php:$this->data['js']['user']['last']#" />
          <rn:widget path="custom/input/InputField" name="user_rut" id="user_rut" initial_focus="Rut" label_input="RUT" required="true" value="#rn:php:$this->data['js']['user']['rut']#" />
          <rn:widget path="custom/input/InputField" name="user_email" id="user_email" label_input="Correo Electrónico" required="true" value="#rn:php:$this->data['js']['user']['email']#" />
          <rn:widget path="custom/input/InputField" name="user_phone" id="user_phone" label_input="Teléfono" required="false" value="#rn:php:$this->data['js']['user']['phone']#" />
        </div>
      </fieldset>

      <fieldset>
        <legend>Información de Usuario </legend>
        <div class="form-content">
          <rn:widget path="custom/input/SelectField" name="user_profile" id="user_profile" label_input="Perfil" required="true" value="#rn:php:$this->data['js']['user']['profile']#" />

          <? if (!empty($user_id)): ?>
            <rn:widget path="custom/input/InputField" options="Sí,No" name="user_disabled" id="user_disabled" label_input="¿Desactivar?" value="#rn:php:$this->data['js']['user']['blocked']#" display_type="boolean" />
          <? endif;?>
        </div>
      </fieldset>

      <div class="form-buttons">
        <div class="rn_FormSubmit">
          <? if (empty($user_id)): ?>
            <input id="btn_submit" type="button" value="Crear">
          <? else : ?>
            <input id="btn_update" type="button" value="Actualizar">
            <rn:widget path="custom/login/LoginResetPassword" />
          <? endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>