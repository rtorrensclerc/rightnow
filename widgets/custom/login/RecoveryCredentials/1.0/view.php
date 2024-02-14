<? /* Overriding EmailCredentials's view */ ?>
<div id="rn_<?= $this->instanceID; ?>" class="<?= $this->classList ?>">
    <rn:block id="top" />
    <? if ($this->data['attrs']['label_heading'] !== '') : ?>
        <rn:block id="heading">
            <h2><?= $this->data['attrs']['label_heading'] ?></h2>
        </rn:block>
    <? endif; ?>
    <? if ($this->data['attrs']['label_description'] !== '') : ?>
        <rn:block id="description">
            <p><?= $this->data['attrs']['label_description'] ?></p>
        </rn:block>
    <? endif; ?>
    <? $selectorPrefix = "rn_{$this->instanceID}_{$this->data['attrs']['credential_type']}"; ?>
    <!-- <form id="<?=$selectorPrefix;?>_Form" onsubmit="return false;" action="/cc/Auth/recoveryPassword"> -->
    <form id="<?=$selectorPrefix;?>_Form" action="/cc/Auth/recoveryPassword">
        <rn:block id="label">
            <label for="<?= $selectorPrefix; ?>_Input"><?= $this->data['attrs']['label_input']; ?></label>
        </rn:block>
        <rn:block id="preInput" />
        <div class="rn_Hidden">
        <rn:widget path="input/FormInput" name="contact.CustomFields.c.json" default_value="" />
        </div>
        <input id="<?= $selectorPrefix; ?>_Input" name="<?= $this->data['js']['request_type']; ?>" type="text" placeholder="usuario@dominio.cl" maxlength="80" autocorrect="off" autocapitalize="off" value="<?= $this->data['email']; ?>" />
        <rn:block id="postInput" />
        <rn:block id="preSubmit" />
        <div class="rn_Hidden">
            <input id="<?= $selectorPrefix; ?>_Submit" type="submit" value="<?= $this->data['attrs']['label_button'] ?>" class="btn" />
        </div>
        <!-- <rn:widget path="input/FormSubmit" label_button="#rn:php:$this->data['attrs']['label_button']#" challenge_required="true" label_on_success_banner="Se envió un código de verificación a tu correo electrónico." on_success_url="none" /> -->
        <rn:widget path="input/FormSubmit" label_button="#rn:php:$this->data['attrs']['label_button']#" challenge_required="true" on_success_url="/app/home" label_on_success_banner="La contraseña fue restablecida y enviada a tu correo electrónico." />
        <rn:block id="postSubmit" />
        <div id="<?= $selectorPrefix; ?>_LoadingIcon"></div>

    </form>
    <rn:block id="bottom" />
</div>