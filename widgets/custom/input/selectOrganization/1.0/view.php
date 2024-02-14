<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">

	<div class="title">
		Busque y seleccione su organización
	</div>

	<div>
		<label class="rn_Label">
		Buscador
		<span class="rn_Required"> *</span><span class="rn_ScreenReaderOnly"> Necesario</span>
		</label>
	</div>

	<input type="text" id="searchOrg" placeholder="Nombre Empresa" />

	<div class="containerBoxList" >
	   <ul class= "orgRow">
	  </ul>
	</div>

	<div>
		<label class="rn_Label">
		Organización
		<span class="rn_Required"> *</span><span class="rn_ScreenReaderOnly"> Necesario</span>
		</label>
	</div>

	<input type="text" id="orgName" readonly><br>

	<div class="hide">
	  <rn:widget path="input/TextInput" name="#rn:php:$this->data['attrs']['name']#" label_input="#rn:php:$this->data['attrs']['label_input']#" />
	</div>

</div>
