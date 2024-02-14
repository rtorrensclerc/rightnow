<rn:widget path="custom/redirect/RedirectBlocked" module_id="2,12,3,4,9" error_page="/app/sv/error.php" />
<rn:meta title="Solicitud de Contacto" template="2020.php" login_required="true" clickstream="supplier_web"/>


<script type="text/javascript">

   function changeFunc() {
    var selectBox = document.getElementById("categoria");
    var selectedValue = selectBox.options[selectBox.selectedIndex].value;
	window.location.href = "/app/sv/request/technical/kw/" +selectedValue ;
   }
</script>

<?


function select_option($ops,$id,$name)
{
  $selc='';
  $i=0;

  echo '<select  required="true" id="' . $name . '"    onchange="changeFunc();">';



  foreach ($ops['id'] as $key => $value) {
    $selc='';
    if($value==$id)
    {
      $selc='selected';
    }
    echo '<option value="' . $ops['values'][$i]  . '" ' . $selc . '>'  . $ops['values'][$i]  .  '</option>';
    $i++;
  }
  echo '</select>';
}
$ruts=$this->data['js']['datos'];

$p_p = \RightNow\Utils\Url::getParameter('p');
$p_c = \RightNow\Utils\Url::getParameter('c');
$kw= \RightNow\Utils\Url::getParameter('kw');
$read_only = ($id) ? true : false;
?>
<div class="rn_ScreenForm">

<div class="form-content">

	<div class="rn_FieldDisplay rn_Output">
		<span class="rn_DataLabel">Area de Interés</span>
	<?
	  $opinion['values'][0]='Cobros y Facturación';
		$opinion['values'][1]='Insumos';
		$opinion['values'][2]='Atención Técnica';
		$opinion['values'][3]='Entrega de Insumos';
		$opinion['values'][4]='Instalaciones';
		$opinion['values'][5]='Equipos';
		$opinion['values'][6]='Retiro';
		$opinion['values'][6]='¿Como configurar libreta direcciones HP?';

		$opinion['id'][0]=0;
		$opinion['id'][1]=1;
		$opinion['id'][2]=2;
		$opinion['id'][3]=3;
		$opinion['id'][4]=3;
		$opinion['id'][5]=3;
		$opinion['id'][6]=3;
		$opinion['id'][6]=3;

		select_option($opinion, 0,'categoria');
	?>
	</div>
</div>
</div>