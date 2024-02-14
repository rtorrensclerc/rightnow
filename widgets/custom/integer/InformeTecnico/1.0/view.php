<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
  <h2>Detalle [<?=$this->data['order']->Subject?>]   </h2>
  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Estado</span>
    <div class="rn_DataValue rn_LeftJustify">

      <select type="select" id="select_status" >

        <option value="<?=$this->data['order']->StatusWithType->Status->ID?>" selected><?= ($this->data['order']->StatusWithType->Status->LookupName)?$this->data['order']->StatusWithType->Status->LookupName:'(Sin Valor)' ?></option>
        <?
            echo '<option value="189">Informe enviado</option>';

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



  <tfoot>
          <input type="button" name="btn_request" value="Guardar">
          <input type="button" name="btn_cancel" value="Cancelar" >

  </tfoot>
</div>
