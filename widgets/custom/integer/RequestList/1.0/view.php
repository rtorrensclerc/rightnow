<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?> rn_Grid">

  <table cellspacing="0">
      <thead>
          <tr>
              <th colspan="1" rowspan="1">
                  Número de Parte
              </th>
              <th colspan="1" rowspan="1">
                  Nombre
              </th>
              <th colspan="1" rowspan="1">
                  Cantidad
              </th>
              <th colspan="1" rowspan="1">
                  Eliminar
              </th>
          </tr>
      </thead>
      <tbody>
        <? for ($i=0; $i < count($this->data['js']['order_detail']['list_items']); $i++): ?>
          <tr class="partRow">
              <td data-title="Número de Parte">
                <input type="hidden" name="idPart" value="<?= $this->data['js']['order_detail']['list_items'][$i]['id'] ?>" />
                <?= $this->data['js']['order_detail']['list_items'][$i]['partNumber'] ?>
              </td>
              <td data-title="Nombre">
                <?= $this->data['js']['order_detail']['list_items'][$i]['name'] ?>
              </td>
              <td data-title="Cantidad">
                <div class="wrap rn_Controls">
                  <button type="button" value="-1" class="btn rn_ControlButton rn_DecreaseButton">
                    <span class="ico_decrease rn_Assets">

                    </span>
                  </button>
                  <span class="rn_Quantity">
                    <?= $this->data['js']['order_detail']['list_items'][$i]['quantity'] ?>
                  </span>
                  <button type="button" value="1" class="btn rn_ControlButton rn_IncreaseButton">
                    <span class="ico_increase rn_Assets">

                    </span>
                  </button>
                </div>
              </td>
              <td data-title="Eliminar">
                  <div class="wrap">
                    <button type="button" value="false" class="btn btn-red rn_ItemDelete">
                      <span class="ico_delete rn_Assets">

                      </span>
                    </button>
                  </div>
              </td>
          </tr>
        <? endfor; ?>
      </tbody>
      <tfoot>
          <tr>
              <th colspan="4" rowspan="1">
                <? if(!$this->data['js']['order_detail']['ref_no']): ?>
                <input type="button" name="btn_create" value="Crear Borrador">
                <? else: ?>
                <input type="button" name="btn_request" value="Solicitar">
                <input type="button" name="btn_draft" value="Guardar Borrador">
                <? endif; ?>
                <input type="button" name="btn_cancel" value="Cancelar" class="gray">
                <input type="button" name="btn_newPart" value="Nuevo Repuesto">
              </th>
          </tr>
      </tfoot>
  </table>

  <? echo $this->data['js']['order_detail']['ref_no']; ?>
</div>
