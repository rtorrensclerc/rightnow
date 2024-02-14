RightNow.namespace('Custom.Widgets.supplier.SupplierRequest');

Custom.Widgets.supplier.SupplierRequest = RightNow.Widgets.extend({
    /**
     * Formulario de solicitud de insumos
     */
    constructor: function () {
        if (this.data.attrs.read_only) {
            return false;
        }
        window.SupplierRequest = this;
        // Variables de configuración
        this.maxQtyItem = RightNow.Interface.getConfig('CUSTOM_CFG_MAX_QTY_ITEM');

        // Generales
        this.widget = this.Y.one(this.baseSelector);
        this.form = this.widget.one("form");
        this.errors_container = this.widget.one('#rn_ErrorLocation');
        this.message_form = this.widget.one("#message_form");
        this.btn_cancel = this.widget.one("#btn_cancel");
        this.btn_submit = this.widget.one("#btn_submit");
        this.btn_requests = this.widget.one("#btn_requests");
        this.btn_new_request = this.widget.one("#btn_new_request");
        this.info_hh = null;
        this.incident_id = null;
        this.incident_refNo = null;
        this.items = {};
        this.hh_saved = null;

        // Pantallas
        this.screenForm = this.widget.one('.rn_ScreenForm');
        this.screenSuccess = this.widget.one('.rn_ScreenSuccess');

        // Pantalla de Éxito
        this.screenForm_ReferenceNumber = this.screenSuccess.one('.rn_ReferenceNumber');
        this.btn_requests.on('click', this.handler_btn_requests, this);
        this.btn_new_request.on('click', this.handler_btn_new_request, this);

        // Tabla de insumos
        this.supplierItems_table = this.widget.one('#supplierItems');
        this.supplierItem_templateRow = this.supplierItems_table.one('.templateRow');
        this.supplierItem_initialRow = this.supplierItems_table.one('.initialRow');

        this.pending_request_list = document.getElementsByClassName('pending_request_list')[0];
        // 👇️ hides element (still takes up space on the page)
        this.pending_request_list.style.visibility = 'hidden';


        // Información de la HH
        this.btn_get_hh = this.widget.one("#btn_get_hh");
        this.btn_get_hh.on('click', this.handler_btn_get_hh, this);

        // Información de Despacho
        this.btn_get_list_supplier = this.widget.one("#btn_get_list_supplier");
        this.btn_get_list_supplier.on('click', this.handler_btn_get_list_supplier, this);

        // Variables
        this.errors = [];

        // Eventos
        this.btn_submit.on('click', this.handler_btn_submit, this);
        this.btn_cancel.on('click', this.handler_btn_cancel, this);

        // Iniciación de Mensajes
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_SELECT_NUMBER_OF_HH");
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_REQUEST_INFORMATION");
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_REQUEST_CONFIRMATION");

        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_HH_ORG_SELECT");
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_COMPLETEALL_BEFORELIST");
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_REQUEST_DENIED");
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_FAIL_SUPPLY_HH");
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_IMPOSSIBLE_TOCANCEL_ORDER");
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_HH_DOESNT_EXIST");
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_HH_WITHOUT_AGREEMENT");
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_INVALID_CONTRACT");
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_HH_NOAGREEMENT");
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_HH_BLOCKED");
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_HH_NOT_COMPANY_ADDRESS");
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_INKCOUNT_GREATERTHANPREVIOUS");
        RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_INKCOLOUR_GREATERTHANPREVIOUS");
        // Ejecuta `init` una vez realizada la carga de los widgets de entrada
        this.loadWidgets = window.setInterval((function (_parent) {
            return function () {
                var x = [];
                RightNow.Event.fire('evt_GetInstanceByInputName', x, 'hh_selector');

                if (x) {
                    _parent.init();
                    window.clearInterval(_parent.loadWidgets);
                }
            }
        })(this), 100);
    },

    /**
     * Agrega los insumos a la tabla
     *
     * @param data {array}
     */
    addSupplierItems: function (data) {
        var template = this.supplierItem_templateRow.getHTML();

        for (var i = data.length - 1; i >= 0; i--) {
            var row = this.Y.Node.create('<tr data-id ="' + data[i].lineId + '">' + template + '</tr>');
            var cells = row.all('td');

            this.items[data[i].lineId] = 0;
            cells.item(0).setHTML(data[i].part_number); // Número de Parte

            if (data[i].alias) {
                cells.item(1).setHTML('<strong>' + data[i].alias + '</strong><br>' + data[i].name); // Descripción
            } else {
                cells.item(1).setHTML('<strong>' + data[i].name + '</strong>'); // Descripción
            }

            cells.item(2).one('.qty').setHTML('0'); // Cantidad
            this.supplierItems_table.one('tbody').append(row);
        }

        // Evento de botones
        var btn_actions = this.supplierItems_table.one('tbody').all('.action');
        btn_actions.detach('click');
        btn_actions.on('click', this.handler_btn_actions, this);
    },

    /**
     * Función inicial
     */
    init: function () {

        /** Get Instances */
        this.hh_brand_list = Integer.getInstanceByName('hh_brand_list');

        // Información de la HH
        this.hh_selector = Integer.getInstanceByName('hh_selector');
        this.hh_selected = Integer.getInstanceByName('hh_selected');
        this.hh_counter_bw = Integer.getInstanceByName('hh_counter_bw');
        this.hh_counter_color = Integer.getInstanceByName('hh_counter_color');
        this.dispatch_address = Integer.getInstanceByName('dispatch_address');

        // Información de Contacto
        this.contact_name = Integer.getInstanceByName('contact_name');
        this.contact_phone = Integer.getInstanceByName('contact_phone');
        this.contact_comment = Integer.getInstanceByName('contact_comment');

        // Poblamiento de datos
        //debugger;

        if (this.data.js.list && this.data.js.list.list_dir) Integer.appendOptions(this.dispatch_address, this.data.js.list.list_dir, 'select', null, 'Elija Dirección');
        if (this.data.js.list && this.data.js.list.brands) Integer.appendOptions(this.hh_brand_list, this.data.js.list.brands, 'select', null, 'Elija Marca HH');

        // Eventos
        this.hh_selector.input.on('change', this.handler_hh_selector, this);
        this.dispatch_address.input.on('change', this.handler_dispatch_address, this);
        this.hh_brand_list.input.on('change', this.handler_change_brand_hh_list, this);
        //this.hh_selector.input.on('change', this.handler_hh_selector, this);

        // Steps
        RightNow.Event.fire('evt_AddStep', { "description": RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_SELECT_NUMBER_OF_HH") });
        RightNow.Event.fire('evt_AddStep', { "description": RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_REQUEST_INFORMATION") });
        RightNow.Event.fire('evt_AddStep', { "description": 'Selector de insumos' });
        RightNow.Event.fire('evt_AddStep', { "description": RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_REQUEST_CONFIRMATION") });
        RightNow.Event.fire('evt_ChangeStep', { "index": 1 });


    },

    /**
     * Cambia el valor de la HH a solicitar según el valor seleccionado de la lista
     *
     * @param e {event}
     */
    handler_change_brand_hh_list: function (e) {
        //console.debug("handler_change_brand_hh_list");
        var value = e.target.get('value');
     
        if (value) {
            this.hh_selector.setDisabled(false);
            this.hh_selected.setDisabled(false);

            this.arr_brands_hh = [];
            if (this.data.js.list.brands_hh.hasOwnProperty(value)) {
                this.arr_brands_hh = this.data.js.list.brands_hh[value];
            }

            Integer.appendOptions(this.hh_selector, this.arr_brands_hh, 'select', null, false);
        } else {
            this.hh_selector.setDisabled(true);
            this.hh_selected.setDisabled(true);
        }
    },

    /** #######################################################################
        EVENTOS DE BOTONES
    ######################################################################## */
    /**
     * Evento del botón 'Ver Mis Solicitudes'
     *
     * @param e {event}
     */
    handler_btn_requests: function (e) {
        console.debug("handler_btn_requests");
        window.location.href = '/app/sv/request/history';
    },

    /**
     * Evento del botón 'Realizar Otra Solicitud'
     *
     * @param e {event}
     */
    handler_btn_new_request: function (e) {
        console.debug("handler_btn_new_request");
        window.location.href = '/app/sv/supplier/form';
    },

    /**
     * Evento del botón 'Obtener Datos de HH'
     *
     * @param e {event}
     */
    handler_btn_get_hh: function (e) {
        console.debug("handler_btn_get_hh");
        var hh = this.hh_selected.input.get('value');
        data = {};
        data.hh = hh;
        x = this.data.js.list.list_hh;

        for (var i = 0; i < x.length; i++) {
            if (x[i].ID === hh) {
                data.trx_id_erp = x[i].trx_id_erp;
            }
        }


        if (data.trx_id_erp == '1662') {

        }
        else {
            // Mapeo de elementos del DOM
            if (!this.validate()) {
                return false;
            }
        }

        this.disabled_infoHH(true);

        this.getHHDataSelected_ajax_endpoint(data);
    },

    /**
     * Suma o resta cantidades al insumo
     *
     * @param e {event}
     */
    handler_btn_actions: function (e) {
        console.debug("handler_btn_actions");
        var btn = (e.target.ancestor('.action')) ? e.target.ancestor('.action') : e.target;
        var qty = btn.ancestor('td').one('.qty');
        var actualQty = parseInt(qty.get('text'));
        var idItem = btn.ancestor('tr')._node.getAttribute('data-id');
        var newQty = actualQty;
        var validform = false;

        if (btn.hasClass('subtract') && actualQty >= 1) {
            newQty--;
        } else if (btn.hasClass('add') && actualQty < this.maxQtyItem) {
            newQty++;
        }

        this.items[idItem] = newQty;

        for (item in this.items) {
            if (this.items[item] > 0) {
                validform = true;
            }
        }

        this.btn_submit.set('disabled', !validform);
        qty.set('text', newQty);

        return true;
    },

    /**
     * Envía el formulario de solicitud
     *
     * @param e {event}
     */
    handler_btn_submit: function (e) {
        console.debug("handler_btn_submit");
        e.preventDefault();
        this.btn_submit.set('disabled', true);
        this.btn_cancel.set('disabled', true);
        this.disabled_infoSupplier(true);

        // Mapeo de elementos del DOM
        if (!this.validate()) {
            return false;
        }

        // Variables
        data = {};
        data.i_id = this.incident_id;
        data.lines_items = [];

        for (item in this.items) {
            data.lines_items.push({
                'id': item,
                'quantity_selected': parseInt(this.items[item])
            });
        }

        this.requestIncident_ajax_endpoint(data);

        return true;
    },

    /**
     * Envía el formulario de solicitud
     *
     * @param e {event}
     */
    handler_dispatch_address: function (e) {
        console.debug("handler_dispatch_address");
        var input = e.target;
        var value = input.get('value');

        if (value !== '') {
            this.disabled_infoContact(false);
        }
    },

    /**
     * Valida y obtiene la lista de insumos de la HH
     *
     * @param e {event}
     */
    handler_btn_get_list_supplier: function (e) {
        console.debug("handler_btn_get_list_supplier");
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

    /**
     * Cancela la solictud
     *
     * @param e {event}
     */
    handler_btn_cancel: function (e) {
        console.debug("handler_btn_cancel");
        if (this.incident_id) {
            data = {};
            data.i_id = this.incident_id;

            this.disabled_infoHH(true, true, true);
            this.cancelIncident_ajax_endpoint(data);
        } else {
            this.disabled_infoHH(true, true, true);
            window.location.reload();
        }

    },

    /**
     * Cambia el valor de la HH a solicitar según el valor seleccionado de la lista
     *
     * @param e {event}
     */
    handler_hh_selector: function (e) {
        console.debug("handler_hh_selector");

        var value = e.target.get('value');
        if (value) {
            this.hh_selected.input.set('value', value);
            //this.pending_request_list = Integer.getInstanceByName('pending_request_list');


            /* this.pending_request_list = document.getElementsByClassName('pending_request_list')[0];
             // hides element (still takes up space on the page)
             this.pending_request_list.style.visibility = '';
 
             var hh = this.hh_selected.input.get('value');
             data = {};
             data.hh = hh;
 
             this.requestpending_request_list_ajax_endpoint(data);
            */ 
            hh = value;
            trx_id_erp = '';
            x = this.data.js.list.list_hh;

            for (var i = 0; i < x.length; i++) {
                if (x[i].ID === hh) {
                    trx_id_erp = x[i].trx_id_erp;
                }
            }


            /*
            if (trx_id_erp == '1662') {
                this.dialog_cobro('Información', this);
            }
            */



        }
    },

    /** #######################################################################
            SERVICIOS
        ######################################################################## */

    /**
     * Endpoint para obtener la lista de insumos asociada a la HH
     *
     * @param {event} e Evento que invóca al método
     * @return {boolean}
     */
    requestpending_request_list_ajax_endpoint: function (params) {
        console.debug("requestpending_request_list_ajax_endpoint");
        //this.disabled_infoHH(true, false, true);

        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.requestpending_request_list_ajax_endpoint, eventObj.data, {
            successHandler: this.requestpending_request_list_ajax_endpointCallback,
            timeout: 60000,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    /**
     * Manejador del la respuesta del endpoint #requestpending_request_list_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del endpoint
     */
    requestpending_request_list_ajax_endpointCallback: function (response, originalEventObj) {
        console.debug("requestpending_request_list_ajax_endpointCallback");
        //this.dialog('Error', RightNow.Interface.getMessage("prueba"));

    },


    /**
     * Endpoint para obtener la lista de insumos asociada a la HH
     *
     * @param {event} e Evento que invóca al método
     * @return {boolean}
     */
    requestIncident_ajax_endpoint: function (params) {
        console.debug("requestIncident_ajax_endpoint");
        this.disabled_infoHH(true, false, true);

        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.requestIncident_ajax_endpoint, eventObj.data, {
            successHandler: this.requestIncident_ajax_endpointCallback,
            timeout: 60000,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    /**
     * Manejador del la respuesta del endpoint #requestIncident_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del endpoint
     */
    requestIncident_ajax_endpointCallback: function (response, originalEventObj) {
        console.debug("requestIncident_ajax_endpointCallback");
        if (response.success) {
            this.disabled_infoSupplier(true);
            this.screenForm_ReferenceNumber.one('a').setHTML(this.incident_refNo);
            this.screenForm_ReferenceNumber.one('a').set('href', '/app/sv/supplier/form/i_id/' + this.incident_id);

            RightNow.Event.fire('evt_ChangeStep', { "index": 4 });
        } else {
            this.disabled_infoSupplier(false);
            this.btn_submit.set('disabled', false);
            this.btn_cancel.set('disabled', false);
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_REQUEST_DENIED"));
        }
    },

    /** #################################################################### */

    /**
     * Endpoint para obtener la lista de insumos asociada a la HH
     *
     * @param {event} e Evento que invóca el método
     * @return {boolean}
     */
    getInfoIncident_ajax_endpoint: function (params) {
        console.debug("getInfoIncident_ajax_endpoint");
        this.disabled_infoHH(true, false);
        this.disabled_infoSupplier(false, false);

        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });
        // debugger;

        RightNow.Ajax.makeRequest(this.data.attrs.getInfoIncident_ajax_endpoint, eventObj.data, {
            successHandler: this.getInfoIncident_ajax_endpointCallback,
            timeout: 60000,
            scope: this,
            data: eventObj,
            json: true
        });
    },
         /**
         * Presenta un dialogo
         *
         * @param title {string} Título del dialogo
         * @param msg {string} Cuerpo del dialogo
         */
    dialog_cobro2: function (title, obj,prediction,items) {
        console.debug("dialog_cobro");
        var nodeDom = "<div id='rn_dialog_info'>";

        debugger;
        nodeDom += '<div class="rn_FieldDisplay rn_Output "  align="center">';
        //nodeDom += "<p>" + JSON.stringify(this.data.js.main) +"</p>";
        nodeDom += '<div>';
        nodeDom += '<p><h1>' ;
        nodeDom += 'Recuerde que esta solicitud será facturada de acuerdo al convenio que mantiene vigente con las siguentes tarifas: </h1>' ;
        nodeDom += '</p></div>';
        
        datos_convenio=JSON.parse(prediction);
        nodeDom += '<p>' ;
     
        datos_convenio.valores.values.forEach(valor => {
            find=0;
            items.forEach(item =>{
                if(item.InventoryItemId==valor.inventory_item_id)
                {
                    find=1;
                }
            }

            )
            if (valor.VALOR_CONVENIO > 0 && find>0) {
                    pesos_value=new Intl.NumberFormat('en-DE').format( Math.round(valor.VALOR_CONVENIO*valor.CONVERSION_RATE));
                    nodeDom += '<p>' + valor.CODIGO_PRODUCTO + ' (' + valor.DESCRIPCION_PRODUCTO + ') ' +  pesos_value+'</p>';

                
            }
            
        })
        
        
        nodeDom += '</p></div>';
        
        nodeDom += '</div>';
        nodeDom += '</div>';

        dialogDiv = this.Y.Node.create(nodeDom);
        var dialogOptions = {
            'cssClass': 'rn_showDialog_dialog'
            ,
            "buttons": [
                {
                    text: "Aceptar",
                    handler: { scope: this, fn: this.exitCallback }
                }, {
                    text: "Cancelar",
                    handler: { scope: this, fn: this.CancelCallback }
                }]
        };

        this._dialog = RightNow.UI.Dialog.actionDialog('Atención', dialogDiv, dialogOptions);

        this._dialog.show();

    },
    /**
         * Presenta un dialogo
         *
         * @param title {string} Título del dialogo
         * @param msg {string} Cuerpo del dialogo
         */
    dialog_cobro: function (title, obj) {
        console.debug("dialog_cobro");
        var nodeDom = "<div id='rn_dialog_info'>";


        nodeDom += '<div class="rn_FieldDisplay rn_Output "  align="center">';
        //nodeDom += "<p>" + JSON.stringify(this.data.js.main) +"</p>";
        nodeDom += '<div>';

        nodeDom += '<p ><h1>Recuerde que esta solicitud será facturada de acuerdo al convenio que mantiene vigente </h1>';
        nodeDom += '</p></div>';
        nodeDom += '</div>';
        nodeDom += '</div>';

        dialogDiv = this.Y.Node.create(nodeDom);
        var dialogOptions = {
            'cssClass': 'rn_showDialog_dialog'
            ,
            "buttons": [
                {
                    text: "Aceptar",
                    handler: { scope: this, fn: this.exitCallback }
                }, {
                    text: "Cancelar",
                    handler: { scope: this, fn: this.CancelCallback }
                }]
        };

        this._dialog = RightNow.UI.Dialog.actionDialog('Atención', dialogDiv, dialogOptions);

        this._dialog.show();

    },
    /**
     * Presenta un dialogo
     *
     * @param title {string} Título del dialogo
     * @param msg {string} Cuerpo del dialogo
     */
    dialog_insumos: function (title, items, total, obj) {
        console.debug("dialog_insumos");
        var nodeDom = "<div id='rn_dialog_info'>";
        this.total = total;

        nodeDom += '<div class="rn_FieldDisplay rn_Output "  align="center">';
        //nodeDom += "<p>" + JSON.stringify(this.data.js.main) +"</p>";
        nodeDom += '<div>';
        if (total == 0) {
            nodeDom += '<p ><h1>No se pueden generar solicitudes pare este equipo. <br>Existen solicitudes pendientes para todos los Insumos relacionados.</h1>';
        }
        else {
            nodeDom += '<p ><h1>Existen Ticket en curso con Insumos ya solicitados.</h1>';
        }
        ticket = '';
        items.forEach(item => {
            if (item.quantity_selected > 0) {


                //items_paralelos = items_paralelos + ' <br> ' + item.ReferenceNumber + ':' + element.quantity_selected + '-' + item.name;
                if (ticket != item.ReferenceNumber) {
                    nodeDom += '<br><b>' + item.ReferenceNumber + ' (' + item.Estado + ') </b>';

                }
                ticket = item.ReferenceNumber;
                nodeDom += '<br> ' + item.quantity_selected + '-' + item.name;
            }
        }
        );



        nodeDom += '</p></div>';
        nodeDom += '</div>';
        nodeDom += '</div>';


        dialogDiv = this.Y.Node.create(nodeDom);
        var dialogOptions = {
            'cssClass': 'rn_showDialog_dialog'
            ,
            "buttons": [{
                text: "Aceptar",
                handler: { scope: this, fn: this.exitCallback }
            }]
        };

        this._dialog = RightNow.UI.Dialog.actionDialog('Atención', dialogDiv, dialogOptions);

        this._dialog.show();

    },
    CancelCallback: function () {
        console.debug("CancelCallback");
        document.location.reload();
    },
    /**
     * 
     *  exitCallback  funcion para validar cierre de livebox
     * 
     */
    exitCallback: function () {
        console.debug("exitCallback");
        // debugger;
        if (this.total == 0) {
            data = {};
            data.i_id = this.incident_id;
            this.cancelIncident_ajax_endpoint(data);
        }
        else {
            //this.test = document.getElementById('rn_dialog_info');
            //this.btn_request = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_request"]');
            // this.test = document.getElementById('rnDialog1');
            // this.test.hidden = true;

            //this.beta   = this.widget.one('.tab_beta');
            //this.gamma  = this.widget.one('.tab_gamma');

            //this.x = this.Y.Node.one('#rnDialog1');
            //this.x.hide()
            this.destroy_dialog();
            this.disabled_infoDispatch(false);
            this.disabled_infoContact(false, true);
        }
    },
    /**
     * 
     *  destroy_dialog se encarga de cerrar dialogo y cerrar livebox
     * 
     */
    destroy_dialog: function () {
        console.debug("destroy_dialog");
        live_box_button = this.Y.one('#rnDialog1');
        live_box = this.Y.one(".yui3-widget-mask");
        live_box.hide();
        live_box_button.hide();
        this._dialog.hide();
        return true;
    },
    /**
     * Manejador del la respuesta del endpoint #getInfoIncident_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del endpoint
     */
    getInfoIncident_ajax_endpointCallback: function (response, originalEventObj) {
        console.debug("getInfoIncident_ajax_endpointCallback");
        // HH ejemplo color con contrato 1122603
        items_paralelos = '';
        i = 0;
        if (response.tickets_paralelos.ticket_paralelo > 0) {
            console.log(response.tickets_paralelos.items_paralelo.length);

            response.tickets_paralelos.items_paralelo.forEach(element => {
                response.response.items.forEach(item => {
                    if (element.part_number == item.part_number && element.quantity_selected > 0) {
                        console.log(item.part_number);
                        i = i + 1;
                        //items_paralelos = items_paralelos + ' <br> ' + element.ReferenceNumber + ':' + element.quantity_selected + '-' + item.name;
                        var index = response.response.items.indexOf(item);
                        if (index > -1) {
                            response.response.items.splice(index, 1);
                        }

                    }
                })

            });


            if (i) {
                this.dialog_insumos('Información', response.tickets_paralelos.items_paralelo, response.response.items.length, this);


            }


            this.addSupplierItems(response.response.items);
        }
        else {
            this.addSupplierItems(response.response.items);
        }
        //response.response.predictiondata

        if (trx_id_erp == '1662') {

            this.dialog_cobro2('Información', this,response.response.predictiondata,response.response.items);
        }

        RightNow.Event.fire('evt_ChangeStep', { "index": 3 });

        this.btn_submit.set('disabled', true);
    },

    /** #################################################################### */

    /**
     * Endpoint para crear un incidente
     *
     * @param {event} e Evento que invóca el método
     * @return {boolean}
     */
    createIncident_ajax_endpoint: function (params) {
        console.debug("createIncident_ajax_endpoint");
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });
        // debugger;
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
        console.debug("createIncident_ajax_endpointCallback");
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

    /** #################################################################### */

    /**
     * Endpoint para crear cancelar una solicitud
     *
     * @param {event} e Evento que invóca el método
     * @return {boolean}
     */
    cancelIncident_ajax_endpoint: function (params) {
        console.debug("cancelIncident_ajax_endpoint");
        this.btn_cancel.set('disabled', true);

        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.cancelIncident_ajax_endpoint, eventObj.data, {
            successHandler: this.cancelIncident_ajax_endpointCallback,
            timeout: 60000,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    /**
     * Manejador del la respuesta del endpoint #cancelIncident_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del endpoint
     */
    cancelIncident_ajax_endpointCallback: function (response, originalEventObj) {
        console.debug("cancelIncident_ajax_endpointCallback");
        if (response.success) {
            window.location.reload();
        } else {
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_IMPOSSIBLE_TOCANCEL_ORDER"));
            this.btn_cancel.set('disabled', false);
        }
    },

    /** #################################################################### */

    /**
     * Endpoint para obtener la información de la HH seleccionada
     *
     * @param {event} e Evento que invóca el método
     * @return {boolean}
     */
    getHHDataSelected_ajax_endpoint: function (params) {
        console.debug("getHHDataSelected_ajax_endpoint");
        this.btn_get_hh.set('value', RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_PROCESSING"));

        // this.btn_get_hh.set('value', 'Obtener datos de otra HH');
        // this.hh_brand.input.set('value', '');
        // this.hh_model.input.set('value', '');
        //debugger;
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });

        RightNow.Ajax.makeRequest(this.data.attrs.getHHDataSelected_ajax_endpoint, eventObj.data, {
            successHandler: this.getHHDataSelected_ajax_endpointCallback,
            timeout: 60000,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    /**
     * Manejador del la respuesta del endpoint #getHHDataSelected_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del endpoint
     */
    getHHDataSelected_ajax_endpointCallback: function (response, originalEventObj) {

        console.debug("getHHDataSelected_ajax_endpointCallback");
        this.btn_get_hh.set('value', RightNow.Interface.getMessage("NEXT_LBL"));
        this.disabled_infoHH(false);

        // Maneja un error en el servicio
        if (!response.success) {
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_HH_DOESNT_EXIST"));
            this.disabled_infoDispatch(true, true);
            this.info_hh = null;
            return;
        }
        //debugger;
        this.info_hh = JSON.parse(response.response);

        // Valida que la HH solicitada esté en convenio
        if (!this.info_hh.respuesta.Convenio) {
            // this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_HH_WITHOUT_AGREEMENT"));
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_HH_NOAGREEMENT"));
            return;
        }

        // Valida que la HH solicitada esté en convenio
        if (this.info_hh.respuesta.TipoContrato === 'Cargo') {
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_INVALID_CONTRACT"));
            return;
        }

        // Valida que la HH solicitada tenga un convenio de insumos
        if (!this.info_hh.respuesta.convenio_insumos) {
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_HH_NOAGREEMENT"));

            return;
        }
        //debugger;

        if (this.data.js.list.HHbloqued) {
            if (this.data.js.list.HHbloqued.HHBloqueoList) {
                if (this.data.js.list.HHbloqued.HHBloqueoList.List) {
                    if (this.data.js.list.HHbloqued.HHBloqueoList.List.data.HH) {
                        found = this.data.js.list.HHbloqued.HHBloqueoList.List.data;

                    } else {
                        found = this.data.js.list.HHbloqued.HHBloqueoList.List.data.find(element => element.HH === this.info_hh.respuesta.ID_HH);
                    }
                } else {
                    found = false;
                }
            }
            else {
                found = false;
            }
        }
        else {
            found = false;
        }

        //debugger;
        if (found && this.info_hh.respuesta.ID_HH == found.HH) {
            this.dialog_screen('Error', found);
            return;
        }
        // Valida que la HH solicitada no esté bloqueda
        // esta validación no debera estar aca , ya que se bloquea el cliente al estar el SAI Bloqueado
        /*if (this.info_hh.respuesta.Direccion.Bloqueado) {
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_HH_BLOCKED"));
            return;
        }*/

        // Valida que la dirección de la HH esté dentro de las posibles direcciones asociadas a su empresa,
        // en caso de existir la dirección se preselecciona en el campo dirección de los datos del despacho.
        if (!this.dispatch_address.setSelectItemFromValue(this.info_hh.respuesta.Direccion.ID_direccion)) {
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_HH_NOT_COMPANY_ADDRESS"));
            return;
        }

        // this.hh_brand.input.set('value', this.info_hh.respuesta.Marca);
        // this.hh_model.input.set('value', this.info_hh.respuesta.Modelo);

        this.hh_counter_bw.data.attrs.min_value = this.info_hh.lastCounter.bn;
        this.hh_counter_bw.message_min_value = RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_INKCOUNT_GREATERTHANPREVIOUS");
        this.hh_counter_color.data.attrs.min_value = this.info_hh.lastCounter.color;
        this.hh_counter_color.message_min_value = RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_INKCOLOUR_GREATERTHANPREVIOUS");
        this.priorizacion = this.info_hh.respuesta.preferente;

        if (this.info_hh.trx_id_erp != '1662') {

            if (this.hh_counter_bw.input.get('value') < this.info_hh.lastCounter.bn) {
                this.dialog('Error', this.hh_counter_bw.message_min_value);
                this.is_valid = false;
                RightNow.Event.fire('evt_ChangeStep', { "index": 1 });

                return false;
            }

            if (this.hh_counter_color.input.get('value') < this.info_hh.lastCounter.color) {
                this.dialog('Error', this.hh_counter_color.message_min_value);
                this.is_valid = false;

                return false;
            }
        }
        /*if (this.info_hh.ticket_paralelo > 0) {
            this.dialog('Error', "Actualmente existe un ticket " + this.info_hh.Subject_ticket_paralelo + " en curso [" + this.info_hh.ref_ticket_paralelo + " ] para HH [" + this.info_hh.respuesta.ID_HH + "]");
            this.is_valid = false;

            return false;
        }*/

        RightNow.Event.fire('evt_ChangeStep', { "index": 2 });

        this.disabled_infoDispatch(false);
        this.disabled_infoContact(false);
        this.disabled_infoSupplier(false, true);

        this.hh_saved = this.hh_selected.input.get('value');


    },

    /** #######################################################################
        UTILIDADES
    ######################################################################## */

    validate: function () {
        console.debug("validate");
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
    },

    /**
     * Desabilita la sección de la HH
     *
     * @param disabled {boolean}
     * @param clean {boolean}
     * @param resetSuppplier {boolean}
     */
    disabled_infoHH: function (disabled, clean, resetSuppplier) {
        console.debug("disabled_infoHH");
        clean = clean || false;
        resetSuppplier = resetSuppplier || false;

        this.hh_selector.setDisabled(disabled);
        this.hh_selected.setDisabled(disabled);
        // this.hh_counter_bw.setDisabled(disabled);
        // this.hh_counter_color.setDisabled(disabled);

        if (clean) {
            this.hh_counter_bw.input.set('value', 0);
            this.hh_counter_color.input.set('value', 0);
        }

        if (disabled) {
            this.disabled_infoDispatch(true, clean);
            this.btn_get_hh.set('disabled', true);
        } else {
            this.btn_get_hh.set('disabled', false);
        }

        if (resetSuppplier) {
            this.disabled_infoSupplier(true, true);
        }
    },

    /**
     * Desabilita la sección del despacho
     *
     * @param disabled {boolean}
     * @param clean {boolean}
     */
    disabled_infoDispatch: function (disabled, clean) {
        console.debug("disabled_infoDispatch");
        clean = clean || false;

        this.dispatch_address.setDisabled(disabled);

        if (clean) {
            this.dispatch_address.input.set('value', '');
            this.disabled_infoSupplier(true, true);
        }

        if (disabled) {
            this.disabled_infoContact(true, clean);
        }
    },

    /**
     * Desabilita la sección de información de contacto
     *
     * @param disabled {boolean}
     * @param clean {boolean}
     */
    disabled_infoContact: function (disabled, clean) {
        console.debug("disabled_infoContact");

        clean = clean || false;

        this.contact_name.setDisabled(disabled);
        this.contact_phone.setDisabled(disabled);
        this.contact_comment.setDisabled(disabled);

        if (clean) {
            this.contact_name.input.set('value', '');
            this.contact_phone.input.set('value', '');
            this.contact_comment.input.set('value', '');
        }
    },

    /**
     * Desabilita la sección de pedidos
     *
     * @param disabled {boolean}
     * @param clean {boolean}
     */
    disabled_infoSupplier: function (disabled, clean) {
        console.debug("disabled_infoSupplier");
        clean = clean || false;

        if (disabled) {
            this.btn_get_list_supplier.set('disabled', true);
            this.supplierItems_table.all('.action').detach('click');
            this.supplierItems_table.all('.action').addClass('rn_Disabled');
        } else {
            this.btn_get_list_supplier.set('disabled', false);
            this.supplierItems_table.all('.action').on('click', this.handler_btn_actions, this);
            this.supplierItems_table.all('.action').removeClass('rn_Disabled');
        }

        if (clean) {
            this.supplierItem_initialRow.show();
        } else {
            this.supplierItem_initialRow.hide();
        }
    },

    /**
     * Presenta un dialogo
     *
     * @param title {string} Título del dialogo
     * @param msg {string} Cuerpo del dialogo
     */
    dialog: function (title, msg) {
        console.debug("dialog");
        title = title | 'Alerta';

        RightNow.UI.Dialog.messageDialog(msg, {
            title: title
        });

        return true;
    },


    /**
     * Presenta un dialogo
     *
     * @param title {string} Título del dialogo
     * @param msg {string} Cuerpo del dialogo
     */
    dialog_screen: function (title, hhinfo) {
        console.debug("dialog_screen");
        var nodeDom = "<div id='rn_landing'>";


        nodeDom += '<div class="rn_FieldDisplay rn_Output "  align="center">';
        //nodeDom += "<p>" + JSON.stringify(this.data.js.main) +"</p>";

        nodeDom += '<div>';
        nodeDom += '<p ><h1>Nuestros Sistemas indican que existen restricciones para generar solicitudes para ';
        nodeDom += 'HH : ' + hhinfo.HH + '</h1></p>';
        nodeDom += '<p >La sucursal  <h1>" ' + hhinfo.DIR + '"</h1> se encuentra bloqueda para solicitar Servicio o Insumos</p> ';
        nodeDom += '<p> Bloqueado por Crédito favor contactar a <B>credito@dimacofi.cl</B> </p>';

        nodeDom += '</div>';
        nodeDom += '</div>';
        nodeDom += '</div>';


        dialogDiv = this.Y.Node.create(nodeDom);
        var dialogOptions = {
            'cssClass': 'rn_showDialog_dialog',
            exitCallback: function () {
                document.location.reload();
            }
        };

        this._dialog = RightNow.UI.Dialog.actionDialog('Atención', dialogDiv, dialogOptions);

        this._dialog.show();

    }

    
});