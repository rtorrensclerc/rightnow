RightNow.namespace('Custom.Widgets.assistance.SpecialTicket');
Custom.Widgets.assistance.SpecialTicket = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {
        window.widget_SpecialTicket = this;
        this.widget = this.Y.one(this.baseSelector);
        this.btn_get_ticket = this.widget.one("#btn_get_ticket");
        this.btn_get_ticket.on('click', this.handler_btn_get_ticket, this);
        if (this.widget.one("#btn_upload_csv") !== null) {
            this.btn_upload_csv = this.widget.one("#btn_upload_csv");
            this.upload_input = this.widget.one(".upload_box input");
            // Eventos
            this.btn_upload_csv.on('click', this.upload, this);
            // Subscipción de eventos
            RightNow.Event.subscribe("evt_SendFile", this.handler_btn_upload_csv, this);
        }


    },
    handler_btn_get_ticket: function(e) {

        id_hh = Integer.getInstanceByName('ID_HH');
        window.location = '/app/reparacion/special/special_suport/no_hh/' + id_hh.input.get('value');
    },
    upload: function(e) {


        if (!this.upload_input.get('files').size()) {
            Integer.dialog('Debe seleccionar un archivo CSV.');

            return false;
        }



        this.f = this.upload_input.get('files')._nodes[0];
        this.reader = new FileReader();

        this.reader.onload = (function(theFile, btn) {
            return function(e) {
                RightNow.Event.fire("evt_SendFile", null, e.target.result);
            };

        })(this.f, e.target);

        this.reader.readAsText(this.f);
    },
    /**
     * Envía el formulario de solicitud
     *
     * @param e {event}
     */
    handler_btn_upload_csv: function(e, arr_args) {
        this.btn_upload_csv.setAttribute('disabled', 'disabled');

        // Variables
        data = {};
        data.data = arr_args[1];

        this.sendCSV_ajax_endpoint(data);

        return true;
    },

    /** #######################################################################
        SERVICIOS
    ######################################################################## */

    /**
     * Endpoint para crear la solicitud de insumos múltiples
     *
     * @param {event} e Evento que invóca al método
     * @return {boolean}
     */
    sendCSV_ajax_endpoint: function(params) {

        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.sendCSV_ajax_endpoint, eventObj.data, {
            successHandler: this.sendCSV_ajax_endpointCallback,
            failureHandler: function() {
                this.btn_upload_csv.removeAttribute('disabled');
            },
            timeout: 120000,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    /**
     * Manejador del la respuesta del endpoint #sendCSV_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del endpoint
     */
    sendCSV_ajax_endpointCallback: function(response, originalEventObj) {
        this.btn_upload_csv.removeAttribute('disabled');

        if (response.success) {
            this.dataset = response;
            //Integer.dialog('Listeilor');
            var i;
            var resultado = "";
            for (i = 0; i < response.no_errors.length; i++) {
                resultado = resultado + response.no_errors[i]["ID_HH"] + "<br>";

            }
            document.getElementById("demo").innerHTML = resultado;

        }

    },

    /**
     * Sample widget method.
     */
    methodName: function() {

    }
});