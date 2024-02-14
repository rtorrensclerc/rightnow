RightNow.namespace('Custom.Widgets.login.LoginResetPassword');
Custom.Widgets.login.LoginResetPassword = RightNow.Widgets.extend({ 
    /**
     * Widget constructor.
     */
    constructor: function() {
      this.widget = this.Y.one(this.baseSelector);
      this.btn_reset = this.widget.one('button');

      this.btn_reset.on('click', this.reset_password_ajax_endpoint, this);
    },

    /**
     * Makes an AJAX request for `reset_password_ajax_endpoint`.
     */
    reset_password_ajax_endpoint: function() {
        // Make AJAX request:
        var eventObj = new RightNow.Event.EventObject(this, {data:{
            w_id: this.data.info.w_id,
            c_id: RightNow.Url.getParameter('u_id')
        }});
        RightNow.Ajax.makeRequest(this.data.attrs.reset_password_ajax_endpoint, eventObj.data, {
            successHandler: this.reset_password_ajax_endpointCallback,
            scope:          this,
            data:           eventObj,
            json:           true
        });
    },

    /**
     * Handles the AJAX response for `reset_password_ajax_endpoint`.
     * @param {object} response JSON-parsed response from the server
     * @param {object} originalEventObj `eventObj` from #reset_password_ajax_endpoint
     */
    reset_password_ajax_endpointCallback: function(response, originalEventObj) {
        // Handle response
    },

    /**
     * Renders the `view.ejs` JavaScript template.
     */
    renderView: function() {
        // JS view:
        var content = new EJS({text: this.getStatic().templates.view}).render({
            // Variables to pass to the view
            // display: this.data.attrs.display
        });
    }
});
