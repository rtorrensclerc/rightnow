<?
  $a_accountValues = $this->CI->session->getSessionData('Account_loggedValues');
  //Parche Uso de cookies
  $a_accountValues  = unserialize($_COOKIE['Account_loggedValues']);

?>
<? if($this->data['attrs']['in_line']) $inline = ' inline'; else $inline = ''; ?>
<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?> <?= $inline ?>">
    <?php
      //if ($this->CI->session->getSessionData('Account_isLogged') == true):
      if ($_COOKIE['Account_isLogged'] == true):
      ?>
      <span class="welcome_msg">Bienvenido <?=$a_accountValues['FullName'] ?></span>
      <input name="btn_disconnect" type="button" value="Desconectar">
    <?php else :    ?>
      <? if(!$this->data['attrs']['is_inline']): ?>
      <h2>Conéctese con una cuenta de técnico</h2>
      <? endif; ?>
      <div class="rn_LoginForm">
        <rn:block id="preErrorMessage"/>
        <div id="rn_<?=$this->instanceID;?>_ErrorMessage"></div>
        <rn:block id="postErrorMessage"/>
          <div>
              <form id="rn_LoginForm_4_Form" onsubmit="return false;">
                  <label for="username">Nombre de usuario</label>
                  <input type="text" maxlength="80" name="username" autocorrect="off" autocapitalize="off" value="">
                  <label for="password">Contraseña</label>
                  <input type="password" maxlength="20" name="password" autocomplete="off">
                  <input name="btn_connect" type="submit" value="Conectar">
              </form>
          </div>
      </div>
    <? endif; ?>
</div>
