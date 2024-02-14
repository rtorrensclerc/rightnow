<span id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Modelo</span>

    <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['info_hh']->model_hh)? $this->data['info_hh']->model_hh: '(Sin Valor)' ?></div>
  </div>
  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Copia B/N</span>
    <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['info_hh']->cont_1)? $this->data['info_hh']->cont_1:'(Sin Valor)' ?></div>
  </div>
  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Copia Color</span>
    <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['info_hh']->cont_2)? $this->data['info_hh']->cont_2:'(Sin Valor)' ?></div>
  </div>
  <div class="rn_FieldDisplay rn_Output">
    <span class="rn_DataLabel">Cliente</span>
    <div class="rn_DataValue rn_LeftJustify"><?= ($this->data['info_hh']->org)?$this->data['info_hh']->org:'(Sin Valor)' ?></div>
  </div>
</span>
