<div id="rn_<?=$this->instanceID?>" class="<?=$this->classList?> form-request">

  <div class="rn_ScreenForm">
    <div class="wrapper">
      <? if(!$this->data['attrs']['read_only']): ?>
        <h1>Solicitud de Insumos Múltiples</h1>
        <div class="message">
        #rn:msg:CUSTOM_MSG_COVID_WARNING#
          <p><br>Solicite insumos para su compañía de forma masiva. Descargue y cargue el CSV con sus solicitudes de insumos, seleccione las direcciones asociadas a cada HH, espere la validación del sistema y corrija de ser necesario.</p>
        </div>
        <? else: ?>
        <h1>Solicitud de Insumos Múltiples</h1>
        <? endif; ?>

      <div id="rn_ErrorLocation" class="rn_MessageBox rn_ErrorMessage" hidden="hidden" style="display: none;">
        <h2 role="alert">#rn:msg:CUSTOM_MSG_ERROR#</h2>
        <div class="messages">
        </div>
      </div>

      <!-- Inicio del formulario -->
      <fieldset>
        <div class="form-content">
          <!--p><a href="/euf/assets/files/Plantilla_Excel.xlsx" target="_blank" download="Plantilla_Excel.xlsx">Descargar Plantilla Excel</a></p-->
          <p><a href="/euf/assets/files/Plantilla_CSV.csv" target="_blank" download="Plantilla_CSV.csv">Descargar Plantilla CSV</a></p>
          <div class="upload_box">
            <h3>Cargue archivo CSV de solicitudes insumos</h3>
            <input type="file" name="" id="">
            <br>
            <a href="#" id="btn_upload_csv" class="btn">Subir Archivo</a>
            
          </div>
        </div>
      </fieldset>


 <!-- Inicio del formulario -->



      <fieldset>
        <div class="form-content">
          <h2>Ítems con errores</h2>
          <div class="rn_Group rn_Info">
          Para continuar con la solicitud, descarte las HH con errores presionando el botón eliminar, o si es de su preferencia corrija los errores en el CSV y vuelva a cargarlo. Una vez que el sistema no detecte ningún error, podrá proceder a generar las solicitudes presionando el botón solicitar
          </div>
          <div class="rn_Table">
            <table id="errors_supplierMultipleItems">
              <thead>
                <tr>
                  <th class="rn_TextRight">#</th>
                  <th class="rn_TextLeft">HH Equipo</th>
                  <th class="rn_TextRight">Contador 1 B/N</th>
                  <th class="rn_TextRight">Contador 2 Color</th>
                  <th class="rn_TextCenter">Toner Negro</th>
                  <th class="rn_TextCenter">Toner Cyan</th>
                  <th class="rn_TextCenter">Toner Magenta</th>
                  <th class="rn_TextCenter">Toner Amarillo</th>
                  <th class="rn_TextLeft">Errores</th>
                  <th class="rn_TextCenter">Acción</th>
                </tr>
              </thead>
              <tbody>
                <? if(!$this->data['attrs']['read_only']): ?>
                <tr class="template" style="display: none;" hidden="hidden">
                  <td data-format="index" class="rn_TextRight">---</td>
                  <td data-key="hh" class="rn_TextLeft">---</td>
                  <td data-format="number" data-key="counter_black" class="rn_TextRight">---</td>
                  <td data-format="number" data-key="counter_color" class="rn_TextRight">---</td>
                  <td data-format="number" data-key="count_black" class="rn_TextCenter">---</td>
                  <td data-format="number" data-key="count_cyan" class="rn_TextCenter">---</td>
                  <td data-format="number" data-key="count_magenta" class="rn_TextCenter">---</td>
                  <td data-format="number" data-key="count_yellow" class="rn_TextCenter">---</td>
                  <td data-format="list" data-key="errors" class="rn_TextLeft">---</td>
                  <td data-format="delete_row" class="rn_TextCenter">---</td>
                </tr>
                <tr class="no_data">
                  <td colspan="10" class="rn_TextCenter">
                    No hay solicitudes con errores
                  </td>
                </tr>
                <? else: ?>
                <? $error_index = 0; ?>
                <? foreach($this->data['arr_errors'] as $error_value): ?>
                <? $error_index++; ?>
                <tr class="templateRow" style="display: none;" hidden="hidden">
                  <td class="rn_TextRight">
                    <?= $error_index ?>
                  </td>
                  <td class="rn_TextLeft">
                    <?= $error_value['hh'] ?>
                  </td>
                  <td class="rn_TextCenter">
                    <?= $error_value['counter_black'] ?>
                  </td>
                  <td class="rn_TextCenter">
                    <?= $error_value['counter_color'] ?>
                  </td>
                  <td class="rn_TextCenter">
                    <?= $error_value['count_black'] ?>
                  </td>
                  <td class="rn_TextCenter">
                    <?= $error_value['count_color'] ?>
                  </td>
                  <td class="rn_TextLeft">
                    <?= $error_value['message'] ?>
                  </td>
                  <td class="rn_TextCenter"><a href="#">Eliminar</a></td>
                </tr>
                <? endforeach; ?>
                <? endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </fieldset>

      <fieldset>
        <div class="form-content">
          <h2>Ítems sin errores</h2>
          <div class="rn_Table">
            <table id="items_supplierMultipleItems">
              <thead>
                <tr>
                  <th class="rn_TextRight">#</th>
                  <th class="rn_TextLeft">HH Equipo</th>
                  <th class="rn_TextCenter">Contador 1 B/N</th>
                  <th class="rn_TextCenter">Contador 2 Color</th>
                  <th class="rn_TextCenter">Toner Negro</th>
                  <th class="rn_TextCenter">Toner Cyan</th>
                  <th class="rn_TextCenter">Toner Magenta</th>
                  <th class="rn_TextCenter">Toner Amarillo</th>
                  <th class="rn_TextLeft">Nombre Contacto</th>
                  <th class="rn_TextLeft">Dirección</th>
                  <th class="rn_TextCenter">Acción</th>
                </tr>
              </thead>
              <tbody>
                <? if(!$this->data['attrs']['read_only']): ?>
                <tr class="template" style="display: none;" hidden="hidden">
                  <td data-format="index" class="rn_TextRight">---</td>
                  <td data-key="hh" class="rn_TextLeft">---</td>
                  <td data-format="number" data-key="counter_black" class="rn_TextCenter">---</td>
                  <td data-format="number" data-key="counter_color" class="rn_TextCenter">---</td>
                  <td data-format="number" data-key="count_black" class="rn_TextCenter">---</td>
                  <td data-format="number" data-key="count_cyan" class="rn_TextCenter">---</td>
                  <td data-format="number" data-key="count_magenta" class="rn_TextCenter">---</td>
                  <td data-format="number" data-key="count_yellow" class="rn_TextCenter">---</td>
                  <td data-format="text" data-key="contact_name" class="rn_TextLeft">---</td>
                  <td data-format="select" data-key="id_dir_selected" class="rn_TextLeft">---</td>
                  <td data-format="delete_row" class="rn_TextCenter">---</td>
                </tr>
                <tr class="no_data">
                  <td colspan="11" class="rn_TextCenter">
                    No hay solicitudes sin errores.
                  </td>
                </tr>
                <? else: ?>
                <? $valid_index = 0; ?>
                <? foreach($this->data['arr_errors'] as $valid_value): ?>
                <? $valid_index++; ?>
                <tr class="template" style="display: none;" hidden="hidden">
                  <td class="rn_TextRight">
                    <?= $valid_index ?>
                  </td>
                  <td class="rn_TextLeft">
                    <?= $error_value['hh'] ?>
                  </td>
                  <td class="rn_TextRight">
                    <?= $valid_value['count_black'] ?>
                  </td>
                  <td class="rn_TextRight">
                    <?= $valid_value['count_cyan'] ?>
                  </td>
                  <td class="rn_TextRight">
                    <?= $valid_value['count_magenta'] ?>
                  </td>
                  <td class="rn_TextLeft">
                    <?= $valid_value['count_yellow'] ?>
                  </td>
                  <td class="rn_TextLeft">---</td>
                  <td class="rn_TextCenter">---</td>
                </tr>
                <? endforeach; ?>
                <? endif; ?>
              </tbody>
            </table>
          </div>

        </div>
      </fieldset>

      <?if (!isset($this->data['attrs']['read_only']) || !$this->data['attrs']['read_only']): ?>
      <div class="form-buttons">
        <div class="rn_FormSubmit">
          <input type="button" id="btn_submit" name="btn_submit" value="Solicitar">

        </div>
      </div>
      <?endif;?>

    </div>
  </div>
  <div class="rn_ScreenSuccess" style="display: none;" hidden="hidden">

    <div class="content-title wrapper">
      <h1>#rn:msg:CUSTOM_MSG_INPUT_REQUEST#</h1>
      <p>#rn:msg:CUSTOM_MSG_REQUEST_LAST_STEP#</p>
    </div>

    <div class="rn_SuccessMesage">
      <div class="message">
        <p>#rn:msg:CUSTOM_MSG_SUCCESS_EMAIL#</p>
      </div>
      <div class="icon"></div>
    </div>

    <div class="form-buttons">
      <div class="rn_FormSubmit">
        <input type="button" id="btn_requests" name="btn_requests" value="Ver Mis Solicitudes">
        <input type="button" id="btn_new_request" name="btn_new_request" value="Realizar Otra Solicitud">
      </div>
    </div>

  </div>
  <div class="rn_ContentTab rn_ContentTab_Loading" style="display:none;">
            <rn:widget path="custom/Info/waiting" />
          
   </div>
</div>
