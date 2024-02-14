RightNow.namespace('Custom.Widgets.input.selectOrganization');

Custom.Widgets.input.selectOrganization = RightNow.Widgets.extend({

    /**
     * Widget constructor.
     */
    constructor: function()
    {
      // Mapeo de elementos del DOM
      this.widget           = this.Y.one(this.baseSelector);
      this.searchBox        = this.widget.one('#searchOrg');
      this.orgRow           = this.widget.one('.orgRow');
      this.inputOrg         = this.Y.one(this.baseSelector + ' .rn_Number');
      this.orgName          = this.widget.one('#orgName');
      this.searchBox.on('keyup', this.searchOrg, this);

    },


    searchOrg: function(e)
    {
      var value = e.target.get('value');
      if( value.length < 4 ) return;

      var data  = {};
      data.name = value;
      this.getOrganizationByName_ajax_endpoint(data);
    },

    /**
     * Makes an AJAX request for `getorganizationbyname_ajax_endpoint`.
     */
    getOrganizationByName_ajax_endpoint: function(params) {
        // Make AJAX request:
        var eventObj = new RightNow.Event.EventObject(this, {
          data:{
            w_id: this.data.info.w_id,
            data: JSON.stringify(params),
            // Parameters to send
        }});
        RightNow.Ajax.makeRequest(this.data.attrs.getOrganizationByName_ajax_endpoint, eventObj.data, {
            successHandler: this.getOrganizationByName_ajax_endpointCallback,
            scope:          this,
            data:           eventObj,
            json:           true
        });
    },

    /**
     * Handles the AJAX response for `getorganizationbyname_ajax_endpoint`.
     * @param {object} response JSON-parsed response from the server
     * @param {object} originalEventObj `eventObj` from #getGetorganizationbyname_ajax_endpoint
     */
    getOrganizationByName_ajax_endpointCallback: function(response, originalEventObj) {
      if (response.success) {
          if(!response.message){
              response.message = 'Creado con éxito.';
          }
          //Limpiar
          this.orgRow.setHTML("");
          var list = response.list;
          //cargar listas
          for (i = 0; i < list.length; i++)
          {
            this.orgRow.append('<li value='+list[i].ID+'>'+list[i].name+'</li>');
          }

          this.listOrgs = this.widget.all('.orgRow li');
          this.listOrgs.on('click', this.copyOrgValue, this);

      }
      else {
      }
    },

    copyOrgValue: function(e)
    {
      var orgId              = e.currentTarget._node.value;
      var textOrg            = e.currentTarget._node.textContent;
      
      this.inputOrg.set('value', orgId);
      this.orgName.set('value', textOrg);
    },

});
