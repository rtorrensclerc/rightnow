RightNow.namespace('Custom.Widgets.pdf.pdfAttacher2');
Custom.Widgets.pdf.pdfAttacher2 = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {

      this.widget  = this.Y.one(this.baseSelector);
      this.loading = this.widget.one("#loading");
      this.message = this.widget.one(".caja");
      if (this.Y.one('.submit'))
      {
          this.Y.one('.submit').on('click', this.loader, this);
      }
    },

    /**
     * Sample widget method.
     */
    loader: function(e) {
      //e.preventDefault();
      this.loading.addClass('loader');
      this.message.addClass('hide');
      this.Y.one('.submit').addClass('hide');
    }
});
