<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
  <ul class="reset-list">
    <li class="rn_AccountItemItem">
      <div class="rn_AccountItemIcon">
        <a href="#" class="rn_Sprite">Mi Cuenta</a>
      </div>
      <ul class="rn_AccountItemItems">
        <li>
          <rn:widget path="navigation/NavigationTab" label_tab="Información de la Cuenta" link="/app/account/profile" pages="account/profile"
          />
        </li>
        <li>
          <rn:widget path="navigation/NavigationTab" label_tab="Cambiar la Contraseña" link="/app/account/change_password" pages="account/change_password"
          />
        </li>
      </ul>
    </li>
  </ul>
</div>