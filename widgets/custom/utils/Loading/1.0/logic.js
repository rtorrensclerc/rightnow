RightNow.namespace('Custom.Widgets.utils.Loading');
Custom.Widgets.utils.Loading = RightNow.Widgets.extend({

  /**
   * Constructor del widget
   * 
   * @author Integer
   */
  constructor: function () {
    // Variables
    this.is_active = false;

    // Mapeo DOM
    this.widget = this.Y.one(this.baseSelector);

    // Suscripción de Eventos
    RightNow.Event.subscribe("evt_ShowLoading", this.show, this);
    RightNow.Event.subscribe("evt_HideLoading", this.hide, this);

    // Oculta el widget una vez termina la carga
    this.hide();
  },

  /**
   * Hace visible el widget
   */
  show: function () {
    this.widget.removeClass('inactive');
    this.widget.addClass('active');
    
    this.is_active = true;
  },
  
  /**
   * Oculta el widget
   */
  hide: function () {
    this.widget.removeClass('active');
    this.widget.addClass('inactive');

    this.is_active = false;
  }
});