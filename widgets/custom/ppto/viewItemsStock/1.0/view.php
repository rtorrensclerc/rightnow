<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
  <!--Cuadro de Texto informativo -->
  <div class="caja">
    <?=$this->data['message'];?>
    <?php
      $a_result = $this->data["a_viewStock"];

      if (is_array($a_result) and (count($a_result)>0)):
      ?>
      <table style="width:100%">
      <tr>
        <th>Nombre</th>
        <th>Código Delfos</th>
        <th>Stock</th>
      </tr>
      <? foreach ($a_result as $key => $item): ?>
          <tr>
            <td><?=$item['name']?></td>
            <td><?=$item['inventoryItemId']?></td>
            <td><?=$item['stock']?></td>
          </tr>
      <? endforeach; ?>
      </table>
      <?endif;?>

  </div>
  <div id="loading" > </div>
  <form action="" method="post">
     <input class="submit" name="enviar" type="submit" value="Ver Stock">
  </form>
</div>
