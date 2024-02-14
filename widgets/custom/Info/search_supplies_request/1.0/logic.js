RightNow.namespace('Custom.Widgets.Info.search_supplies_request');
Custom.Widgets.Info.search_supplies_request = RightNow.Widgets.extend({     /**
     * Widget constructor.
     */
    constructor: function () {
        // Variables

        window.search_supplies_request = this;
        this.widget = this.Y.one(this.baseSelector);
        this.btn_downloadXLSX = this.Y.one('.btn_downloadXLSX');
        this.btn_downloadXLSX.on('click', this.handler_btn_downloadXLSX, this);

        this.btn_get_trx = this.Y.one('#btn_get_trx');
        this.btn_get_trx.on('click', this.handler_btn_get_trx, this);


        this.btn_erase = this.widget.one("#btn_erase");
        this.btn_erase.on('click', this.handler_btn_erase, this);

        this.table_Lines = this.widget.one('.rn_Grid .rn_search_transaccions');
        this.table_Lines_thead = this.table_Lines.one('thead');
        this.table_Lines_tbody = this.table_Lines.one('tbody');



        this.btn_downloadXLSX.hide();
        this.ContentTab_Loading = this.widget.one('.rn_ContentTab_Loading');
        this.ContentTab_Loading.hide();
        this.no_data = this.widget.one('.no_data');


        this.items = {};
        this.hh_saved = null;
        console.log("obteniendo instancias por nombre...");
        
        // Ejecuta `init` una vez realizada la carga de los widgets de entrada
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

        // Instancias

        // this.HH = Integer.getInstanceByName('HH');


        this.machine = Integer.getInstanceByName("HH");
        this.serie = Integer.getInstanceByName("Serie");
        this.machine.input.on("keypress",this.chequer,this);
        this.serie.input.on("keypress",this.chequer,this);
        
        

        /*this.status_list = Integer.getInstanceByName('status_list');
        // Carga Listas
        Integer.appendOptions(this.status_list, this.data.js.list.Status, 'select', null, true);
       // this.status_list.input.set('selectedIndex', 1)
       */
        // Instancias
        //this.status_list = Integer.getInstanceByName('status_list');
        // Carga Listas
        //Integer.appendOptions(this.status_list, this.data.js.Estados, 'select', null, true);
        //this.contract_list.input.set('selectedIndex', 1)
        this.handler_btn_get_trx();


    },
    chequer:function(e){
        // debugger;
        if (e.keyCode === 13) {
            this.handler_btn_get_trx();
        }
    },
    /**
     *  Borra los elementos 
     */
    handler_btn_erase: function () {
        this.btn_downloadXLSX.hide();
        this.btn_get_trx.set('disabled', false);


        this.HH = Integer.getInstanceByName('HH');
        this.HH.input.set('value', '');

        this.Serie = Integer.getInstanceByName('Serie');
        this.Serie.input.set('value', '');

        Integer.fillTable(null, this.table_Lines_tbody);
    },
    /**
     * 
     */
    handler_btn_get_trx: function () {
        data = {};

        this.ContentTab_Loading.show();
        this.btn_get_trx.set('disabled', true);


        this.HH = Integer.getInstanceByName('HH');
        data.HH = this.HH.input.get('value');

        this.Serie = Integer.getInstanceByName('Serie');
        data.Serie = this.Serie.input.get('value');

        //this.status_list = Integer.getInstanceByName('status_list');
        //data.status_list = this.status_list.input.get('value');

        this.getTrx_ajax_endpoint(data);
        this.btn_get_trx.set('disabled', false);

    },
    /**
     * 
     */
    handler_btn_downloadXLSX: function () {

        this.btn_downloadXLSX.hide();
        this.downloadXLSX();
    },
    downloadXLSX: function () {
        var _data = this.table2Array(this.table_Lines);

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
   * Makes an AJAX request for `asset_search_ajax_endpoint`.
   */
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

    /**
     * Manejador del la respuesta del endpoint #getInvoicePaymentList.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del método #getDefault_ajax_endpoint
     */
    getTrx_ajax_endpointCallback: function (response, originalEventObj) {


        this.ContentTab_Loading.hide();

        //Integer.fillTable(response.Trx_data, this.table_Lines_tbody);
        this.fillTables(response.Trx_data);
        this.btn_downloadXLSX.show();
        // this.btn_get_trx.set('disabled', false);


    },
    fillTables: function (data) {


        if (data) {

            Integer.fillTable(data, this.table_Lines_tbody);

        } else {
            Integer.fillTable(null, this.table_Lines_tbody);


        }
    },
    table2Array: function (table) {
        var arr_ = [];
        var table_thead = table.one('thead');
        var table_tbody = table.one('tbody');
        var td = table_tbody.all('tr');
        var count = td.size();

        arr_.push(table_thead.one('tr').all('th').get('text'));
        var z = 0;
        for (var i = 0; i < count; i++) {
            row = td.item(i);
            if (row.hasClass('template') || row.hasClass('template_child') || row.hasClass('no_data') || row._isHidden())
                continue;
            arr_.push(row.all('td').get('text'));
            z++;
        }

        return arr_;
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
    }
});