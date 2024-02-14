<? /* Overriding LoginForm's view */ ?>
<div id="rn_<?=$this->instanceID;?>" class="<?= $this->classList ?>">
    <div id="rn_<?=$this->instanceID;?>_Content">

      <? if($this->data['attrs']['is_dialog']): ?>
        <div class="rn_<?=$this->instanceID;?>_Enter-container rn_Enter-container">
          <input type="button" id="rn_<?=$this->instanceID;?>_Enter" class="rn_Enter" name="rn_<?=$this->instanceID;?>_Enter" value="Ingresar">
        </div>
        <div class="rn_<?=$this->instanceID;?>_Form-container rn_Form-container dialog" hidden="hidden" style="display: none;">
        <? else: ?>
        <div class="rn_<?=$this->instanceID;?>_Form-container rn_Form-container">
        <? endif; ?>

          <form id="rn_<?=$this->instanceID;?>_Form" onsubmit="return false;">
          <? if($this->data['attrs']['label_username'] != ""):?>
              <label for="rn_<?=$this->instanceID;?>_Username"><?=$this->data['attrs']['label_username'];?></label>
          <? endif;?>
              <input id="rn_<?=$this->instanceID;?>_Username" type="text" <?if($this->data['attrs']['placeholder_username'] != "") echo " placeholder=\"".$this->data['attrs']['placeholder_username']."\""; ?>maxlength="80" name="Contact.Login" autocorrect="off" autocapitalize="off" value="<?=$this->data['username'];?>"/>
          <? if(!$this->data['attrs']['disable_password']):?>
          <? if($this->data['attrs']['label_password'] != ""):?>
              <label for="rn_<?=$this->instanceID;?>_Password"><?=$this->data['attrs']['label_password'];?></label>
          <? endif;?>
              <input id="rn_<?=$this->instanceID;?>_Password" type="password" <?if($this->data['attrs']['placeholder_password'] != "") echo " placeholder=\"".$this->data['attrs']['placeholder_password']."\""; ?>maxlength="20" name="Contact.Password" <?=($this->data['attrs']['disable_password_autocomplete']) ? 'autocomplete="off"' : '' ?>/>
          <? elseif($this->data['isIE']):?>
              <label for="rn_<?=$this->instanceID;?>_HiddenInput" class="rn_Hidden">&nbsp;</label>
              <input id="rn_<?=$this->instanceID;?>_HiddenInput" type="text" class="rn_Hidden" disabled="disabled" />
          <? endif;?>

          <? if(!$this->data['attrs']['is_dialog']): ?>
              <input id="rn_<?=$this->instanceID;?>_Submit" type="submit" value="<?=$this->data['attrs']['label_login_button'];?>"/>
          <? endif;?>
          </form>

          <? if($this->data['attrs']['show_forget_password']): ?>
            <a href="<?= $this->data['attrs']['url_forget_password'] ?>#rn:session#">#rn:msg:FORGOT_YOUR_USERNAME_OR_PASSWORD_MSG#</a>
          <? endif; ?>

          <div id="rn_<?=$this->instanceID;?>_ErrorMessage"></div>
        </div>

    </div>
</div>
