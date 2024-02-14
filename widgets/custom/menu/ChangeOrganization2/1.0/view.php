<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">

<?
function select_option($ops,$id,$name)
{
  $selc='';
  $i=0;

  echo '<select  required="true" id="' . $name . '">';



  foreach ($ops['id'] as $key => $value) {
    $selc='';
    if($value==$id)
    {
      $selc='selected';
    }
    echo '<option value="' . $ops['id'][$i]  . '" ' . $selc . '>'  . $ops['values'][$i]  .  '</option>';
    $i++;
  }
  echo '</select>';
}
$ruts=$this->data['js']['datos'];
$CI = get_instance();
$obj_info_contact= $CI->session->getSessionData('info_contact');

//echo json_encode($obj_info_contact);
?>

    <div class="rn_PageHeader rn_Account">
        <div class="rn_Container">
            <h1>Cambio de Organización</h1>
        </div>
    </div>

    <div id="rn_PageContent" class="rn_Profile">
        <div class="rn_Padding">
            <rn:container>
                <div class="rn_Required rn_LargeText">#rn:url_param_value:msg#</div>

                    <div id="rn_ErrorLocation" style="display:none;"></div>
                        <legend>Organización Actual</legend>
                      
                        <div class="rn_FieldDisplayFull rn_Output">
                            <span
                                class="rn_Label"></span><?=$this->data['js']['arut'] . '<br>' .$this->data['js']['aName'] .'<br><br> '?>
                        </div>
                        <legend>Cambio de Organización</legend>
                        <span>
                            <p>Seleccione la organización</p>
                        </span>
                        <span class="rn_DataLabel">Organización</span>
                        <br>
                        <?
            $i=0;
            if(count($ruts->List->data)>1)
            {
                
                foreach($ruts->List->data as $key => $rut)
                {
                    ?>
                    <input name="intereses" type="radio" value="rbipeliculas"/> <?=' - ' . $rut->rut_cliente . ' - ' . $rut->nombre_cliente?> <br>
                    <?
                    //$organization['values'][$i]=;
                    //$organization['id'][$i]=$rut->rut_cliente;
                    $i++;
                }
                //select_option($organization, 0,'organization');
            }
            else
            {

                
                ?>

                        <span>
                            <p>Sólo Tiene una organizacion asociada a su perfil, No puede cambiar a otra</p>
                        </span>

                        <?
            }
          ?>



                    <div class="rn_FormSubmit">
					      <input type="button" id="btn_save" name="btn_save" value="Guardar Cambios">
					</div>
            </rn:container>
        </div>
    </div>

</div>