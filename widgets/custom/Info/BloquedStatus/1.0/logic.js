RightNow.namespace('Custom.Widgets.Info.BloquedStatus');
Custom.Widgets.Info.BloquedStatus = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {
        window.widget_BloquedStatus = this;
        this.widget = this.Y.one(this.baseSelector);
        if (
            document.getElementById("btn_Notif")

        ) {
            this.btn_Notif = this.widget.one("#btn_Notif");
            this.btn_Notif.on('click', this.handler_btn_Notif, this);
        }

    },
    handler_btn_Notif: function(e) {

        data = {};
        this.btn_Notif = this.widget.one("#btn_Notif");
        this.btn_Notif.hide();

        data['Texto'] = 'Hello';
        data['bloqueo'] = 0;
        if (
            document.getElementById("id_mora") &&
            document.getElementById("id_mora").value
        ) {
            data['id_mora'] = document.getElementById("id_mora").value;
            data['bloqueo'] = data['bloqueo'] + 1;
        }
        if (
            document.getElementById("id_factura") &&
            document.getElementById("id_factura").value
        ) {
            data['id_factura'] = document.getElementById("id_factura").value;
            data['bloqueo'] = data['bloqueo'] + 2;
        }

        if (
            document.getElementById("id_info") &&
            document.getElementById("id_info").value
        ) {
            data['id_info'] = document.getElementById("id_info").value;
            data['bloqueo'] = data['bloqueo'] + 4;
        }
        if (
            document.getElementById("id_riesgo") &&
            document.getElementById("id_riesgo").value
        ) {
            data['id_riesgo'] = document.getElementById("id_riesgo").value;
            data['bloqueo'] = data['bloqueo'] + 8;
        }
        if (
            document.getElementById("id_deuda") &&
            document.getElementById("id_deuda").value
        ) {
            data['id_deuda'] = document.getElementById("id_deuda").value;
            data['bloqueo'] = data['bloqueo'] + 16;
        }

        if (
            document.getElementById("id_incidente") &&
            document.getElementById("id_incidente").value
        ) {
            data['id_incidente'] = document.getElementById("id_incidente").value;

        }

        this.SendMailRequest_ajax_endpoint(data);



    },
    /**
     * Sample widget method.
     */
    methodName: function() {

    },

    /**
     * Makes an AJAX request for `default_ajax_endpoint`.
     */
    SendMailRequest_ajax_endpoint: function(params) {
        // Make AJAX request:
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                // Parameters to send
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.SendMailRequest_ajax_endpoint, eventObj.data, {
            successHandler: this.SendMailRequest_ajax_endpointCallback,
            timeout: 120000,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    /**
     * Handles the AJAX response for `default_ajax_endpoint`.
     * @param {object} response JSON-parsed response from the server
     * @param {object} originalEventObj `eventObj` from #getDefault_ajax_endpoint
     */
    SendMailRequest_ajax_endpointCallback: function(response, originalEventObj) {
        // Handle response

    }
});