<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
  <? if(array_search('JSON', $this->data['js']['valid_types']) !== false): ?><a class="btn_download btn" data-type="json" href="javascript:void 0;">Descargar JSON</a><? endif; ?>
  <? if(array_search('CSV', $this->data['js']['valid_types']) !== false): ?><a class="btn_download btn" data-type="csv" href="javascript:void 0;">Descargar CSV</a><? endif; ?>
  <? if(array_search('XLSX', $this->data['js']['valid_types']) !== false): ?><a class="btn_download btn" data-type="xlsx" href="javascript:void 0;">Descargar XLSX</a><? endif; ?>
</div>
