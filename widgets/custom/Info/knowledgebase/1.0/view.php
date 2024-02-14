<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
<!-- The Modal -->
<div id="myModal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <rn:container source_id="KFSearch" per_page="3">
	  <rn:widget path="searchsource/SourceResultListing" label_heading="Posibles Respuestas" hide_when_no_results="true" truncate_size="100" />
    <input type="button" id="btn_continuar" name="btn_continuar" value="Continuar">
  </div>

</div>
</div>