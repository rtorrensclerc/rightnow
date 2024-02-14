RightNow.namespace('Custom.Widgets.assistance.ContactAssistance');
Custom.Widgets.assistance.ContactAssistance = RightNow.Widgets.extend({
    /**
     * Formulario de Opinion
     */
    constructor: function() {
        if (this.data.attrs.read_only) {
            return false;
        }
        this.maxQtyItem = RightNow.Interface.getConfig('CUSTOM_CFG_MAX_QTY_ITEM');
        window.widget_contact_assistance = this;
        this.widget = this.Y.one(this.baseSelector);

        this.errors_container = this.widget.one('#rn_ErrorLocation');
        this.items = {};
        this.screenForm = this.widget.one('.rn_ScreenForm');
        this.screenSuccess = this.widget.one('.rn_ScreenSuccess');
        // Información de la HH
        this.btn_get_tipo = this.widget.one("#btn_get_tipo");
        this.btn_get_tipo.on('click', this.handler_btn_get_tipo, this);
        // Variables
        this.errors = [];

        this.loadWidgets = window.setInterval((function(_parent) {
            return function() {
                var x = [];
                RightNow.Event.fire('evt_GetInstanceByInputName', x, 'hh_selector');

                if (x) {
                    _parent.init();
                    window.clearInterval(_parent.loadWidgets);
                }
            }
        })(this), 100);
    },

    /**
     * Función inicial
     */
    init: function() {

        // Eventos

        // Steps
        RightNow.Event.fire('evt_AddStep', { "description": "Cuentanos tu Opinion" });
        RightNow.Event.fire('evt_AddStep', { "description": "Información de solicitud" });
        RightNow.Event.fire('evt_AddStep', { "description": "Confirmación de solicitud" });
        RightNow.Event.fire('evt_ChangeStep', { "index": 1 });
    },
    /**
     * Evento del botón 'Obtener Datos de HH'
     *
     * @param e {event}
     */
    handler_btn_get_tipo: function(e) {

        data = {};
        //this.getTipoDataSelected_ajax_endpoint(data);
        this.btn_get_tipo.set('value', 'Siguiente');
        RightNow.Event.fire('evt_EnableJump', { "index": 1, "enabled": true });
        RightNow.Event.fire('evt_ChangeStep', { "index": 2 });
    },

    /** #################################################################### */

    /**
     * Endpoint para obtener la información de la HH seleccionada
     *
     * @param {event} e Evento que invóca el método
     * @return {boolean}
     */
    getTipoDataSelected_ajax_endpoint: function(params) {
        if (!this.validate()) {
            this.dialog('Error', 'Complete el formulario antes de avanzar.');
            this.disabled_infoHH(false);

            return false;
        }

        this.btn_get_tipo.set('value', 'Procesando...');
        // this.hh_brand.input.set('value', '');
        // this.hh_model.input.set('value', '');

        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });

        RightNow.Ajax.makeRequest(this.data.attrs.getTipoDataSelected_ajax_endpoint, eventObj.data, {
            successHandler: this.getTipoDataSelected_ajax_endpointCallback,
            scope: this,
            data: eventObj,
            json: true
        });
    },
    /**
     * Manejador del la respuesta del endpoint #getHHDataSelected_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del endpoint
     */
    getTipoDataSelected_ajax_endpointCallback: function(response, originalEventObj) {
        this.btn_get_tipo.set('value', 'Siguiente');
        RightNow.Event.fire('evt_EnableJump', { "index": 1, "enabled": true });
        RightNow.Event.fire('evt_ChangeStep', { "index": 2 });
    },
    /** #######################################################################
	    UTILIDADES
	######################################################################## */

    validate: function() {
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
            window.scrollTo(this.errors_container.getX(), this.errors_container.getY());
            return false;
        } else {
            this.errors_container.hide();
            this.errors_container.one('.messages').setHTML('');
        }

        return true;
    },
    /**
     * Sample widget method.
     */
    methodName: function() {

    }
});