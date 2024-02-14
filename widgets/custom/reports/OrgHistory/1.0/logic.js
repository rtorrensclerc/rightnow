RightNow.namespace('Custom.Widgets.reports.OrgHistory');
Custom.Widgets.reports.OrgHistory = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {
        window.widget_reports_OrgHistory = this;
        this.widget = this.Y.one(this.baseSelector);
        this.btn_get_tickets = this.widget.one("#btn_get_tickets");
        this.btn_get_tickets.on('click', this.handler_btn_get_tickets, this);

        this.ContentTab_Loading = this.widget.one('.rn_ContentTab_Loading');

        this.loadWidgets = window.setInterval((function(_parent) {
            return function() {
                var temporal_widget = [];
                RightNow.Event.fire('evt_GetInstanceByInputName', temporal_widget, 'ticket_to');
                if (temporal_widget.length) {
                    _parent.init();
                    window.clearInterval(_parent.loadWidgets);
                }
            };
        })(this), 100);

    },
    init: function() {

        // Instancias
        //this.contract_list = Integer.getInstanceByName('contract_list');
        // Carga Listas
        //Integer.appendOptions(this.contract_list, this.data.js.list.contracts, 'select', null, true);
        //this.contract_list.input.set('selectedIndex', 1)

    },

    handler_btn_get_tickets: function(e) {

        data = {};

        this.ContentTab_Loading.show();
        this.btn_get_tickets.set('disabled', true);

        this.tickets_from = Integer.getInstanceByName('ticket_from');
        data.tickets_from = this.tickets_from.input.get('value');

        this.tickets_to = Integer.getInstanceByName('ticket_to');
        data.tickets_to = this.tickets_to.input.get('value');

        data.tickets_from = data.tickets_from.replace('-', '/');
        data.tickets_from = data.tickets_from.replace('-', '/');
        data.tickets_to = data.tickets_to.replace('-', '/');
        data.tickets_to = data.tickets_to.replace('-', '/');
        if (data.tickets_from.length == 0 && data.tickets_from.length == 0) {
            this.getTickets_ajax_endpoint(data);
        } else {
            if (this.validatedate(data.tickets_from) &&
                this.validatedate(data.tickets_to)) {
                this.getTickets_ajax_endpoint(data);
            } else {


                RightNow.UI.Dialog.messageDialog("debe proporcionar ambas fechas", {
                    icon: 'WARN'
                });
                this.btn_gett_ickets.set('disabled', false);
                this.ContentTab_Loading.hide();
            }
        }

    },
    handler_btn_get: function(e) {
        data = {};

        this.getTicket_ajax_endpoint(data);
    },
    getTickets_ajax_endpoint: function(params) {
        // Make AJAX request:
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                // Parameters to send
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.getTickets_ajax_endpoint, eventObj.data, {
            successHandler: this.getTickets_ajax_endpointCallback,
            timeout: 60000,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    /**
     * Manejador del la respuesta del endpoint #getInvoicePaymentList.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del método #getDefault_ajax_endpoint
     */
    getTickets_ajax_endpointCallback: function(response, originalEventObj) {
        this.btn_get_tickets.set('disabled', false);
        this.ContentTab_Loading.hide();

        if (response.success) {
            window.last_response = response;
            //document.getElementById("customer_info2").style.display = 'block';
            //debugger;
            this.fillTables(response.incidents);
            // debugger;

            this.table_Lines.show();


        } else {
            this.btn_get_tickets.set('disabled', false);
            RightNow.UI.Dialog.messageDialog(response.message, {
                icon: 'WARN'
            });
        }
    }
});