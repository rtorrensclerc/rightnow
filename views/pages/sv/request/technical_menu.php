<rn:widget path="custom/redirect/RedirectBlocked" module_id="2" error_page="/app/sv/error.php" />
<rn:meta title="Solicitud de Asistencia Técnica" template="2020.php" login_required="true" clickstream="supplier_web"/>

<?
    $id        = \RightNow\Utils\URL::getParameter('i_id');
    $read_only = ($id) ? true : false;
?>
 <div class="rn_Container">
      <div class="rn_Shortcuts">
        <ul class="rn_ShortcutsList">

<? if (isEnabled(1)) : ?>
            <li class="rn_ShortcutsItem rn_ShortcutsItem-cc" style="display:none">
              <a href="/app/sv/request/contact">
                <div class="rn_ShortcutsIcon rn_ShorcutsSprite"></div>
                <h3 class="rn_ShortcutsList">Tu Opinión </br>Nos Importa</h3>
              </a>
            </li>
<? endif;?>
<? if (isEnabled(1)) : ?>
<li class="rn_ShortcutsItem rn_ShortcutsItem-cc">
    <a href="/app/sv/request/contact">
    <div class="rn_ShortcutsIcon rn_ShorcutsSprite"></div>
    <h3 class="rn_ShortcutsList">Tu Opinión </br>Nos Importa</h3>
    </a>
</li>
<? endif;?>
</ul>
      </div>
    </div>
