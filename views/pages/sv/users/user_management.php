<rn:widget path="custom/redirect/RedirectBlocked" module_id="7" error_page="/app/sv/error.php" />
<rn:meta title="Gestión de usuarios" template="2020.php" login_required="true" force_https="true" />

<?php
$CI = get_instance();
$obj_info_contact = $CI->session->getSessionData('info_contact');
$org_id           = $obj_info_contact["Org_id"];


if (is_numeric($org_id))

$static_filter = "org_id=".$org_id;

else
$static_filter = "";
?>

<?php if ($static_filter !== null && !empty($static_filter)): ?>
  <rn:container report_id="101573">
    <rn:widget path="reports/Grid" static_filter="#rn:php:$static_filter#" add_params_to_url="org" per_page="30"/>
    <rn:widget path="reports/Paginator"  static_filter="#rn:php:$static_filter#" per_page="30"/>
  </rn:container>
<?php endif; ?>
