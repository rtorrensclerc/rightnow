<span id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?><? if(\RightNow\Utils\Framework::isLoggedIn()) echo ' online'; else echo ' offline'; ?>">
      <rn:condition logged_in="true">
      
        <rn:widget path="custom/menu/AccountItem" />
        
          <rn:widget  path="login/LogoutLink" label="Desconectar"/>
        
        <div class="rn_UserInfo rn_FloatRight">
         #rn:msg:WELCOME_BACK_LBL#
        <strong>
            <rn:field name="Contact.LookupName"/>
						<br>
            <span class="rn_Enterprise"><rn:field name="Contact.Organization.Name"/></span>
        </strong>
       </div>
 
      <rn:condition_else/>
        <? if($this->data['attrs']['show_login']): ?>
          <rn:widget path="custom/login/LoginFormInline" placeholder_username="Usuario" placeholder_password="Contraseña" />
        <? endif; ?>
      </rn:condition>
      
      

</span>

