RightNow.namespace('Custom.Widgets.payments.ListPaymentsAndInvoices');
Custom.Widgets.payments.ListPaymentsAndInvoices = RightNow.Widgets.extend({

    /**
     * Contructor del Widget
     */
    constructor: function () {

        // Variables
        Integer.__ListPaymentsAndInvoices = this;
        window.last_response = '';

        // Subscipción de eventos
        RightNow.Event.subscribe("evt_LoadDataTaxDocument", this.loadDataTaxDocument, this);

        // Ejecuta `init` una vez realizada la carga de los widgets de entrada
        this.loadWidgets = window.setInterval((function (_parent) {
            return function () {
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
     * Realiza la carga de información para la vista previa de la factura
     *
     * @param evt {Event}
     */
    loadDataTaxDocument: function (evt, arr_args) {
        if (!arr_args[0].success) {
            return false;
        }

        this.data.js._data = arr_args[0].detail;

        this.loadDataDetail();

        return true;
    },

    /**
     * Realiza la carga del detalle de la factura
     */
    loadDataDetail: function () {
        var data = this.data.js._data;

        var arr_hh = [];

        for (var i = 0; i < data.lines.length; i++) {
            if (data.header.divisa !== 'CLP') {
                if (arr_hh.indexOf(data.lines[i].hh) === -1) {
                    data.lines[i].fixed_amount = parseFloat(data.lines[i].fixed_amount) * parseFloat(data.header.exchange_rate);
                    arr_hh.push(data.lines[i].hh);
                } else {
                    data.lines[i].fixed_amount = 0;
                }
            }
        }

        Integer.fillTable(data.lines, this.table_Detail_tbody);

        return true;
    },

    /**
     * Rellena las tablas 
     */
    fillTables: function (data) {
        if (data) {

            for (var i = 0, count = data.lastInvoices.length; i < count; i++) {
                var is_pending = (data.lastInvoices[i].amount_remaining > 0) ? true : false;
                var container = this.Y.Node.create('<span></span>');

                if (!RightNow.Interface.getConfig('CUSTOM_CFG_HIDE_BTN_DOWNLOAD_INVOICE_PAYMENTS') && data.lastInvoices[i].url_dte) {
                    btn_download = this.Y.Node.create('<a target="_blank" href="#" class="btn">Descargar</a>');
                    btn_download.set('href', data.lastInvoices[i].url_dte);
                    container.append(btn_download);

                    btn_download1 = this.Y.Node.create('<a target="_blank" href="#" data-invoice="' + data.lastInvoices[i].trx_number + '" class="btn">Ver Detalle</a>');
                    btn_download1.on('click', this.show_detail, this);
                    container.append(btn_download1);
                }
                /*
                        if(!RightNow.Interface.getConfig('CUSTOM_CFG_HIDE_BTN_DOWNLOAD_INVOICE_PAYMENTS') && data.lastInvoices[i].url_dte) {
                          btn_download = this.Y.Node.create('<a target="_blank" href="#" class="btn">Ver detalle</a>');
                          btn_download.set('href', data.lastInvoices[i].url_dte);
                          container.append(btn_download);
                        }
                */
                data.lastInvoices[i].trx_contract = this.contract_list.input.get('value');
                // debugger;
                if (is_pending) {
                    data.lastInvoices[i].trx_paid = 'Pendiente de Pago';
                    container.append(this.Y.Node.create('<br>'));
                    if (!RightNow.Interface.getConfig('CUSTOM_CFG_HIDE_BTN_PAY') && !RightNow.Interface.getConfig('CUSTOM_CFG_HIDE_BTN_PAY_INVOICE_PAYMENTS')) {
                        btn_pay = this.Y.Node.create('<a href="javascript:return void 0;" class="btn">Pagar</a>');
                        btn_pay.on('click', this.pay, data.lastInvoices[i]);
                        container.append(btn_pay);

                    }

                } else {
                    data.lastInvoices[i].trx_paid = 'Pagada';
                }
                data.lastInvoices[i].trx_urls = container;
            }



            Integer.fillTable(data.lastInvoices, this.table_LastInvoices_tbody);
            Integer.fillTable(data.lastPayments, this.table_LastPayments_tbody);
        } else {
            Integer.fillTable(null, this.table_LastInvoices_tbody);
            Integer.fillTable(null, this.table_LastPayments_tbody);
        }
    },

    /**
     * Permite redireccionar a la pantalla de pago
     * 
     * /cc/Transactions/initTransaction/invoice/{var_invoice}/amount/{var_amount}/contract_number/{var_contract}
     * https://dimacofi.getdte.cl/custodia_digital.php?id=S92083000-5_T33_F241527_H6dd1fc0de704acf05d31ac9aec948e09
     */
    pay: function (evt) {
        if (evt.target.getAttribute('disabled') != '') {
            return false;
        }

        // debugger;
        if (this.trx_number && this.trx_amount && this.trx_contract) {
            //window.location.href = '/cc/Transactions/initTransaction/invoice/' + this.trx_number + '/amount/' + this.ammount_remaining + '/contract_number/' + contrat;
            window.location.href = '/cc/Transactions_v2/initTransaction/invoice/' + this.trx_number + '/amount/' + this.amount_remaining;
        } else {
            return false;
        }

        return true;
    },

    /**
     * Completa la tabla de detalle de la vista previa del documento tributario
     */
    fillTablePreview: function (json) {
        var result = [];
        var keys = Object.keys(json);

        // // Convierte el JSON a Array
        // keys.forEach(function(key){
        //     result.key = key;
        //     result.push(json[key]);
        // });

        // Integer.fillTable(result, this.table_DetailPreview_tbody);

        return true;
    },

    /**
     * 
     */
    show_detail: function (evt) {
        evt.preventDefault();

        var contract = this.contract_list.input.get('value');
        var invoice = evt.target.getData('invoice');

        if (evt.target.getAttribute('disabled') != '') {
            return false;
        }

        this.getLastConsumptionsLines(contract, invoice);

        return true;
    },

    /**
     * Método inicial
     */
    init: function () {

        // Mapeo de elementos del DOM
        this.widget = this.Y.one(this.baseSelector);

        this.items_menuTabs = this.widget.all('.rn_MenuTabs ul li a');

        this.table_LastPayments = this.widget.one('.rn_Grid .rn_LastPayments');
        this.table_LastPayments_thead = this.table_LastPayments.one('thead');
        this.table_LastPayments_tbody = this.table_LastPayments.one('tbody');

        this.table_LastInvoices = this.widget.one('.rn_Grid .rn_LastInvoices');
        this.table_LastInvoices_thead = this.table_LastInvoices.one('thead');
        this.table_LastInvoices_tbody = this.table_LastInvoices.one('tbody');

        this.btn_summary = this.Y.one('.btn_summary');
        this.btn_summary.on('click', this.changeScreen, this);

        this.btn_downloadCSV = this.Y.one('.btn_downloadCSV');
        this.btn_downloadXLSX = this.Y.one('.btn_downloadXLSX');
        this.btn_downloadCSV.on('click', this.downloadCSV, this);
        this.btn_downloadXLSX.on('click', this.downloadXLSX, this);

        // Instancias
        this.contract_list = Integer.getInstanceByName('contract_list');
        this.ContentTab_Loading = this.widget.one('.rn_ContentTab_Loading');
        // Carga Listas
        Integer.appendOptions(this.contract_list, this.data.js.list.contracts, 'select', null, true);
        this.contract_list.input.set('selectedIndex', 1)
        this.changeContract();

        this.listPaymentsAndInvoices = this.Y.one('.rn_ListPaymentsAndInvoices');
        this.billDetailContent = this.Y.one('.rn_BillDetailContent');

        // Tabla Detalle Vista Previa
        // this.table_PreviewDetail       = this.Y.one('.rn_BillDetailContent table');
        // this.table_DetailPreview_tbody = this.table_PreviewDetail.one('tbody');

        // Tabla Detalle
        this.table_Detail = this.billDetailContent.one('table');
        this.table_Detail_tbody = this.table_Detail.one('tbody');

        // Subscipción de eventos
        // RightNow.Event.subscribe("evt_ChangeContract", this.contract_list, this);

        // Eventos
        this.contract_list.input.on('change', this.changeContract, this);
        this.items_menuTabs.on('click', this.changeTab, this);
    },

    /**
     * Alterna la vista de la vista previa de la factura y su detalle
     *
     * @param evt {Event}
     */
    changeScreen: function (show_detail) {
        show_detail = (typeof show_detail !== 'object') ? show_detail : false;

        if (show_detail) {
            this.listPaymentsAndInvoices.hide();
            this.billDetailContent.show();
        } else {
            this.listPaymentsAndInvoices.show();
            this.billDetailContent.hide();
        }

        return true;

    },

    /**
     * 
     */
    changeTab: function (e) {
        var item = e.target;
        var item_li = item.ancestor('li');

        this.widget.all('.rn_MenuTabs ul li').removeClass('active');
        item_li.addClass('active');

        var itemName = item_li.getData('name');

        this.widget.all('.rn_ContentTab').hide();
        this.widget.one('.rn_ContentTab_' + itemName).show();

        return true;
    },

    /**
     * 
     */
    changeContract: function (e) {
        // debugger;

        var sel = document.getElementsByName("contract_list");
        var text = sel[0].options[sel[0].selectedIndex].text;
        var datos = text.split(":");

        var value = this.contract_list.input.get('value');

        if (value) {
            this.ContentTab_Loading.show();
            this.getInvoicePaymentList(datos); // Información facturas y pagos
        }

        return true;
    },

    /** ################################################################################################################################################ */

    /**
     * Endpoint que obtiene el detalle del mes de las lineas asociadas a una factura
     */
    getLastConsumptionsLines: function (contract_number, invoice_number) {
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                contract_number: contract_number,
                invoice_number: invoice_number
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.getLastConsumptionsLines, eventObj.data, {
            successHandler: this.getLastConsumptionsLinesCallback,
            scope: this,
            data: eventObj,
            json: true,
            timeout: 60000,
        });

        this.changeScreen(true);
    },

    /**
     * Manejador del la respuesta del endpoint #getLastConsumptionsLines.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del método #getDefault_ajax_endpoint
     */
    getLastConsumptionsLinesCallback: function (response, originalEventObj) {
        // debugger;
        if (response.success) {
            window.last_response = response;
            // debugger;
            RightNow.Event.fire('evt_LoadDataTaxDocument', response);
        } else {
            RightNow.UI.Dialog.messageDialog(response.message, {
                icon: 'WARN'
            });
        }
    },

    /** ################################################################################################################################################ */
    /**
     * Endpoint que obtiene el consumo de los ultimos 6 meses
     */
    getInvoicePaymentList: function (value) {
        // debugger;
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                contract_number: value[0],
                rut: value[1]
            }
        });

        RightNow.Ajax.makeRequest(this.data.attrs.getInvoicePaymentList, eventObj.data, {
            successHandler: this.getInvoicePaymentListCallback,
            timeout: 120000,
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
    getInvoicePaymentListCallback: function (response, originalEventObj) {
        //debugger;
        console.debug(response);
        if (response.success) {
            window.last_response = response;

            //debugger;
            this.fillTables(response);
            // debugger;

        } else {
            RightNow.UI.Dialog.messageDialog(response.message, {
                icon: 'WARN'
            });
        }
        this.ContentTab_Loading.hide();
    },

    /**
     * Realiza la descar de la información de la tabla en CSV
     */
    downloadCSV: function () {
        var csv = Integer.table2CSV(this.table_Detail);

        download(csv, "reporte.csv", "text/plain");
    },
    /**
     * Realiza la descarga de la información de la tabla en XLSX
     */
    downloadXLSX: function () {
        var _data = Integer.table2Array(this.table_Detail);

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
    }


});