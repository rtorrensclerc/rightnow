RightNow.namespace('Custom.Widgets.Info.ticketdetail');
Custom.Widgets.Info.ticketdetail = RightNow.Widgets.extend({     /**
     * Widget constructor.
     */
    constructor: function () {
        window.trackingdetail = this;
        this.widget = this.Y.one(this.baseSelector);

        this.loadWidgets = window.setInterval((function (_parent) {
            return function () {
                var x = [];

                if (x) {
                    _parent.init();
                    window.clearInterval(_parent.loadWidgets);
                }
            }
        })(this), 100);
    },
    init: function () {


        //this.handler_get_incident_data();
    },

    /**
     * Sample widget method.
     */
    handler_get_incident_data: function () {
        data = {};


        this.get_incident_data_ajax_endpoint(data);


    },
    get_incident_data_ajax_endpoint: function (params) {
        // Make AJAX request:
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                // Parameters to send
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.get_incident_data_ajax_endpoint, eventObj.data, {
            successHandler: this.get_incident_data_ajax_endpointCallback,
            timeout: 160000,
            scope: this,
            data: eventObj,
            json: true
        });
    },
    get_incident_data_ajax_endpointCallback: function (response, originalEventObj) {

        r = response;
        let elements = document.getElementsByName("Incident.Threads");
        elements[0].value = "hola";

        // this.btn_get_trx.set('disabled', false);


    }

});