RightNow.namespace('Custom.Widgets.reports.DownloadReport');
Custom.Widgets.reports.DownloadReport = RightNow.Widgets.extend({
  /**
   * Widget constructor.
   */
  constructor: function () {
    // Mapping de elementos del DOM
    this.widget = this.Y.one(this.baseSelector);
    this.btns_download = this.widget.all(".btn_download");

    // Variables
    // this.errors = [];

    // Eventos
    if (this.btns_download) this.btns_download.on('click', this.handle_form_download, this);
  },

  /**
   * 
   */
  handle_form_download: function (e) {
    this.btn = e.target;
    this.type = this.btn.getData('type').toUpperCase();

    this.download_ajax_endpoint(this.type);
  },

  /**
   * 
   */
  download_ajax_endpoint: function (type) {
    // Make AJAX request:
    var eventObj = new RightNow.Event.EventObject(this, {
      data: {
        w_id: this.data.info.w_id,
        type: type
      }
    });
    RightNow.Ajax.makeRequest(this.data.attrs.download_ajax_endpoint, eventObj.data, {
      successHandler: this.download_ajax_endpointCallback,
      scope: this,
      data: eventObj
    });
  },

  /**
   * Handles the AJAX response for `download_ajax_endpoint`.
   * @param {object} response JSON-parsed response from the server
   * @param {object} originalEventObj `eventObj` from #download_ajax_endpoint
   */
  download_ajax_endpointCallback: function (response, originalEventObj) {
    if (this.type === 'CSV') {
      download(response.response, 'reporte.csv', 'text/csv');
    } else if (this.type === 'XLSX') {
      var wb = XLSX.utils.book_new();
      wb.Props = {
        Title: "Reporte",
        Subject: "Reporte",
        Author: "Integer",
        CreatedDate: new Date(2018, 10, 19)
      }

      wb.SheetNames.push("Registros");
      var ws = XLSX.utils.aoa_to_sheet(response[0]);
      wb.Sheets["Registros"] = ws;
      var wbout = XLSX.write(wb, {
        bookType: 'xlsx',
        type: 'binary'
      });

      download(new Blob([this.s2ab(wbout)], {
        type: "application/octet-stream"
      }), "Reporte.xlsx", "application/vnd.ms-excel");

      // download(response.response, 'reporte.xlsx', 'application/vnd.ms-excel');
    } else if (this.type === 'JSON') {
      download(response.response, 'reporte.json', 'application/json');
    } else {
    }
  },

  /**
   * download(new Blob(s2ab(wbout)), "Reporte.xlsx", "application/vnd.ms-excel");
   */
  s2ab: function (s) {
    var buf  = new ArrayBuffer(s.length);  //convert s to arrayBuffer
    var view = new Uint8Array(buf);        //create uint8array as viewer

    for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF; //convert to octet

    return buf;
  }
});
