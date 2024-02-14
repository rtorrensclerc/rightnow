RightNow.namespace('Custom.Widgets.input.SelectField');

Custom.Widgets.input.SelectField = RightNow.Widgets.extend({
  /**
   * Constructor
   */
  constructor: function() {
    if(this.data.attrs.read_only){
      return false;
    }
    this.widget         = this.Y.one(this.baseSelector);
    this.input          = this.widget.one('select');
    this.input_selected = this.widget.one('.rn_SelectedValue');
    this.input_search   = this.widget.one('input');

    if (this.input_search) {
      this.resetOptionsList();
      this.input_search.on('keyup', this.searchOptions, this);
    }

    if (this.data.attrs.show_group) {
      this.input.on('change', this.show_group, this);
    }

    if (this.data.attrs.filter) {
      if(this.input_selected) this.input_selected.one('a').on('click', function(e) {
        if (e.target.getAttribute('multiple') === '' && !this.recentlyChanged) {
          if(this.data.attrs.colapsible) {
            this.input_search.show();
            this.input_search.focus();
            this.input.show();
            this.input_selected.hide();
          }

          return false;
        }
      }, this);

      this.input.on('click', function(e) {
        var element = (e.target.get('tagName') === 'OPTION')?e.target.ancestor('select'):e.target;
        if (element.getAttribute('multiple') !== '') {
          if(this.data.attrs.colapsible){
            this.input_search.hide();
            this.input.hide();
            if(this.input_selected && element.get('selectedIndex') !== -1) this.input_selected.one('a').setHTML(element.get('options').item(element.get('selectedIndex')).getHTML());
            if(this.input_selected) this.input_selected.show();
          }
        }
      }, this);
    }
    RightNow.Event.subscribe('evt_ValidateInput', this.validate, this);
    RightNow.Event.subscribe('evt_GetSelectedItem', this.getSelectedItem, this);
    RightNow.Event.subscribe('evt_GetInstanceByInputName', this.getInstanceByInputName, this);
    RightNow.Event.subscribe('evt_GetValues', this.getValues, this);
    RightNow.Event.subscribe('evt_GetData', this.getData, this);
  },

  /**
  * Complementa el array de valores en notación key - value
  */
  getData: function(e, arr){
    var name = this.input.get('name');
    var value = this.input.get('value');

    // arr[0].push({'key':name, 'value':value});
    arr[0]._data = arr[0]._data || {};
    arr[0]._data[name] = value;

    return arr;
  },

  /**
  * Obtiene datos del select
  */
  get: function(type){
    var value = null;

    var selectedIndex = this.input.get('selectedIndex')
    var selectedOption = this.input.get('options').item(selectedIndex);

    if(type === 'name') return this.input.get('name');
    if(type === 'value') return this.input.get('value');
    if(type === 'text') {
      value = selectedOption.getContent();
    }

    return value;
  },

  /**
  * Reestablece los valores del select
  */
  reset: function() {
    // this.input.get('options').remove();
    // this.input.set('disabled', true);

    this.input.set('value', '');

    if(this.data.attrs.colapsible) {
      this.input_search.show();
      this.input_search.focus();
      this.input.show();
      this.input_selected.hide();
    }

    return true;
  },

  /**
  * Complementa el array de valores
  */
  getValues: function(e, arr){
    arr[0].push(this.input.get('value'));

    return arr;
  },

  show_group: function(e) {
    var value = this.input.get('value');

    if(this.data.attrs.show_group_value){
      if (value === this.data.attrs.show_group_value) {
        this.Y.all('.' + this.data.attrs.show_group).show();
      } else {
        this.Y.all('.' + this.data.attrs.show_group).hide();
      }
    }

    return false;
  },

  /**
  * Retorna la instancia según el nombre el campo
  *
  * @param e {event} evento que invóca al método
  * @param param {array} arr array de parámetros
  * @param {object}
  */
  getInstanceByInputName: function(e, param) {
    var instance = this;

    if(this.input && (this.input.get('name') === param[1] || this.input.get('id') === param[1])) {
      param[0].push(instance);
    }

    return instance;
  },

  resetOptionsList: function() {
    this.optionsCC = [];

    for (var i = 0; i < this.input._node.options.length; i++) {
      this.optionsCC.push({name: this.input.get('options').item(i).get('text'), value: this.input.get('options').item(i).get('value')});
    }
  },

  setOptions: function(options) {
    return true;
  },

  /**
  * Selecciona un ítem por su valor
  *
  * @param value {string}
  * @return {boolean}
  */
  setSelectItemFromValue: function(value) {
    var values = this.input.get('options').get('value');

    if(values.indexOf(value) !== -1){
      this.input.set('value', value);
      return true;
    }

    return false;
  },

  /**
  * Limpia los ítems del select
  *
  * @return {boolean}
  */
  clearOptions: function() {
    this.input._node.input.options.length = 0;

    return true;
  },

  /**
  * Hace visible todos los ítems del select
  *
  * @return {boolean}
  */
  showAllOptions: function() {
    var options = this.input._node.options;
    this.input_search._node.value = '';

    if (options.length) {
      for (var i = 0; i < options.length; i++) {
        options[i].style.display = '';
      }
    }

    return true;
  },

  /**
  * Buscar y filtra elementos mediante su visibilidad
  *
  * @param {event} e Evento del invocador
  * @return {boolean}
  */
  searchOptions: function(e) {
    var valor = this.input_search._node.value.toLowerCase();
    var baseOptions = this.optionsCC;
    var finalOptions = [];
    this.input._node.options.length = 0;
        var reg_value = new RegExp(valor);
    for (var i = 0; i < baseOptions.length; i++) {
      var innerHTML = baseOptions[i].name;
      if (reg_value.test(innerHTML.toLowerCase())) {
        var option = new Option(innerHTML, baseOptions[i].value);
        finalOptions.push(option);
        }
      }
    for (var i = 0; i < finalOptions.length; i++) {
      this.input.append(finalOptions[i]);
    }
  },

  /**
  * Establece la obligatoriedad del campo
  *
  * @param {boolean}
  * @return {boolean}
  */
  setMandatory: function(isMandatory) {
    this.data.attrs.required = isMandatory;

    if(isMandatory){
      this.widget.one('.rn_Required').show();
    } else {
      this.widget.one('.rn_Required').hide();
    }

    return true;
  },

  /**
  * Establece si el campo estará desabilitado o no
  *
  * @param {boolean}
  * @return {boolean}
  */
  setDisabled: function(isDisabled) {    this.data.attrs.disabled = isDisabled;

    if(isDisabled){
      this.input.set('disabled', 'disabled');
    } else {
      this.input.set('disabled', '');
    }
  },

  /**
  * Valida contra los el valor del widget según corresponda
  *
  * @param {event} e evento que invóca al método
  * @param {array} errors arreglo de errores
  * @return {array} Arreglo de objetos de errores
  */
  validate: function(e, errors) {
    var error = [];

    if(this.widget.ancestor('[hidden]') || this.widget._isHidden()) {
      return errors;
    }

    if (this.data.attrs.required) {
      if (!this.input.get('value')) {
        error.push({
          valid: false,
          name: this.baseDomID,
          instance: this.instanceID,
          message: 'El campo <strong>' + this.data.attrs.label_input + '</strong> es obligatorio.'
        });
      }
    }

    if (error.length) {
      this.Y.each(error, function(value, i) {
        errors[0].push(value);
      });
    } else {
      errors[0].push({
        valid: true,
        name: this.baseDomID,
        message: ''
      });
    }

    return errors;
  },

  /**
  * Obtiene el ítem selecionado
  *
  * @param {event} e evento que invóca al método
  * @param {array} arr array de parámetros
  */
  getSelectedItem: function(e, arr){
    var item = this.input.get('options').item(this.input.get('selectedIndex'));

    return item;
  }
});
