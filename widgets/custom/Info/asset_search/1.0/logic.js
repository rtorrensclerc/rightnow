RightNow.namespace('Custom.Widgets.Info.asset_search');
Custom.Widgets.Info.asset_search = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {
        window.asset_search = this;
        this.widget = this.Y.one(this.baseSelector);
        this.btn_search = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_search"]');
        this.btn_change = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_change"]');
        if (this.btn_search) this.btn_search.on("click", this._btn_searchHH, this);
        if (this.btn_change) this.btn_change.on("click", this._btn_change, this);

    },
    _btn_change: function(e) {

        data = {};
        data.rut = document.getElementById("txt_new_rut").value;


        this.setOrganization_ajax_endpoint(data);
        return true;

    },
    _btn_searchHH: function(e) {
        //Debe validar si esta seleccionado e. estado
        //document.location.reload();

        this._setOrder(e, 1);



    },
    /**
     * Realiza el llamado al servicio setOrder.
     *
     * Tipos de Solicitud
     * 1 ->  Crear
     * 2 ->  Actualizar
     * 3 ->  Enviar
     *
     * @param {Event} Mouse Event
     * @param {Integer} Tipo de Solicitud
     */
    _setOrder: function(e, action) {


        data = {};


        //debugger;
        data.valor_hh = document.getElementById("valor_hh").value;
        data.valor_serie = document.getElementById("valor_serie").value;
        data.valor_serie = document.getElementById("valor_serie").value;
        this.btn_save = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_search"]');

        document.getElementById("btn_search").disabled = true;
        data.rut = this.data.js.org_rut;
        data.org_id = this.data.js.org_id;
        this.getAsset_search_ajax_endpoint(data);
        return true;


    },
    /**
     * Sample widget method.
     */
    methodName: function() {

    },

    /**
     * Makes an AJAX request for `asset_search_ajax_endpoint`.
     */
    getAsset_search_ajax_endpoint: function(params) {
        // Make AJAX request:
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                // Parameters to send
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.asset_search_ajax_endpoint, eventObj.data, {
            successHandler: this.asset_search_ajax_endpointCallback,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    /**
     * Handles the AJAX response for `asset_search_ajax_endpoint`.
     * @param {object} response JSON-parsed response from the server
     * @param {object} originalEventObj `eventObj` from #getAsset_search_ajax_endpoint
     */
    asset_search_ajax_endpointCallback: function(response, originalEventObj) {
        // Handle response
        i = response;

        if (response.id == "1") {


            document.getElementById("customer_info").style.display = 'block';
            if (response.rut !== this.data.js.arut) {
                document.getElementById("customer_info3").style.display = 'block';
            }
            document.getElementById("customer_info2").style.display = 'none';


            Integer.getInstanceByName("hh_selected1").input._node.value = response.org_name;
            Integer.getInstanceByName("hh_selected2").input._node.value = response.rut;
            Integer.getInstanceByName("hh_selected3").input._node.value = response.dir;
            Integer.getInstanceByName("hh_selected4").input._node.value = response.ebs_comuna;
            Integer.getInstanceByName("hh_selected5").input._node.value = response.ebs_region;
            Integer.getInstanceByName("hh_selected6").input._node.value = response.Nombre_Equipo;
            Integer.getInstanceByName("hh_selected7").input._node.value = response.Serie;
            Integer.getInstanceByName("hh_selected8").input._node.value = data.rut;
            document.getElementById("txt_new_rut").value = response.rut;
            var s_ch = "Cambia a: ";
            document.getElementById('btn_change').value = s_ch.concat(response.rut);
        }
        if (response.id == "0") {
            document.getElementById("customer_info2").style.display = 'block';
            document.getElementById("customer_info").style.display = 'none';

        }
        document.getElementById("btn_search").disabled = false;

    },
    /**
     * Makes an AJAX request for `setOrganization_ajax_endpoint`.
     */
    setOrganization_ajax_endpoint: function(params) {

        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,

                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.setorganization_ajax_endpoint, eventObj.data, {
            successHandler: this.setorganization_ajax_endpointCallback,
            scope: this,
            data: eventObj,
            json: true
        });
    },
    /**
     * Manejador del la respuesta del endpoint #setOrganization_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del endpoint
     */
    setorganization_ajax_endpointCallback: function(response, originalEventObj) {


        document.location.reload();

    }
});