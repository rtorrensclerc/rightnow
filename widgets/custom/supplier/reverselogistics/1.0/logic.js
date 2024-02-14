RightNow.namespace('Custom.Widgets.supplier.reverselogistics');
Custom.Widgets.supplier.reverselogistics = RightNow.Widgets.extend({
    /**
     * Formulario de solicitud de insumos
     */
    constructor: function () {
        if (this.data.attrs.read_only) {
            return false;
        }
        window.reverselogistics = this;
        this.widget = this.Y.one(this.baseSelector);
        this.incident_id = null;

        this.btn_submit = this.widget.one("#btn_submit");
          // Ejecuta `init` una vez realizada la carga de los widgets de entrada
          this.loadWidgets = window.setInterval((function (_parent) {
            return function () {
                var x = [];
                var y = [];
                RightNow.Event.fire('evt_GetInstanceByInputName', x, 'hh_brand_list');
                RightNow.Event.fire('evt_GetInstanceByInputName', y, 'dispatch_address');

                if (x.length &&  x.length) {
                    _parent.init();
                    window.clearInterval(_parent.loadWidgets);
                }
            }
        })(this), 100);
       
    },

     /**
     * Función inicial
     */
    init: function () {
        
        /* Mapeo de instancias
        this.hh_brand_list = Integer.getInstanceByName('hh_brand_list');
        this.contact_comment = Integer.getInstanceByName('Incident.Product');
        // Poblaminto de datos
        */
       debugger;
        this.hh_brand_list = Integer.getInstanceByName('hh_brand_list');
        this.dispatch_address = Integer.getInstanceByName('dispatch_address');
        this.Cantidad = Integer.getInstanceByName('Cantidad');
        this.btn_submit = this.widget.one("#btn_submit");
       
        
        if (this.data.js.list && this.data.js.list.list_dir) Integer.appendOptions(this.dispatch_address, this.data.js.list.list_dir, 'select', null, 'Elija Dirección');
        if (this.data.js.list && this.data.js.list.brands) Integer.appendOptions(this.hh_brand_list, this.data.js.list.brands, 'select', null, 'Elija Marca HH');

        // Eventos
        this.dispatch_address.input.on('change', this.handler_dispatch_address, this);
        this.hh_brand_list.input.on('change', this.handler_change_brand_hh_list, this);
        this.Cantidad.input.on('change', this.handler_change_Cantidad, this);
        this.btn_Submit.on('click', this.handler_btn_Submit, this);

     },

    /*
       var instanceName_marca = Y.one('input[name="Incident.CustomFields.c.marca_hh"]').get('id').split(/_Incident/)[0].replace('rn_','');
var instance_marca = RightNow.Widgets.getWidgetInstance(instanceName_marca);
instance_marca.input.set('value', 'Rodrigo');
    */
     /**
      * Cambia el valor de la HH a solicitar según el valor seleccionado de la lista
      *
      * @param e {event}
      */
     handler_change_brand_hh_list: function (e) {
         var value = e.target.get('value');
         debugger;
         if (value) {
            var instanceName_marca = Y.one('input[name="Incident.CustomFields.c.marca_hh"]').get('id').split(/_Incident/)[0].replace('rn_','');
            var instance_marca = RightNow.Widgets.getWidgetInstance(instanceName_marca);
            instathis.validform();nce_marca.input.set('value', value);
            this.validform()
         } 
         /*else {
             this.btn_submit.setDisabled(true);
         }*/
     },

     handler_change_Cantidad: function (e) {
        var value = e.target.get('value');
        debugger;
        if (value) {
           var instanceContador = Y.one('input[name="Incident.CustomFields.c.gasto"]').get('id').split(/_Incident/)[0].replace('rn_','');
           var instanceContador = RightNow.Widgets.getWidgetInstance(instanceContador);
           instanceContador.input.set('value', value);
           this.validform();
        } 
        /*else {
            this.btn_submit.setDisabled(true);
        }*/
    },
    
    handler_dispatch_address: function (e) {
        var value = e.target.get('value');
        debugger;
        if (value) {
           var instanceContador = Y.one('input[name="Incident.CustomFields.c.gasto"]').get('id').split(/_Incident/)[0].replace('rn_','');
           var instanceContador = RightNow.Widgets.getWidgetInstance(instanceContador);
           instanceContador.input.set('value', value);
           this.validform();

        } 
        /*else {
            this.btn_submit.setDisabled(true);
        }*/
    },
    validform: function (e)
    {
        this.hh_brand_list = Integer.getInstanceByName('hh_brand_list');
        this.dispatch_address = Integer.getInstanceByName('dispatch_address');
        this.Cantidad = Integer.getInstanceByName('Cantidad');
        hh_brand_list=this.hh_brand_list.input.get('value');
        dispatch_address=this.dispatch_address.input.get('value');
        Cantidad=this.Cantidad.input.get('value');

    },

    /**
     * Valida y obtiene la lista de insumos de la HH
     *
     * @param e {event}
     */
    handler_btn_Submit: function (e) {
        this.disabled_infoSupplier(true);

        var btn = e.target;
        data = {};

        // Datos Solicitud
        data.cont_bn = parseInt(this.hh_counter_bw.input.get('value'));
        data.cont_color = parseInt(this.hh_counter_color.input.get('value'));
        data.id_hh = parseInt(this.hh_selected.input.get('value'));

        // Datos de HH
        data.serial_hh = this.info_hh.respuesta.Serie;
        data.brand_hh = this.info_hh.respuesta.Marca;
        data.model_hh = this.info_hh.respuesta.Modelo;
        data.client_covenant = this.info_hh.respuesta.Convenio;
        data.client_blocked = this.info_hh.respuesta.Direccion.Bloqueado;
        data.contract_type = this.info_hh.respuesta.TipoContrato;
        data.sla_hh_rsn = this.info_hh.respuesta.SLA;
        data.delfos = this.info_hh.respuesta.delfos;
        data.machine_serial = this.info_hh.respuesta.Serie;
        data.supplier_covenant = this.info_hh.respuesta.convenio_insumos;
        data.brackets_covenant = this.info_hh.respuesta.convenio_corchetes;
        data.inventory_item_id = parseInt(this.info_hh.respuesta.inventory_item_id);
        data.suppliers = this.info_hh.respuesta.suppliers;
        data.trx_id_erp = this.info_hh.trx_id_erp;
        data.priorization = this.info_hh.respuesta.preferente;

        // Datos de contacto
        data.contact_name = this.contact_name.input.get('value');
        data.contact_phone = this.contact_phone.input.get('value');
        data.contact_comments = this.contact_comment.input.get('value');

        // Dirección
        data.dir_id = parseInt(this.dispatch_address.input.get('value'));

        if (!this.info_hh) {
            this.disabled_infoSupplier(false, true);
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_HH_ORG_SELECT"));
        }

        if (this.validate()) {
            this.disabled_infoHH(true);

            if (this.incident_id) {
                obj = {};
                obj.i_id = this.incident_id;

                this.getInfoIncident_ajax_endpoint(this.hh_saved);
            } else {
                this.createIncident_ajax_endpoint(data);
            }
        } else {
            this.disabled_infoSupplier(false, true);
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_COMPLETEALL_BEFORELIST"));
            return;
        }
    },

    /** #################################################################### */

    /**
     * Endpoint para crear un incidente
     *
     * @param {event} e Evento que invóca el método
     * @return {boolean}
     */
    createIncident_ajax_endpoint: function (params) {
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });
        debugger;
        RightNow.Ajax.makeRequest(this.data.attrs.createIncident_ajax_endpoint, eventObj.data, {
            successHandler: this.createIncident_ajax_endpointCallback,
            timeout: 60000,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    /**
     * Manejador del la respuesta del endpoint #createIncident_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del endpoint
     */
    createIncident_ajax_endpointCallback: function (response, originalEventObj) {
        if (response.success) {
            this.incident_id = response.id;
            this.incident_refNo = response.refNo;

            obj = {};
            obj.i_id = this.incident_id;

            this.getInfoIncident_ajax_endpoint(obj);
        } else {
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_FAIL_SUPPLY_HH"));
            this.incident_id = null;
            return false;
        }
    },
    /** #######################################################################
        UTILIDADES
    ######################################################################## */

    validate: function () {
        // Variables
        this.errors = [];
        this.errors_messages = [];
        this.is_valid = true;

        RightNow.Event.fire('evt_ValidateInput', this.errors);

        for (var error in this.errors) {
            if (!this.errors[error].valid) {
                this.errors_messages.push(this.errors[error].message);
                this.is_valid = false;
            }
        }

        if (!this.is_valid) {
            this.errors_container.one('.messages').setHTML('<p>' + this.errors_messages.join('</p><p>') + '</p>');
            this.errors_container.show();
            this.btn_submit.set('disabled', true);
            window.scrollTo(this.errors_container.getX(), this.errors_container.getY());
            return false;
        } else {
            this.errors_container.hide();
            this.errors_container.one('.messages').setHTML('');
        }

        return true;
    }

     
});