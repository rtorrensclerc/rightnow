RightNow.namespace('Custom.Widgets.menu.ChangeOrganization2');
Custom.Widgets.menu.ChangeOrganization2 = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {
        this.btn_save = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_save"]');
        if (this.btn_save) this.btn_save.on("click", this._request, this);
    },
    _request: function(e) {
        this._setOrder(e, 1);
    },
    /**
     * Realiza el llamado al servicio setOrder.
     *
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
    /**
     * Makes an AJAX request for `setorganization_ajax_endpoint`.
     */
    setOrganization_ajax_endpoint: function() {
        // Make AJAX request:
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                // Parameters to send
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
     * Handles the AJAX response for `setorganization_ajax_endpoint`.
     * @param {object} response JSON-parsed response from the server
     * @param {object} originalEventObj `eventObj` from #getSetorganization_ajax_endpoint
     */
    setorganization_ajax_endpointCallback: function(response, originalEventObj) {
        // Handle response
    }
});