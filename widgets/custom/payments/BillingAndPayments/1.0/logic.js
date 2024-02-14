RightNow.namespace('Custom.Widgets.payments.BillingAndPayments');
Custom.Widgets.payments.BillingAndPayments = RightNow.Widgets.extend({

    /**
     * Contructor del Widget
     */
    constructor: function() {

        // Variables
        Integer.__BillingAndPayments = this;
        window.last_response = '';

        // Ejecuta `init` una vez realizada la carga de los widgets de entrada
        this.loadWidgets = window.setInterval((function(_parent) {
            return function() {
                var temporal_widget = [];
                RightNow.Event.fire('evt_GetInstanceByInputName', temporal_widget, 'contract_list');
                if (temporal_widget) {
                    _parent.init();
                    window.clearInterval(_parent.loadWidgets);
                }
            };
        })(this), 100);

        // Precarga de base de mensajes
        // RightNow.Interface.getMessage("CUSTOM_MSG_XXX");
    },

    /**
     * Método inicial
     */
    init: function() {

        // Mapeo de elementos del DOM

        this.widget = this.Y.one(this.baseSelector);

        // Instancias
        this.contract_list = Integer.getInstanceByName('contract_list');

        // Carga Listas
        Integer.appendOptions(this.contract_list, this.data.js.list.contracts, 'select', null, true);
        this.contract_list.input.set('selectedIndex', 1)
        this.changeContract();

        this.dataConsumptionLastSixMonths = null;


        // Subscipción de eventos
        RightNow.Event.subscribe("evt_UpdateLastConsumptionsLines", this.updateLastConsumptionsLines, this);

        // Eventos
        this.contract_list.input.on('change', this.changeContract, this);
    },

    /**
     * Actualiza la información del detalle de la factura
     * 
     * @param evt {Event}
     */
    updateLastConsumptionsLines: function(evt, arr_args) {
        var item = arr_args[1];
        var contract_number = this.contract_list.input.get('value');
        // var invoice_id = this.dataConsumptionLastSixMonths[item].data[0].invoice;
        var invoice_id = this.dataConsumptionLastSixMonths[item].TRX_NUMBER;

        this.getLastConsumptionsLines(contract_number, invoice_id);
    },

    /**
     * 
     */
    changeContract: function(e) {
        var value = this.contract_list.input.get('value');

        if (value) {
            this.getConsumptionLastSixMonths(value); // Información de gráficos
            // Integración detalle agrupado.
            this.getDetailInvoices(value);
            // this.getLastConsumptionsLines(value); // Detalle de la factura
            // this.getLastSixInvoices(value); // Últimas 6 facturas
        }

        return true;
    },

    /** ################################################################################################################################################ */
    /**
     * Endpoint que obtiene el detalle agrupado de las facturas.
     */
    getDetailInvoices: function(value) {
        
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                contract_number: value
            }
        });

        RightNow.Ajax.makeRequest(this.data.attrs.getDetailInvoices, eventObj.data, {
            successHandler: this.getDetailInvoicesCallback,
            timeout: 60000,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    getDetailInvoicesCallback: function(response, originalEventObj) {
        if (response.success) {
            window.detailsInvoices = response;
            this.detailsInvoices = response.detail;

            // var invoice_id = response.detail[0].TRX_NUMBER;
            // console.log('número de factura ' + response.detail[response.detail.length-1].TRX_NUMBER);
            // console.log('monto de factura ' + response.detail[response.detail.length-1].AMOUNT);
            // console.log(response.detail);
            /* var invoice_id = response.detail[response.detail.length-1].TRX_NUMBER;
            var contract_number = parseInt(this.contract_list.input.get('value'));

            if (invoice_id) {
                this.getLastConsumptionsLines(contract_number, invoice_id); // Detalle de la factura
            } */            
            // TODO: Acá enviar datos al gráfico de pastel.

            RightNow.Event.fire('evt_LoadDataDonutsChart', response.detail);

        } else {
            RightNow.UI.Dialog.messageDialog(response.message, {
                icon: 'WARN'
            });
        }
    },


    /**
     * Endpoint que obtiene el consumo de los ultimos 6 meses
     */
    getConsumptionLastSixMonths: function(value) {
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                contract_number: value
            }
        });

        RightNow.Ajax.makeRequest(this.data.attrs.getConsumptionLastSixMonths, eventObj.data, {
            successHandler: this.getConsumptionLastSixMonthsCallback,
            timeout: 60000,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    /**
     * Manejador del la respuesta del endpoint #getConsumptionLastSixMonths.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del método #getDefault_ajax_endpoint
     */
    getConsumptionLastSixMonthsCallback: function(response, originalEventObj) {
        if (response.success) {
            window.last_response = response;

            this.dataConsumptionLastSixMonths = response.detail;

            // var invoice_id = response.detail[0].TRX_NUMBER;
            // console.log('número de factura ' + response.detail[response.detail.length-1].TRX_NUMBER);
            // console.log('monto de factura ' + response.detail[response.detail.length-1].AMOUNT);
            // console.log(response.detail);
            var invoice_id = response.detail[response.detail.length-1].TRX_NUMBER;
            var contract_number = parseInt(this.contract_list.input.get('value'));

            if (invoice_id) {
                this.getLastConsumptionsLines(contract_number, invoice_id); // Detalle de la factura
            }
            
            RightNow.Event.fire('evt_LoadDataBarChart', response);
            
            // TODO: Acá enviar datos al gráfico de pastel.
            // RightNow.Event.fire('evt_LoadDataDonutsChart', response);

        } else {
            debugger;
            RightNow.UI.Dialog.messageDialog(response.message, {
                icon: 'WARN'
            });
        }
    },

    /** ################################################################################################################################################ */
    /**
     * Endpoint que obtiene el detalle del mes de las lineas asociadas a una factura
     */
    getLastConsumptionsLines: function(contract_number, invoice_number) {
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                contract_number: contract_number,
                invoice_number: invoice_number
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.getLastConsumptionsLines, eventObj.data, {
            successHandler: this.getLastConsumptionsLinesCallback,
            timeout: 60000,
            scope: this,
            data: eventObj,
            json: true,
            timeout: 60000,
        });
    },

    /**
     * Manejador del la respuesta del endpoint #getLastConsumptionsLines.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del método #getDefault_ajax_endpoint
     */
    getLastConsumptionsLinesCallback: function(response, originalEventObj) {
        debugger;
        if (response.success) {
            window.last_response = response;
            RightNow.Event.fire('evt_LoadDataTaxDocument', response);
        } else {
            RightNow.UI.Dialog.messageDialog(response.message, {
                icon: 'WARN'
            });
        }
    },

    /** ################################################################################################################################################ */
    /**
     * Endpoint que obtiene las ultimas 6 facturas
     */
    getLastSixInvoices: function(value) {
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                contract_number: value
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.getLastSixInvoices, eventObj.data, {
            successHandler: this.getLastSixInvoicesCallback,
            timeout: 60000,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    /**
     * Manejador del la respuesta del endpoint #getLastSixInvoices.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del método #getDefault_ajax_endpoint
     */
    getLastSixInvoicesCallback: function(response, originalEventObj) {
        if (response.success) {
            window.last_response = response;
        } else {
            RightNow.UI.Dialog.messageDialog(response.message, {
                icon: 'WARN'
            });
        }
    }
});