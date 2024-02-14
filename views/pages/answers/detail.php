<rn:widget path="custom/redirect/RedirectBlocked" />
<rn:meta title="Dimacofi - Sucursal Virtual" template="2020.php" clickstream="faq_detail_redirect" login_required="false" />
<?
$a_id = \RightNow\Utils\Url::getParameter('a_id');

if($a_id) {
  header('location: /app/sv/faq/detail/a_id/' . $a_id);
}
?>

