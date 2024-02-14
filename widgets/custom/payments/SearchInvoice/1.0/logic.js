RightNow.namespace('Custom.Widgets.payments.SearchInvoice');
Custom.Widgets.payments.SearchInvoice = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function () {
        // Generales
        window.widget_SearchInvoice = this;
        this.widget = this.Y.one(this.baseSelector);
        this.btn_get_invoice = this.widget.one("#btn_get_invoice");
        this.btn_get_invoice.on('click', this.handler_btn_get_invoice, this);
        this.table_Lines = this.widget.one('.rn_Grid .rn_LastInvoices');
        this.table_Lines_thead = this.table_Lines.one('thead');
        this.table_Lines_tbody = this.table_Lines.one('tbody');

        this.btn_erase = this.widget.one("#btn_erase");
        this.btn_erase.on('click', this.handler_btn_erase, this);


        this.ContentTab_detail = this.widget.one('.rn_ContentTab_detail');
        this.ContentTab_detail.hide();

        this.table_detail = this.widget.one('.rn_Grid .rn_detail');
        this.table_detail_thead = this.table_detail.one('thead');
        this.table_detail_tbody = this.table_detail.one('tbody');
        this.ContentTab_Loading = this.widget.one('.rn_ContentTab_Loading');


        this.btn_downloadCSV = this.Y.one('.btn_downloadCSV');
        this.btn_downloadXLSX = this.Y.one('.btn_downloadXLSX');
        this.btn_downloadCSV.on('click', this.downloadCSV, this);
        this.btn_downloadXLSX.on('click', this.downloadXLSX, this);


        this.loadWidgets = window.setInterval((function (_parent) {
            return function () {
                var temporal_widget = [];
                //RightNow.Event.fire('evt_GetInstanceByInputName', temporal_widget, 'contract_list');
                if (temporal_widget) {
                    _parent.init();
                    window.clearInterval(_parent.loadWidgets);
                }
            };
        })(this), 100);
    },
    init: function () {

        this.invoice_number = Integer.getInstanceByName("invoice_number");
        this.invoice_rut = Integer.getInstanceByName("invoice_rut");
        this.invoice_number.input.on("keypress", this.chequer, this);
        this.invoice_rut.input.on("keypress", this.chequer, this);




    },
    chequer: function (e) {
        // debugger;
        if (e.keyCode === 13) {
            this.handler_btn_get_invoice();
        }
    },

    handler_btn_get_invoice: function (e) {

        data = {};
        this.ContentTab_detail.hide();
        this.ContentTab_Loading.show();
        this.btn_get_invoice.set('disabled', true);
        this.invoice_number = Integer.getInstanceByName('invoice_number');
        data.invoice_number = this.invoice_number.input.get('value');

        this.invoice_rut = Integer.getInstanceByName('invoice_rut');
        data.invoice_rut = this.invoice_rut.input.get('value');


        //data.invoice_contrato = this.contract_list.input.get('value');

        this.invoice_from = Integer.getInstanceByName('invoice_from');
        data.invoice_from = this.invoice_from.input.get('value');

        this.invoice_to = Integer.getInstanceByName('invoice_to');
        data.invoice_to = this.invoice_to.input.get('value');

        data.invoice_from = data.invoice_from.replace('-', '/');
        data.invoice_from = data.invoice_from.replace('-', '/');
        data.invoice_to = data.invoice_to.replace('-', '/');
        data.invoice_to = data.invoice_to.replace('-', '/');
        if (data.invoice_from.length == 0 && data.invoice_from.length == 0) {
            this.getInvoice_ajax_endpoint(data);
        } else {
            if (this.validatedate(data.invoice_from) &&
                this.validatedate(data.invoice_to)) {
                this.getInvoice_ajax_endpoint(data);
            } else {


                RightNow.UI.Dialog.messageDialog("debe proporcionar ambas fechas", {
                    icon: 'WARN'
                });
                this.btn_get_invoice.set('disabled', false);
                this.ContentTab_Loading.hide();
            }
        }

    },

    handler_btn_erase: function () {
        this.invoice_number = Integer.getInstanceByName('invoice_number');
        this.invoice_number.input.set('value', '');
        this.invoice_rut = Integer.getInstanceByName('invoice_rut');
        this.invoice_rut.input.set('value', '');
        //this.contract_list.input.set('value', '');
        this.invoice_from = Integer.getInstanceByName('invoice_from');
        this.invoice_from.input.set('value', '');
        this.invoice_to = Integer.getInstanceByName('invoice_to');
        this.invoice_to.input.set('value', '');

        Integer.fillTable(null, this.table_Lines_tbody);
        //this.table_Lines.show();
    },
    /**
     * Makes an AJAX request for `asset_search_ajax_endpoint`.
     */
    getInvoice_ajax_endpoint: function (params) {
        // Make AJAX request:
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                // Parameters to send
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.getInvoice_ajax_endpoint, eventObj.data, {
            successHandler: this.getInvoice_ajax_endpointCallback,
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
    getInvoice_ajax_endpointCallback: function (response, originalEventObj) {
        this.btn_get_invoice.set('disabled', false);
        this.ContentTab_Loading.hide();

        if (response.success) {
            window.last_response = response;
            //document.getElementById("customer_info2").style.display = 'block';
            //debugger;
            this.fillTables(response.invoice_data);
            // debugger;

            this.table_Lines.show();


        } else {
            this.btn_get_invoice.set('disabled', false);
            RightNow.UI.Dialog.messageDialog(response.message, {
                icon: 'WARN'
            });
        }
    },
    fillTables: function (data) {


        if (data) {

            for (var i = 0, count = data.length; i < count; i++) {
                var is_pending = (data[i].ammount_remaining > 0) ? true : false;
                var container = this.Y.Node.create('<span></span>');


                data[i].trx_contract = 0;

                if (is_pending) {
                    data[i].trx_paid = 'Pendiente de Pago';
                    container.append(this.Y.Node.create('<br>'));
                    if (!RightNow.Interface.getConfig('CUSTOM_CFG_HIDE_BTN_PAY') && !RightNow.Interface.getConfig('CUSTOM_CFG_HIDE_BTN_PAY_INVOICE_PAYMENTS')) {
                        btn_pay = this.Y.Node.create('<a href="javascript:return void 0;" class="btn">Pagar</a>');
                        btn_pay.on('click', this.pay, data[i]);
                        container.append(btn_pay);

                    }

                } else {
                    data[i].trx_paid = 'Pagada';
                }
                btn_invoiceDTE = this.Y.Node.create('<a href="javascript:return void 0;" class="btn">Descargar</a>');

                btn_invoiceDTE.on('click', this.view_DTE, { instance: this, data: data[i] });
                container.append(btn_invoiceDTE);
                if (data[i].contrat) {
                    btn_invoicedetail = this.Y.Node.create('<a href="javascript:return void 0;" class="btn">Ver Detalle</a>');
                    btn_invoicedetail.on('click', this.view_detail, { instance: this, data: data[i] });
                    container.append(btn_invoicedetail);
                } else {
                    btn_invoicedetail = this.Y.Node.create('<a disabled  class="btn">Ver Detalle</a>');
                    container.append(btn_invoicedetail);
                }



                data[i].trx_urls = container;
            }

            // debugger;

            Integer.fillTable(data, this.table_Lines_tbody);

        } else {
            Integer.fillTable(null, this.table_Lines_tbody);


        }
    },

    view_detail: function (evt) {
        evt.preventDefault();

        data.rut = this.data.rut;
        //data.contract_number = this.data.contrat;
        data.invoice_number = this.data.trx_number;
        this.instance.getInvoiceDetail_ajax_endpoint(data);


    },
    view_DTE: function (evt) {
        evt.preventDefault();
        data.invoice_number = this.data.trx_number;
        this.instance.getInvoiceDTE_ajax_endpoint(data);


    },


    getInvoiceDTE_ajax_endpoint: function (params) {
        // Make AJAX request:
        this.ContentTab_Loading.show();
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                // Parameters to send
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.getInvoiceDTE_ajax_endpoint, eventObj.data, {
            successHandler: this.getInvoiceDTE_ajax_endpointCallback,
            timeout: 60000,
            scope: this,
            data: eventObj,
            json: true
        });
    },
    getInvoiceDTE_ajax_endpointCallback: function (response, originalEventObj) {
        this.ContentTab_Loading.hide();
        if (response.success) {
            window.open(response.url_dte, '_blank');

        } else {
            this.btn_get_invoice.set('disabled', false);
            RightNow.UI.Dialog.messageDialog(response.message, {
                icon: 'WARN'
            });
        }

    },

    /**
     * Makes an AJAX request for `getInvoiceDetail_ajax_endpoint`.
     */
    getInvoiceDetail_ajax_endpoint: function (params) {
        // Make AJAX request:
        this.ContentTab_Loading.show();
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                // Parameters to send
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.getInvoiceDetail_ajax_endpoint, eventObj.data, {
            successHandler: this.getInvoiceDetail_ajax_endpointCallback,
            timeout: 60000,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    /**
     * Manejador del la respuesta del endpoint #getInvoiceDetail_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del método #getDefault_ajax_endpoint
     */

    getInvoiceDetail_ajax_endpointCallback: function (response, originalEventObj) {
        debugger;
        this.btn_get_invoice.set('disabled', false);
        this.ContentTab_Loading.hide();
        if (response.success) {
            window.last_response = response;
            this.ContentTab_detail.show();
            this.table_Lines.hide();

            //document.getElementById("customer_info2").style.display = 'block';
            //debugger;
            Integer.fillTable(response.invoice_data, this.table_detail_tbody);
            //this.fillTables(response.invoice_data);
            // debugger;

        } else {
            this.btn_get_invoice.set('disabled', false);
            RightNow.UI.Dialog.messageDialog(response.message, {
                icon: 'WARN'
            });
        }


    },

    /**
     * Permite redireccionar a la pantalla de pago
     * 
     * /cc/Transactions/initTransaction/invoice/{var_invoice}/amount/{var_amount}/contract_number/{var_contract}
     * https://dimacofi.getdte.cl/custodia_digital.php?id=S92083000-5_T33_F241527_H6dd1fc0de704acf05d31ac9aec948e09
     */
    pay: function (evt) {
        contrat = this.contrat;
        if (contrat === null) {
            contrat = 'Sin Referencia';
        }

        if (evt.target.getAttribute('disabled') != '') {
            return false;
        }

        debugger;
        if (this.trx_number && this.ammount_remaining && contrat) {

            //window.location.href = '/cc/Transactions/initTransaction/invoice/' + this.trx_number + '/amount/' + this.ammount_remaining + '/contract_number/' + contrat;
            window.location.href = '/cc/Transactions_v2/initTransaction/invoice/' + this.trx_number + '/amount/' + this.ammount_remaining;
        } else {
            return false;
        }

        return true;
    },

    /**
     * Sample widget method.
     */
    methodName: function () {

    },
    validatedate: function (inputText) {

        var dateformat = /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/;

        // Match the date format through regular expression
        if (inputText.match(dateformat)) {

            //Test which seperator is used '/' or '-'
            var opera1 = inputText.split('/');
            var opera2 = inputText.split('-');
            lopera1 = opera1.length;
            lopera2 = opera2.length;
            // Extract the string into month, date and year
            if (lopera1 > 1) {
                var pdate = inputText.split('/');
            } else if (lopera2 > 1) {
                var pdate = inputText.split('-');
            }
            var dd = parseInt(pdate[2]);
            var mm = parseInt(pdate[1]);
            var yy = parseInt(pdate[0]);
            // Create list of days of a month [assume there is no leap year by default]
            var ListofDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            if (mm == 1 || mm > 2) {
                if (dd > ListofDays[mm - 1]) {
                    //alert('Invalid date format!');
                    return false;
                }
            }
            if (mm == 2) {
                var lyear = false;
                if ((!(yy % 4) && yy % 100) || !(yy % 400)) {
                    lyear = true;
                }
                if ((lyear == false) && (dd >= 29)) {

                    return false;
                }
                if ((lyear == true) && (dd > 29)) {

                    return false;
                }
            }
        } else {


            return false;
        }
        return true;
    },
    /**
     * Realiza la descar de la información de la tabla en CSV
     */
    downloadCSV: function () {
        var csv = Integer.table2CSV(this.table_detail);

        download(csv, "reporte.csv", "text/plain");
    },
    /**
     * Realiza la descarga de la información de la tabla en XLSX
     */
    downloadXLSX: function () {
        var _data = Integer.table2Array(this.table_detail);

        var wb = XLSX.utils.book_new();
        wb.Props = {
            Title: "Reporte",
            Subject: "Reporte",
            Author: "Integer",
            CreatedDate: new Date(2018, 10, 19)
        }

        wb.SheetNames.push("Registros");
        var ws = XLSX.utils.aoa_to_sheet(_data);
        wb.Sheets["Registros"] = ws;
        var wbout = XLSX.write(wb, {
            bookType: 'xlsx',
            type: 'binary'
        });

        download(new Blob([this.s2ab(wbout)], {
            type: "application/octet-stream"
        }), "Reporte.xlsx", "application/vnd.ms-excel");
    },
    /**
     * download(new Blob(s2ab(wbout)), "Reporte.xlsx", "application/vnd.ms-excel");
     */
    s2ab: function (s) {
        var buf = new ArrayBuffer(s.length); //convert s to arrayBuffer
        var view = new Uint8Array(buf); //create uint8array as viewer

        for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF; //convert to octet

        return buf;
    },
    validate: function () { // Variables
        this.errors = [];
        this.errors_messages = [];
        this.is_valid = true;
        return true;
    }
});