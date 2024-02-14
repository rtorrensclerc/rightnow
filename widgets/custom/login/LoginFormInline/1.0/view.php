<? /* Overriding LoginForm's view */ ?>
<div id="rn_<?=$this->instanceID;?>" class="<?= $this->classList ?>">
    <rn:block id="top"/>
    <div id="rn_<?=$this->instanceID;?>_Content">
        <rn:block id="preErrorMessage"/>
        <div id="rn_<?=$this->instanceID;?>_ErrorMessage"></div>
        <rn:block id="postErrorMessage"/>
        <a href="/app/#rn:config:CP_ACCOUNT_ASSIST_URL##rn:session#">#rn:msg:FORGOT_YOUR_USERNAME_OR_PASSWORD_MSG#</a>

        <form id="rn_<?=$this->instanceID;?>_Form" onsubmit="return false;">
            <rn:block id="preUsername"/>
        <? if($this->data['attrs']['label_username'] != ""):?>
            <label for="rn_<?=$this->instanceID;?>_Username"><?=$this->data['attrs']['label_username'];?></label>
        <? endif;?>
            <input id="rn_<?=$this->instanceID;?>_Username" type="text" <?if($this->data['attrs']['placeholder_username'] != "") echo " placeholder=\"".$this->data['attrs']['placeholder_username']."\""; ?>maxlength="80" name="Contact.Login" autocorrect="off" autocapitalize="off" value="<?=$this->data['username'];?>"/>
            <rn:block id="postUsername"/>
        <? if(!$this->data['attrs']['disable_password']):?>
            <rn:block id="prePassword"/>
        <? if($this->data['attrs']['label_password'] != ""):?>
            <label for="rn_<?=$this->instanceID;?>_Password"><?=$this->data['attrs']['label_password'];?></label>
        <? endif;?>
            <input id="rn_<?=$this->instanceID;?>_Password" type="password" <?if($this->data['attrs']['placeholder_password'] != "") echo " placeholder=\"".$this->data['attrs']['placeholder_password']."\""; ?>maxlength="20" name="Contact.Password" <?=($this->data['attrs']['disable_password_autocomplete']) ? 'autocomplete="off"' : '' ?>/>
            <rn:block id="postPassword"/>
        <? elseif($this->data['isIE']):?>
            <label for="rn_<?=$this->instanceID;?>_HiddenInput" class="rn_Hidden">&nbsp;</label>
            <input id="rn_<?=$this->instanceID;?>_HiddenInput" type="text" class="rn_Hidden" disabled="disabled" />
        <? endif;?>
            <rn:block id="preSubmit"/>
            <input id="rn_<?=$this->instanceID;?>_Submit" type="submit" value="<?=$this->data['attrs']['label_login_button'];?>"/>
            <rn:block id="postSubmit"/>
            <br/>
        </form>

    </div>
    <rn:block id="bottom"/>
</div>
