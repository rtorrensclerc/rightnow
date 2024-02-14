<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
  <div class="rn_SearchBoxInput">
    <div class="rn_KeywordText">
      <label for="txt_partCode">Número de Parte</label>
      <input name="txt_partCode" type="text" maxlength="255" value="">
    </div>
    <div class="rn_KeywordText">
      <label for="txt_delfosCode">Código Delfos</label>
      <input name="txt_delfosCode" type="text" maxlength="255" value="">
    </div>
    <? if(!$this->data['js']['onlyParts']): ?>
    <div class="rn_KeywordSelect">
      <label for="select_type">Tipo Solicitud</label>
      <select name="select_type">
        <? for ($i=0; $i < count($this->data['result']); $i++) {
          echo "<option value=\"{$this->data['result'][$i]->ID}\">{$this->data['result'][$i]->LookupName}</option>";
          echo $this->data['result'][$i]->LookupName;
        } ?>
      </select>
    </div>
  <? endif; ?>
  </div>
  <div class="rn_SearchBoxButtons">
      <input type="button" name="btn_search" value="Buscar">
      <input type="button" name="btn_clear" value="Limpiar" class="gray">
  </div>
</div>
