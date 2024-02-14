RightNow.namespace('Custom.Widgets.payments.TaxDocumentPreview');

Custom.Widgets.payments.TaxDocumentPreview = RightNow.Widgets.extend({

  /**
   * Contructor del Widget
   */
  constructor: function () {
    // Variables
    Integer.__TaxDocumentPreview = this;

    this.var_invoice  = 0;
    this.var_amount   = 0;
    this.var_contract = 0;

    // Ejecuta `init` una vez realizada la carga de los widgets de entrada
    this.init();
  },

  /**
   * Método inicial
   */
  init: function () {

    // Mapeo de elementos del DOM
    this.widget           = this.Y.one(this.baseSelector);
    this.btn_detail       = this.widget.one('.btn_detail');
    this.btn_summary      = this.Y.one('.btn_summary');
    this.btn_pay          = this.widget.one('.btn_pay');
    this.btn_download     = this.widget.one('.btn_download');
    this.btn_downloadCSV  = this.Y.one('.btn_downloadCSV');
    this.btn_downloadXLSX = this.Y.one('.btn_downloadXLSX');

    this.billingPaymentsContent = this.Y.one('.rn_BillingPaymentsContent');
    this.billDetailContent      = this.Y.one('.rn_BillDetailContent');

    // Tabla Detalle
    this.table_Detail       = this.billDetailContent.one('table');
    this.table_Detail_tbody = this.table_Detail.one('tbody');

    // Tabla Detalle Vista Previa
    this.table_PreviewDetail       = this.widget.one('.rn_DetailContent table');
    this.table_DetailPreview_tbody = this.table_PreviewDetail.one('tbody');

    // Subscipción de eventos

    RightNow.Event.subscribe("evt_LoadDataTaxDocument", this.loadDataTaxDocument, this);

    // Eventos
    this.btn_detail.on('click', this.changeScreen, this);
    if(this.btn_pay) this.btn_pay.on('click', this.pay, this);
    this.btn_summary.on('click', this.changeScreen, this);
    this.btn_downloadCSV.on('click', this.downloadCSV, this);
    this.btn_downloadXLSX.on('click', this.downloadXLSX, this);
  },

  /**
   * Realiza la descarga de la información de la tabla en XLSX
   */
  downloadXLSX: function() {
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
    var buf  = new ArrayBuffer(s.length);  //convert s to arrayBuffer
    var view = new Uint8Array(buf);        //create uint8array as viewer

    for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF; //convert to octet

    return buf;
  },

  /**
   * Realiza la descar de la información de la tabla en CSV
   */
  downloadCSV: function() {
    var csv = Integer.table2CSV(this.table_Detail);

    download(csv, "reporte.csv", "text/plain");
  },

  /**
   * Permite redireccionar a la pantalla de pago
   *
   * /cc/Transactions/initTransaction/invoice/{var_invoice}/amount/{var_amount}/contract_number/{var_contract}
   * https://dimacofi.getdte.cl/custodia_digital.php?id=S92083000-5_T33_F241527_H6dd1fc0de704acf05d31ac9aec948e09
   */
  pay: function(evt) {


    if(evt.target.getAttribute('disabled') != ''){
      return false;
    }

    if(this.var_invoice && this.var_amount && this.var_contract) {
      //window.location.href = '/cc/Transactions/initTransaction/invoice/' + this.trx_number + '/amount/' + this.ammount_remaining + '/contract_number/' + contrat;
      window.location.href = '/cc/Transactions_v2/initTransaction/invoice/' + this.trx_number + '/amount/' + this.ammount_remaining ;
    } else {
      return false;
    }

    return true;

  },

  /**
   * Alterna la vista de la vista previa de la factura y su detalle
   *
   * @param evt {Event}
   */
  changeScreen: function(evt) {
    var btn = evt.target;

    if(btn.hasClass('btn_detail')) {
      this.billingPaymentsContent.hide()
      this.billDetailContent.show()
    } else {
      this.billingPaymentsContent.show()
      this.billDetailContent.hide()
    }

    return true;

  },

  /**
   * Realiza la carga de información para la vista previa de la factura
   *
   * @param evt {Event}
   */
  loadDataTaxDocument: function(evt, arr_args) {
    if(!arr_args[0].success) {
      return false;
    }

    this.data.js._data = arr_args[0].detail;

    //this.disable_pay = this.data.js._data.header.amount_remaining?((parseInt(this.data.js._data.header.amount_remaining.replace(/\D/g,'')) > 0)?true:false):0;
    this.disable_pay = this.data.js._data.header.amount_remaining?((parseInt(this.data.js._data.header.amount_remaining.toString().replace(/\D/g,'')) > 0)?true:false):0;

    if(this.btn_pay) this.btn_pay.set('disabled', this.disable_pay);



    // Declaración de variables
    this.header_customer_name   = this.widget.all('.header_customer_name');
    this.header_customer_number = this.widget.all('.header_customer_number');
    this.header_giro            = this.widget.all('.header_giro');
    this.header_invoice_number  = this.widget.all('.header_invoice_number');
    this.header_invoice_addr    = this.widget.all('.header_invoice_addr');
    this.header_due_date        = this.widget.all('.header_due_date');
    this.header_amount_clp      = this.widget.all('.header_amount_clp');
    this.header_iva             = this.widget.all('.header_iva');
    this.header_total           = this.widget.all('.header_total');

    // Asignación de Variables
    this.header_customer_name.setHTML(this.data.js._data.header.customer_name);
    this.header_customer_number.setHTML(this.data.js._data.header.customer_number);
    this.header_giro.setHTML(this.data.js._data.header.giro);
    this.header_invoice_number.setHTML(this.data.js._data.header.invoice_number);
    this.header_invoice_addr.setHTML(this.data.js._data.header.invoice_addr);
    this.header_due_date.setHTML(this.data.js._data.header.trx_date);

    this.fillTablePreview(this.data.js._data.summary_lines);
    var total_neto = Integer.number_format(Math.round(parseFloat(this.data.js._data.header.amount_remaining)), 0, ',', '.', '$ ');
    var total_iva  = Integer.number_format(Math.round(parseFloat(this.data.js._data.header.amount_remaining) * 0.19), 0, ',', '.', '$ ');
    var total      = Integer.number_format(Math.round(parseFloat(this.data.js._data.header.amount_remaining) + (parseFloat(this.data.js._data.header.amount_remaining) * 0.19)), 0, ',', '.', '$ ');

    this.var_invoice  = this.data.js._data.header.invoice_number;
    this.var_amount   = Math.round(parseFloat(this.data.js._data.header.amount_remaining) + (parseFloat(this.data.js._data.header.amount_remaining) * 0.19));
 
    this.var_contract = this.data.js._data.header.contract_number;

    this.header_amount_clp.setHTML(total_neto);
    this.header_iva.setHTML(total_iva);
    this.header_total.setHTML(total);

    this.loadDataDetail();

    // Asigna URL al botón descargar
    this.btn_download.set('href', this.data.js._data.header.url_dte);

    return true;
  },

  /**
   * Completa la tabla de detalle de la vista previa del documento tributario
   */
  fillTablePreview: function(json) {
      var result = [];
      var keys   = Object.keys(json);

      // Convierte el JSON a Array
      keys.forEach(function(key){
          result.key = key;
          result.push(json[key]);
      });

      Integer.fillTable(result, this.table_DetailPreview_tbody);

      return true;
  },

  /**
   * Realiza la carga del detalle de la factura
   */
  loadDataDetail: function() {
    var data = this.data.js._data;

    var arr_hh = [];

    for (var i = 0; i < data.lines.length; i++) {
      if(data.header.divisa !== 'CLP') {
        if(arr_hh.indexOf(data.lines[i].hh) === -1) {
          data.lines[i].fixed_amount = parseFloat(data.lines[i].fixed_amount) * parseFloat(data.header.exchange_rate);
          arr_hh.push(data.lines[i].hh);
        } else {
          data.lines[i].fixed_amount = 0;
        }
      }
    }

    Integer.fillTable(data.lines, this.table_Detail_tbody);

    return true;
  }

});

