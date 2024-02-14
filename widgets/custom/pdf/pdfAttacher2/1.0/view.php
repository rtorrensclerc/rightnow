<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">


  <!--Cuadro de Texto informativo -->
  <div class="caja">
    <?=$this->data['message'];?>
  </div>
  <div id="loading" > </div>
  <form id="rn_QuestionSubmit" action="" method="post">
     <input class="submit" name="enviar" type="submit" value="Generar PDF1">
  </form>
</div>
