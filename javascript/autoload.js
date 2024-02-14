if(!window.Y) YUI().use('*', function (Y) { window.Y = Y; });

// Determina si es la página adecuada
if(window.location.pathname === '/app/utils/create_account') {
  // Crea un intervalo para asegurar la carga de los widgets
  this.loadWidgets = window.setInterval((function(_parent) {
    return function() {
      var originReference = RightNow.Widgets.getWidgetInstance(window.Y.one('.rn_OriginReference div').get('id').replace('rn_', ''));
  
      if (originReference) {
          _parent.Load.init();
          window.clearInterval(_parent.loadWidgets);
      }
    };
  })(this), 100);
}

Load = {
  config : [{"id":null,"label":"No Aplica","visible_text":false,"visible_select":false,"options":""},{"id":210,"label":"Red Social","visible_text":false,"visible_select":true,"options":"LinkedIn, Twitter, Facebook, Instagram, Slack, WhatsApp"},{"id":211,"label":"Email de contacto","visible_text":true,"visible_select":false,"options":""},{"id":213,"label":"Nombre de Colaborador","visible_text":true,"visible_select":false,"options":""},{"id":214,"label":"Medios","visible_text":false,"visible_select":true,"options":"Radio, TV, Periodico, Revista, Pagina Web, Via Pública"},{"id":215,"label":"Comente","visible_text":true,"visible_select":false,"options":""}],
  options: [],

  /**
   * Inicia el script
   */
  init: function(){
    this.container_originReference  = Y.one('.rn_OriginReference');
    this.container_originReferred   = Y.one('.rn_OriginReferred');
    this.originReference            = RightNow.Widgets.getWidgetInstance(window.Y.one('.rn_OriginReference div').get('id').replace('rn_', ''));
    this.originReferred             = RightNow.Widgets.getWidgetInstance(window.Y.one('.rn_OriginReferred div').get('id').replace('rn_', ''));
    this.container_select_reference = Y.one('.rn_SelectReferred');
    this.select_reference           = Integer.getInstanceByName('select_reference');

    // Oculta los elementos iniciales
    this.container_originReferred.hide();
    this.container_select_reference.hide();
  
    this.originReference.input.on('change', this.change_reference, this);
    this.select_reference.input.on('change', this.change_select_reference, this);
  },
  
  /**
   * Ejecuta la lógica al cambiar el select de referencia
   */
  change_select_reference: function() {
    var index = this.select_reference.input.get('selectedIndex');
    var text  = this.select_reference.input.get('options').item(index).get('text')
    this.originReferred.input.set('value', text);
  },
  
  /**
   * Ejecuta la lógica al cambiar la referencia
   */
  change_reference: function() {
    this.id = this.originReference.input.get('value');

    this.originReferred.input.set('value', '');

    for (var i = 0; i < this.config.length; i++) {
      if(this.config[i].id == this.id) {
        if(this.config[i].visible_text) {
          this.container_originReferred.show();
          this.originReferred.setLabel(this.config[i].label)
        } else {
          this.container_originReferred.hide();
          options = [];
        }

        if(this.config[i].visible_select) {
          this.container_select_reference.show();
          options = this.config[i].options.split(',');
          arr_options = [];
          for (var i_option = 0; i_option < options.length; i_option++) {
            option = {};
            option.ID   = i_option;
            option.name = options[i_option];
            arr_options.push(option);
            Y.one('.rn_SelectReferred label').setHTML(this.config[i].label);
          }
          Integer.appendOptions(this.select_reference, arr_options, 'select', null, true);
        } else {
          this.container_select_reference.hide();
          options = [];
        }
      }
    }
  }
};
