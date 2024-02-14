<rn:widget path="custom/redirect/RedirectBlocked" module_id="7" error_page="/app/sv/error.php" />
<rn:meta title="Crear usuario" template="2020.php" login_required="true" force_https="true" />

<?php
$CI               = get_instance();
$obj_info_contact = $CI->session->getSessionData('info_contact');
$org_id           = $obj_info_contact['Org_id'];
?>

<rn:widget path="custom/administration/UserAdministration" />
