RightNow.namespace('Custom.Widgets.contact.AdvancedProfiling');

Custom.Widgets.contact.AdvancedProfiling = RightNow.Widgets.extend({

  /**
   * Constructor
   */
  constructor: function () {

    // Desarrollo
    window.profiling = this;

    // Mapping de elementos del DOM
    this.widget                = this.Y.one(this.baseSelector);
    this.errors_container      = this.widget.one('#rn_ErrorLocation');
    this.table_Profiling       = this.widget.one('.rn_Grid .rn_Profiling');
    this.table_Profiling_tbody = this.table_Profiling.one('tbody');
    this.btn_save              = this.Y.one('#btn_save');

    // Variables
    this.errors          = [];
    this.errors_messages = [];
    this.errors          = [];
    this.c_id            = (RightNow.Url.getParameter('c_id')) ? RightNow.Url.getParameter('c_id') : -1;
    this.c_type          = (RightNow.Url.getParameter('c_type')) ? RightNow.Url.getParameter('c_type') : -1;

    if(!this.data.js.is_custom) {
      // Ejecuta el método `init` una vez realizada la carga de los widgets de entrada
      this.loadWidgets = window.setInterval((function (_parent) {
        return function () {
          this.profile_type = Integer.getInstanceByName('profile_type');

          if (this.profile_type) {
            _parent.init();
            window.clearInterval(_parent.loadWidgets);
          }
        };
      })(this), 100);
    } else {
      this.init();
    }
  },

  /**
   * Función inicial
   */
  init: function () {

    // Instancias
    this.profile_type  = Integer.getInstanceByName('profile_type');
    this.arr_profiling = '';

    // Eventos
    if (this.profile_type) this.profile_type.input.on('change', this.handle_profile_change, this);
    if (this.btn_save) this.btn_save.on('click', this.handle_profile_change, this);

    // Lista de tipo de perfilaminto
    if (this.profile_type) Integer.appendOptions(this.profile_type, this.data.js.list.profiles, 'select', false, '- Seleccione -');
    if (this.data.js.profile_type && this.profile_type) this.profile_type.input.set('value', this.data.js.profile_type.toString());

    var result = [];
    var json   = this.data.js.profiling;

    if(this.data.js.profiling) {
      for (var i = 0; i < this.data.js.profiling.modules.length; i++) {
        var obj                  = {};
            obj.profiling_id     = this.data.js.profiling.modules[i].id,
            obj.module_id        = this.data.js.profiling.modules[i].module.id,
            obj.module_name      = this.data.js.profiling.modules[i].module.name,
            obj.profiling_access = ((this.data.js.profiling.modules[i].access)?'Sí':'No')
  
        if(this.data.js.is_custom) {
          selected             = '';
          obj.profiling_access = '<select>';

          if(this.data.js.profiling.modules[i].access) {
            selected = ' selected="selected"';
          }
          obj.profiling_access += '<option' + selected + ' value="' + obj.profiling_id + '_1">Sí</option>';

          selected = '';
          if(!this.data.js.profiling.modules[i].access) {
            selected = ' selected="selected"';
          }

          obj.profiling_access += '<option' + selected + ' value="' + obj.profiling_id + '_0">No</option>';
          obj.profiling_access += '</select>';
        }
  
        result.push(obj);
      }
    }

    Integer.fillTable(result, this.table_Profiling_tbody);

    this.select_access = this.Y.all('table select');
    this.select_access.on('change', this.select_access_change, this);
  },

  /**
   * Manejador del cambio de acceso
   * 
   * @param {event} e Evento que invóca el método
   */
  select_access_change: function(e) {
    // Declaración de variables
    var input           = e.target,                // Campo select
        row             = input.ancestor('tr'),    // Fila seleccionada
        index           = row.get('rowIndex'),     // Índice de fila seleccionada
        count_base_rows = (index - 2),             // Cantidad de filas previas al registro
        profiling_index = (count_base_rows - 1),   // Cantidad de filas previas al registro
        value           = input.get('value'),      // Valor de selector
        arr_value       = [],                      // Arreglo de valores de perfilamiento
        profile_id      = null,                    // ID de perfilamiento
        access          = null;                    // Acceso de perfilamiento
    
    // Asignación de variables
    arr_value  = value.split('_');
    profile_id = (arr_value[0].toLowerCase() === 'null')?null:parseInt(arr_value[0]);
    access     = (arr_value[1] === '1')?true:false;

    // Actualiza el objeto `profiling`
    this.data.js.profiling.modules[profiling_index].id     = profile_id;
    this.data.js.profiling.modules[profiling_index].access = access;

  },

  /**
   * Manejador al enviar el formulario
   *
   * @param {event} e Evento que invóca el método
   * @return {boolean}
   */
  handle_profile_change: function (e) {
    e.preventDefault();

    // Validación
    if (!this.validate()) {
      return false;
    }

    // Variables
    this.errors          = [];
    this.errors_messages = [];
    this.errors          = [];
    this._data           = {};

    // Establece el ID del contacto
    this._data.c_id   = this.c_id;
    this._data.c_type = this.c_type;
    if(this.profile_type) this._data.profile_type = this.profile_type.input.get('value');
    
    if(this.data.js.is_custom) {
      this._data.json_custom_profile = this.data.js.profiling;
    }
 
    // Invoca al servicio
    this.updateProfiling_ajax_endpoint(this._data);
    return true;
  },

  /**
   * Valida el formulario
   */
  validate: function () {

    // Variables
    this.errors = [];
    this.errors_messages = [];

    // Evento de validación
    RightNow.Event.fire('evt_ValidateInput', this.errors);

    // Recorre los posibles errores
    for (var error in this.errors) {
      if (!this.errors[error].valid) {
        this.errors_messages.push(this.errors[error].message);
      }
    }

    // Presenta mensajes si contiene errores
    // if (!this.errors_messages.length) {
    //   this.errors_container.one('.messages').setHTML('<p>' + this.errors_messages.join('</p><p>') + '</p>');
    //   this.errors_container.show();
    //   window.scrollTo(this.errors_container.getX(), this.errors_container.getY());
    //   return false;
    // } else {
    //   this.errors_container.hide();
    //   this.errors_container.one('.messages').setHTML('');
    // }

    return true;
  },

  /**
   * Endpoint para actualizar el perfilamiento de un contacto
   *
   * @param {event} e Evento que invóca el método
   * @return {boolean}
   */
  updateProfiling_ajax_endpoint: function (a_post_values) {
    var eventObj = new RightNow.Event.EventObject(this, {
      data: {
        w_id: this.data.info.w_id,
        data: JSON.stringify(a_post_values)
      }
    });
    RightNow.Ajax.makeRequest(this.data.attrs.updateProfiling_ajax_endpoint, eventObj.data, {
      successHandler: this.updateProfiling_ajax_endpointCallback,
      scope         : this,
      data          : eventObj,
      json          : true
    });
  },

  /**
   * Manejador del la respuesta del endpoint #updateProfiling_ajax_endpoint.
   *
   * @param {object} response respuesta JSON del servidor
   * @param {object} originalEventObj objeto principal del método #getDefault_ajax_endpoint
   */
  updateProfiling_ajax_endpointCallback: function (response, originalEventObj) {
        
    if (response.success) {
      // RightNow.UI.displayBanner(response.message);

      window.location.reload();
    } else {
      RightNow.UI.Dialog.messageDialog(response.message, {
        icon: 'WARN'
      });
    }
  }
});