<?
// PROD
//$a_accountValues  = unserialize($_COOKIE['Account_loggedValues']);


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

$hidde_init = '';
if(!$this->data['attrs']['read_only'])
{
  $hidde_init = 'hidden="hidden" style="display: none;"';
}
$p_p=$this->data['p_p'];
$p_c=$this->data['p_c'];

?>

<div id="rn_<?=$this->instanceID?>" class="<?=$this->classList?>">
    <div class="rn_ScreenForm">
        <div class="content-title wrapper">
            <h1>Tu opinion es importante</h1>
        </div>
    </div>
    <div class="rn_PageContent rn_Wraper">
        #rn:msg:CUSTOM_MSG_FORM_OTHER_TXT#
        <div class="rn_PageContentInner">
            <rn:condition logged_in="false">
                <div class="ask-in-login">
                    <p>Si ya está registrado, inicie sesión</p>
                    <rn:widget path="custom/login/LoginFormMouldable" redirect_url="/app/sv/request/contact" />
                </div>
            </rn:condition>
            <div id="rn_ErrorLocation" class="rn_MessageBox rn_ErrorMessage" hidden="hidden" style="display: none;">
                <h2 role="alert">#rn:msg:CUSTOM_MSG_ERROR#</h2>
                <div class="messages">
                </div>
            </div>
            <form id="rn_QuestionSubmit" method="post" action="/ci/ajaxRequest/sendForm">
                <form id="form_supplier" method="post">

                    <? if(!$this->data['attrs']['read_only']): ?>
                    <rn:widget path="custom/integer/Steps" />
                    <? endif; ?>

                    <rn:condition logged_in="false">
                        <fieldset>
                            <legend>Datos de Contacto</legend>

                            <div class="form-content">

                                <div class="form-element">
                                    <rn:widget path="input/FormInput" name="Contact.Name.First" label_input="Nombre"
                                        required="true" />
                                </div>

                                <div class="form-element">
                                    <rn:widget path="input/FormInput" name="Contact.Name.Last" label_input="Apellido"
                                        required="true" />
                                </div>

                                <div class="form-element">
                                    <rn:widget path="input/FormInput" name="Contact.CustomFields.c.name_org"
                                        label_input="Nombre Empresa" required="false" />
                                </div>

                                <div class="form-element">
                                    <rn:widget path="input/FormInput" name="Contact.Emails.PRIMARY.Address"
                                        initial_focus="true" label_input="Correo Electrónico" required="true" />
                                </div>

                                <div class="form-element">
                                    <rn:widget path="input/FormInput" name="Contact.Phones.HOME.Number"
                                        label_input="Teléfono" required="true" />
                                </div>
                            </div>
                        </fieldset>
                    </rn:condition>

                    <fieldset class="rn_StepGroup rn_Step1">
                        <legend>Datos Requerimiento</legend>
                        <?if ($this->data['p_p']=='67') : ?>
                        <h1>Felicitaciones</h1>
                        <?endif;?>
                        <?if ($this->data['p_p']=='66') : ?>
                        <h1>Reclamos</h1>
                        <?endif;?>
                        <?if ($this->data['p_p']=='50') : ?>
                        <h1>Sugerencias</h1>
                        <?endif;?>

                        <div class="form-content">

                            <div class="rn_FieldDisplay rn_Output">
                                <span class="rn_DataLabel">Area de Interés</span>
                                <?  $opinion['values'][0]='Cobros y Facturación';
                                $opinion['values'][1]='Regularizacion';
                                $opinion['values'][2]='Atención Técnica';
                                $opinion['values'][3]='Entrega de Insumos';
                                $opinion['values'][4]='Instalaciones';
                                $opinion['values'][5]='Entrega de Equipos';
                                $opinion['values'][6]='Retiro de Equipos';
                                $opinion['values'][7]='Atencion Comercial';
                                $opinion['values'][8]='Atencion Local';


                                $opinion['id'][0]=0;
                                $opinion['id'][1]=1;
                                $opinion['id'][2]=2;
                                $opinion['id'][3]=3;
                                $opinion['id'][4]=3;
                                $opinion['id'][5]=3;
                                $opinion['id'][6]=3;
                                $opinion['id'][7]=3;
                                $opinion['id'][8]=3;
                                select_option($opinion, 0,'expend_type');
                            ?>

                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="rn_StepGroup rn_Step2">
                        <div class="form-content">
                            <div class="form-element">
                                <rn:widget path="custom/input/ProductCategoryInputExtended" exclude="68"
                                    name="Incident.Product" required_lvl="1" default_value="50" label_input="Tipo"
                                    hide_on_load="#rn:php:json_encode(($p_p)?FALSE:FALSE)#" />
                            </div>
                            <div class="form-element">
                                <rn:widget path="custom/input/ProductCategoryInputExtended" exclude="34"
                                    name="Incident.Category" required_lvl="1"
                                    hide_on_load="#rn:php:json_encode(($p_c)?FALSE:FALSE)#" />
                            </div>
                            <div class="form-element form-element-wide">
                                <rn:widget path="input/FormInput" name="Incident.Threads" required="true"
                                    label_input="Comentarios" />
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Archivos Adjuntos</legend>
                        <div class="form-content">
                            <div class="form-element-wide">
                                <rn:widget path="input/FileAttachmentUpload" />
                            </div>
                        </div>
                    </fieldset>

                    <? if(!$this->data['attrs']['read_only']): ?>
                    <div class="form-element-wide form-buttons">
                        <div class="rn_FormSubmit">
                            <input type="button" id="btn_get_tipo" name="btn_get_tipo" value="Siguiente Paso">
                        </div>
                    </div>
                    <? endif; ?>

                </form>
        </div>
    </div>
</div>