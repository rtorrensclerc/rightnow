<!-- <h2>{DEVELOPMENT}</h2> -->
<?
if(getUrlParm('u_id') && !getUrlParm('dev'))
{
  exit('Error al cargar el widget.');
}
?>

<div id="rn_<?= $this->instanceID ?>" class="<?= $this->classList ?>">
  <div class="rn_GroupFields rn_GroupInline">
    <? if($this->data['js']['is_custom']): ?>
      <input id="btn_save" class="gradient" type="button" value="Guardar">
    <? else: ?>
      <rn:widget path="custom/input/SelectField" name="profile_type" id="profile_type" label_input="Perfil" required="true" value="#rn:php:$this->data['js']['list']['profile_type']#" />
    <? endif; ?>
  </div>
  
  <div class="rn_Grid">
    <table class="yui3-datatable-table rn_Profiling">
      <thead>
        <tr>
          <th class="rn_TextRight">#</th>
          <th data-key="module_name" class="rn_TextLeft">Módulos</th>
          <th data-key="profiling_access" class="rn_TextCenter">Acceso</th>
        </tr>
      </thead>
      <tbody>
        <tr class="template" style="display:none;">
          <td data-key="" data-format="index" class="rn_TextRight">0</td>
          <td data-key="module_name" data-format="text" class="rn_TextLeft">-</td>
          <? if($this->data['js']['is_custom']): ?>
            <td data-key="profiling_access" data-format="node" class="rn_TextCenter">
            </td>
          <? else: ?>
          <td data-key="profiling_access" data-format="text" class="rn_TextCenter">-</td>
          <? endif; ?>
        </tr>
        <tr class="no_data">
          <td data-key="_" colspan="3" class="rn_TextCenter">( Sin Perfil - Seleccione )</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
