<?

$ruts=$this->data['js']['datos'];

?>

<div id="rn_<?=$this->instanceID?>" class="<?=$this->classList?> form-request">
 <div class="rn_Padding">
 <rn:container>
		<div class="content-title wrapper">
		  	<h1>Búsqueda de Información de sus Equipos</h1>
			  <form id="form_supplier" method="post">
          <fieldset class="rn_StepGroup rn_Step1">
		        <div class="form-content">
            <h3>Estimado clientes:<br>
Actualmente esta mirando los datos de : <br>           
<span  class="rn_Label"></span><?=$this->data['js']['arut'] . '<br>' .$this->data['js']['aName'] .'<br><br> '?><br>
Busque datos de su HH en toda su organización. <br>
</h3>

                <div class="rn_FieldDisplay rn_Output">
                
                    <span class="rn_DataLabel">Número HH</span>
                    <input id="valor_hh"/><br>
                    <span class="rn_DataLabel">Número de Serie</span>
                    <input id="valor_serie"/>
                </div>
            </div>
          </fieldset>
          <div class="form-buttons">
		          <div class="rn_FormSubmit">
                <div class="rn_FieldDisplay rn_Output">
					        <input type="button" id="btn_search" name="btn_search" value="Buscar Equipo" />
                </div>
					  </div>
        </div>
        </form> 
        
        <br>
        <div id="customer_info" hidden="hidden"  style="">
                
           <legend>Información de HH</legend>
                <rn:widget 	path="custom/input/InputField"  		id="contact_comment1"	name="hh_selected1" 	label_input="Nombre de Empresa"		value="#rn:php:$this->data['contact_comment']#" display_type="text" disabled="true" />
                <rn:widget 	path="custom/input/InputField"  		id="contact_comment2"	name="hh_selected2" 	label_input="RUT de Empresa"		value="#rn:php:$this->data['contact_comment']#" display_type="text" disabled="true" />
                <rn:widget 	path="custom/input/InputField"  		id="contact_comment2"	name="hh_selected3" 	label_input="Dirección"		value="#rn:php:$this->data['contact_comment']#" display_type="text" disabled="true" />
                <rn:widget 	path="custom/input/InputField"  		id="contact_comment2"	name="hh_selected4" 	label_input="Comuna"		value="#rn:php:$this->data['contact_comment']#" display_type="text" disabled="true" />
                <rn:widget 	path="custom/input/InputField"  		id="contact_comment2"	name="hh_selected5" 	label_input="Región"		value="#rn:php:$this->data['contact_comment']#" display_type="text" disabled="true" />
                <rn:widget 	path="custom/input/InputField"  		id="contact_comment2"	name="hh_selected6" 	label_input="Descripción"		value="#rn:php:$this->data['contact_comment']#" display_type="text" disabled="true" />        
                <rn:widget 	path="custom/input/InputField"  		id="contact_comment2"	name="hh_selected7" 	label_input="Serie"		value="#rn:php:$this->data['contact_comment']#" display_type="text" disabled="true" />
                <rn:widget 	path="custom/input/InputField"  		id="contact_comment2"	name="hh_selected8" 	label_input="Rut Actual"		value="#rn:php:$this->data['contact_comment']#" display_type="text" disabled="true" />
                <input type="text" id="txt_new_rut" name="txt_new_rut" value="" >

                <div class="rn_FieldDisplay rn_Output">
                <div id="customer_info3" hidden="hidden"  style="">
                <h3>este HH no esta en la Empresa Actual <?=$this->data['js']['org_rut']?>. ¿quiere cambiar la empresa de su perfil?</h3><br>
					        <input type="button" id="btn_change" name="btn_change" value="Cambiar a :" />
                </div>
        
            </div>
        </div>
        </div>
        <div id="customer_info2" hidden="hidden"  style="">

          <legend>No Existe Informacion de este HH en su Organización</legend>
          
            
        </div>
 
        
      </div>
    </div>
    </rn:container>
	</div>
</div>

