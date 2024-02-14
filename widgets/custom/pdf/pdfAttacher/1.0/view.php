<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
  <form id="rn_QuestionSubmit"  >
      <?php $op_id = $_GET['op'] ?>

      <rn:widget path="input/FormSubmit" label_button="Generar PDF" target="app/pdf/pdf/?op=<?= $op_id ?>&cr=1" />
  </form>
</div>
