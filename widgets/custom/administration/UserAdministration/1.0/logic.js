RightNow.namespace('Custom.Widgets.administration.UserAdministration');

Custom.Widgets.administration.UserAdministration = RightNow.Widgets.extend({

  /**
   * Constructor
   */
  constructor: function () {

    // Desarrollo
    window.form = this;

    // Mapping de elementos del DOM
    this.widget           = this.Y.one(this.baseSelector);
    this.btn_submit       = this.widget.one("#btn_submit");
    this.btn_update       = this.widget.one("#btn_update");
    this.errors_container = this.widget.one('#rn_ErrorLocation');

    // Variables
    this.errors          = [];
    this.errors_messages = [];
    this.is_valid        = true;
    this.errors          = [];
    this.arr_files       = [];

    // Eventos
    if (this.btn_submit) this.btn_submit.on('click', this.handle_form_submit, this);
    if (this.btn_update) this.btn_update.on('click', this.handle_form_submit, this);

    // Ejecuta `init` una vez realizada la carga de los widgets de entrada
    this.loadWidgets = window.setInterval((function (_parent) {
      return function () {
        this.user_name = Integer.getInstanceByName('user_name');

        if (this.user_name) {
          _parent.init();
          window.clearInterval(_parent.loadWidgets);
        }
      };
    })(this), 100);
  },

  /**
   * Función inicial
   */
  init: function () {

    // Instancias
    this.user_name             = Integer.getInstanceByName('user_name');
    this.user_last             = Integer.getInstanceByName('user_last');
    this.user_rut              = Integer.getInstanceByName('user_rut');
    this.user_email            = Integer.getInstanceByName('user_email');
    this.user_phone            = Integer.getInstanceByName('user_phone');
    this.user_profile          = Integer.getInstanceByName('user_profile');

    // Listas
    Integer.appendOptions(this.user_profile, this.data.js.list.profiles, 'select', false, '- Seleccione -');
    if(this.data.js.user && this.data.js.user.profile) this.user_profile.input.set('value', this.data.js.user.profile);
  },

  /**
   * Manejador al enviar el formulario
   *
   * @param {event} e Evento que invóca el método
   * @return {boolean}
   */
  handle_form_submit: function (e) {
    e.preventDefault();

    if (this.btn_update) this.btn_update.set('disabled', true);
    if (this.btn_submit) this.btn_submit.set('disabled', true);

    // Validación
    if (!this.validate()) {
      return false;
    }

    // Variables
    this.errors          = [];
    this.errors_messages = [];
    this.is_valid        = true;
    this.errors          = [];
    this._data           = [];

    // Obtener datos del formulario
    RightNow.Event.fire('evt_GetData', this._data);

    // Procesa la respuesta
    this._data = this._data._data;

    // Establece el ID del contacto
    this._data.id = (RightNow.Url.getParameter('u_id'))?RightNow.Url.getParameter('u_id'):-1;

    // Determina si es borrador o no
    this._data.is_update = (e.target.get('id') == 'btn_update') ? true : false;

    // Invoca al servicio
    this.saveForm_ajax_endpoint(this._data);

    return true;
  },

  /**
   *
   */
  validate: function () {

    // Variables
    this.errors = [];
    this.errors_messages = [];
    this.is_valid = true;

    RightNow.Event.fire('evt_ValidateInput', this.errors);

    for (var error in this.errors) {
      if (!this.errors[error].valid) {
        this.errors_messages.push(this.errors[error].message);
        this.is_valid = false;
      }
    }

    if (!this.is_valid) {
      this.errors_container.one('.messages').setHTML('<p>' + this.errors_messages.join('</p><p>') + '</p>');
      this.errors_container.show();
      if (this.btn_submit) this.btn_submit.set('disabled', false);
      if (this.btn_update) this.btn_update.set('disabled', false);
      window.scrollTo(this.errors_container.getX(), this.errors_container.getY());
      return false;
    } else {
      this.errors_container.hide();
      this.errors_container.one('.messages').setHTML('');
    }

    return true;
  },

  /**
   * Endpoint para crear un contacto
   *
   * @param {event} e Evento que invóca el método
   * @return {boolean}
   */
  saveForm_ajax_endpoint: function (a_post_values) {
    var eventObj = new RightNow.Event.EventObject(this, {
      data: {
        w_id: this.data.info.w_id,
        data: JSON.stringify(a_post_values)
      }
    });
    RightNow.Ajax.makeRequest(this.data.attrs.saveForm_ajax_endpoint, eventObj.data, {
      successHandler: this.saveForm_ajax_endpointCallback,
      scope: this,
      data: eventObj,
      json: true
    });
  },

  /**
   * Manejador del la respuesta del endpoint #saveForm_ajax_endpoint.
   *
   * @param {object} response respuesta JSON del servidor
   * @param {object} originalEventObj objeto principal del método #getDefault_ajax_endpoint
   */
  saveForm_ajax_endpointCallback: function (response, saveForm_ajax_endpoint) {
    if (response.success) {
      RightNow.UI.displayBanner(response.message);

      window.location.href = '/app/sv/users/user_management';
    } else {
      RightNow.UI.Dialog.messageDialog(response.message, {
        icon: 'WARN'
      });

      if (this.btn_update) this.btn_update.set('disabled', false);
      if (this.btn_submit) this.btn_submit.set('disabled', false);
    }
  }
});
