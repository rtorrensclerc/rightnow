RightNow.namespace('Custom.Widgets.menu.ChangeOrganization');
Custom.Widgets.menu.ChangeOrganization = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {
        window.ChangeOrganization = this;
        this.widget = this.Y.one(this.baseSelector);
        this.btn_save = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_save"]');
        if (this.btn_save) this.btn_save.on("click", this._request, this);
    },

    _request: function(e) {
        //Debe validar si esta seleccionado e. estado
        //document.location.reload();

        this._setOrder(e, 1);



    },
    /**
     * Realiza el llamado al servicio setOrder.
     *
     * Tipos de Solicitud
     * 1 ->  Crear
     * 2 ->  Actualizar
     * 3 ->  Enviar
     *
     * @param {Event} Mouse Event
     * @param {Integer} Tipo de Solicitud
     */
    _setOrder: function(e, action) {


        data = {};
        data.rut = document.getElementById("organization").value;


        this.setOrganization_ajax_endpoint(data);
        return true;


    },
    /** #######################################################################
	    SERVICIOS
	######################################################################## */

    /**
     * Endpoint para configurar la organizacion seleccionada
     *
     * @param {event} e Evento que invóca al método
     * @return {boolean}
     */
    setOrganization_ajax_endpoint: function(params) {

        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,

                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.setorganization_ajax_endpoint, eventObj.data, {
            successHandler: this.setorganization_ajax_endpointCallback,
            scope: this,
            data: eventObj,
            json: true
        });
    },
    /**
     * Manejador del la respuesta del endpoint #setOrganization_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del endpoint
     */
    setorganization_ajax_endpointCallback: function(response, originalEventObj) {


        document.location.reload();

    },
    /**
     * Sample widget method.
     */
    methodName: function() {

    }
});