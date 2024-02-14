RightNow.namespace('Custom.Widgets.Info.trackingdetail');
Custom.Widgets.Info.trackingdetail = RightNow.Widgets.extend({     /**
     * Widget constructor.
     */
    constructor: function () {
        window.trackingdetail = this;
        this.widget = this.Y.one(this.baseSelector);
        this.ContentTab_Loading = this.widget.one('.rn_ContentTab_Loading');
        this.ContentTab_Loading.hide();
        this.no_data = this.widget.one('.no_data');

        this.table_Lines = this.widget.one('.rn_Grid .rn_search_transaccions');
        this.table_Lines_thead = this.table_Lines.one('thead');
        this.table_Lines_tbody = this.table_Lines.one('tbody');

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
        this.handler_btn_get_trx();

    },

    /**
     * Sample widget method.
     */
    handler_btn_get_trx: function () {
        data = {};

        this.ContentTab_Loading.show();

        this.getTrx_ajax_endpoint(data);


    },
    getTrx_ajax_endpoint: function (params) {
        // Make AJAX request:
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                // Parameters to send
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.getTrx_ajax_endpoint, eventObj.data, {
            successHandler: this.getTrx_ajax_endpointCallback,
            timeout: 160000,
            scope: this,
            data: eventObj,
            json: true
        });
    },
    getTrx_ajax_endpointCallback: function (response, originalEventObj) {


        this.ContentTab_Loading.hide();
        //Integer.fillTable(response.Trx_data, this.table_Lines_tbody);
        this.fillTables(response.Trx_data);
        // this.btn_get_trx.set('disabled', false);


    },
    fillTables: function (data) {


        if (data) {
            Integer.fillTable(data, this.table_Lines_tbody);

        } else {
            Integer.fillTable(null, this.table_Lines_tbody);


        }
    },
    methodName: function () {
    },    /**
     * Renders the `view.ejs` JavaScript template.
     */
    renderView: function () {
        // JS view:
        var content = new EJS({ text: this.getStatic().templates.view }).render({
            // Variables to pass to the view
            // display: this.data.attrs.display
        });
    }
});