<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
  <h2>Detalle [<?=$this->data['order']->Subject?>]   </h2>
  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Estado</span>
    <div class="rn_DataValue rn_LeftJustify">
      <select type="select" id="select_status" >
        <option value="<?=$this->data['order']->StatusWithType->Status->ID?>" selected><?= ($this->data['order']->StatusWithType->Status->LookupName)?$this->data['order']->StatusWithType->Status->LookupName:'(Sin Valor)' ?></option>
        <?switch ($this->data['order']->StatusWithType->Status->ID) {
          case 120:
            echo '<option value="112">Despacho Entregado</option>';
            echo '<option value="142">Despacho no Entregado</option>';

          break;
          case 140:
            echo '<option value="112">Despacho Entregado</option>';
            echo '<option value="142">Despacho no Entregado</option>';

          break;
          case 111: /*por despachar */
            echo '<option value="112">Despacho Entregado</option>';
            echo '<option value="142">Despacho no Entregado</option>';

          break;
        }
       ?>
      </select>
    </div>
  </div>
  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Cliente</span>
    <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['order']->CustomFields->DOS->Direccion->Organization->LookupName)?$this->data['order']->CustomFields->DOS->Direccion->Organization->LookupName:'(Sin Valor)' ?></div>
  </div>
  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">RUT Cliente</span>
    <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['order']->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut)?$this->data['order']->CustomFields->DOS->Direccion->Organization->CustomFields->c->rut:'(Sin Valor)' ?></div>
  </div>
  <div class="rn_FieldDisplay rn_Output fullWidth">
    <span class="rn_DataLabel">Dirección Despacho</span>
    <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['order']->CustomFields->DOS->Direccion->dir_envio)?$this->data['order']->CustomFields->DOS->Direccion->dir_envio:'(Sin Valor)' ?></div>
  </div>

  <div class="rn_FieldDisplay rn_Output fullWidth">
    <span class="rn_DataLabel">Contacto</span>
    <div class="rn_DataValue rn_LeftJustify"><?=$this->data['order']->PrimaryContact->LookupName?><br><?=$this->data['order']->PrimaryContact->Emails[0]->Address?><br><?=$this->data['order']->PrimaryContact->Phones[0]->Number?></div>
  </div>
  <div class="rn_FieldDisplay rn_Output fullWidth">
    <span class="rn_DataLabel">Instrucciones de Envio</span>
    <div class="rn_DataValue rn_LeftJustify"><?=$this->data['order']->CustomFields->c->shipping_instructions?></div>
  </div>
  <div class="rn_FieldDisplayFull rn_Output">
  <span class="rn_Label">Notas</span><br>
  <textarea name="name" id="nota" rows="8" cols="80"></textarea>
  <br>
  

  </div>

  <div class="rn_FieldDisplay rn_Output">
    <div class="rn_DataValue rn_LeftJustify">
      <form enctype="multipart/form-data" method="post" id="fileinfo" name="fileinfo" >
          <label>Seleccionar Archivo:</label><br>
          <input type="file" name="file" required id="file"/>
      </form>
    </div>
  </div>
  <?if($this->data['order']->FileAttachments ){?>
  <div class="rn_FieldDisplay rn_Output">
    <h2>Archivos Adjuntos:   </h2>
  <?
  $CI = get_instance();
  $username = $CI->session->getSessionData("username");
  $password = $CI->session->getSessionData("password");

  initConnectAPI($username,$password);
  if($this->data['order']->FileAttachments)
  {
  foreach ($this->data['order']->FileAttachments as $file) {
  ?>
       -- <a href="<?=$file->getAdminURL()?>" ><?=$file->FileName?></a><br>
  <?
  }
  }
  ?>
  </div>
  <?}?>
  <div align= "center" id="myProgress" class="myProgress">
    <div id="myBar" class="myBar">
      <div id="outter_1" class="outter"></div>
    </div>
  </div>

  <tfoot>
          <input type="button" name="btn_request" value="Guardar">
          <input type="button" name="btn_cancel" value="Cancelar" >
          <input type="button" value="Upload" name="btn_upload" >
  </tfoot>
</div>
