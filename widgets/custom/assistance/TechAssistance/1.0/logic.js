RightNow.namespace('Custom.Widgets.assistance.TechAssistance');

Custom.Widgets.assistance.TechAssistance = RightNow.Widgets.extend({
    /**
     * Formulario de solicitud de insumos
     */
    constructor: function () {
        if (this.data.attrs.read_only) {
            return false;
        }

        // Variables de configuración
        this.maxQtyItem = RightNow.Interface.getConfig('CUSTOM_CFG_MAX_QTY_ITEM');

        // Generales
        window.widget_tech_assistance = this;
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

        // Mensaje Pantalla de Éxito
        this.message_phone = this.widget.one('.message_phone');

        // Pantalla de Éxito
        this.screenForm_ReferenceNumber = this.screenSuccess.one('.rn_ReferenceNumber');
        this.btn_requests.on('click', this.handler_btn_requests, this);
        this.btn_new_request.on('click', this.handler_btn_new_request, this);

        // Información de la HH
        this.btn_get_hh = this.widget.one("#btn_get_hh");
        this.btn_get_hh.on('click', this.handler_btn_get_hh, this);

        // Variables
        this.errors = [];

        // Eventos
        this.btn_submit.on('click', this.handler_btn_submit, this);
        this.btn_cancel.on('click', this.handler_btn_cancel, this);

        // Iniciación de Mensajes
        RightNow.Interface.getMessage("CUSTOM_MSG_INPUT_REQUEST");
        RightNow.Interface.getMessage("CUSTOM_MSG_DETAIL_SUPPLY_REQUEST");
        RightNow.Interface.getMessage("CUSTOM_MSG_ERROR");
        RightNow.Interface.getMessage("CUSTOM_MSG_HH_INFORMATION");
        RightNow.Interface.getMessage("CUSTOM_MSG_HH_ORG_SELECT");
        RightNow.Interface.getMessage("CUSTOM_MSG_COMPLETEALL_BEFORELIST");
        RightNow.Interface.getMessage("CUSTOM_MSG_REQUEST_DENIED");
        RightNow.Interface.getMessage("CUSTOM_MSG_FAIL_SUPPLY_HH");
        RightNow.Interface.getMessage("CUSTOM_MSG_IMPOSSIBLE_TOCANCEL_ORDER");
        RightNow.Interface.getMessage("CUSTOM_MSG_HH_DOESNT_EXIST");
        RightNow.Interface.getMessage("CUSTOM_MSG_HH_WITHOUT_AGREEMENT");
        RightNow.Interface.getMessage("CUSTOM_MSG_INVALID_CONTRACT");
        RightNow.Interface.getMessage("CUSTOM_MSG_HH_NOAGREEMENT");
        RightNow.Interface.getMessage("CUSTOM_MSG_HH_BLOCKED");
        RightNow.Interface.getMessage("CUSTOM_MSG_HH_NOT_COMPANY_ADDRESS");
        RightNow.Interface.getMessage("CUSTOM_MSG_INKCOUNT_GREATERTHANPREVIOUS");
        RightNow.Interface.getMessage("CUSTOM_MSG_INKCOLOUR_GREATERTHANPREVIOUS");

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
     * Función inicial
     */
    init: function () {
        // Mapeo de instancias
        this.hh_brand_list = Integer.getInstanceByName('hh_brand_list');
        this.hh_selector = Integer.getInstanceByName('hh_selector');
        this.hh_selected = Integer.getInstanceByName('hh_selected');
        // this.hh_brand         = Integer.getInstanceByName('hh_brand');
        // this.hh_model         = Integer.getInstanceByName('hh_model');
        //this.hh_counter_bw = Integer.getInstanceByName('hh_counter_bw');
        //this.hh_counter_color = Integer.getInstanceByName('hh_counter_color');

        // Información de Despacho
        this.dispatch_address = Integer.getInstanceByName('dispatch_address');
        this.direccion_incorrecta = Integer.getInstanceByName('direccion_incorrecta');
        this.direccion_correcta = Integer.getInstanceByName('direccion_correcta');

        // Información de Contacto
        this.contact_name = Integer.getInstanceByName('contact_name');
        this.contact_phone = Integer.getInstanceByName('contact_phone');
        this.contact_email = Integer.getInstanceByName('contact_email');
        this.suggested_type = Integer.getInstanceByName('suggested_type');
        this.codigo_error = Integer.getInstanceByName('codigo_error');
        this.equipo_detenido_cliente = Integer.getInstanceByName('equipo_detenido_cliente');

        // this.contact_comment = Integer.getInstanceByName('contact_comment');
        this.contact_detail = Integer.getInstanceByName('contact_detail');

        // Poblaminto de datos
        if (this.data.js.list && this.data.js.list.brands) Integer.appendOptions(this.hh_brand_list, this.data.js.list.brands, 'select', null, 'Elija Marca HH');
        // if (this.data.js.list && this.data.js.list.list_dir) Integer.appendOptions(this.this.dispatch_address, this.data.js.list.list_dir, 'select', null, 'Elija Dirección');
        if (this.data.js.list && this.data.js.list.suggested_type) Integer.appendOptions(this.suggested_type, this.data.js.list.suggested_type, 'select', null, 'Elija Motivo');

        // Eventos
        this.hh_brand_list.input.on('change', this.handler_change_brand_hh_list, this);
        this.hh_selector.input.on('change', this.handler_hh_selector, this);
        this.direccion_incorrecta.input.on('change', this.handler_direccion_incorrecta, this);
        this.suggested_type.input.on('change', this.handler_suggested_type, this);
        // this.dispatch_address.input.on('change', this.handler_dispatch_address, this);

        // Steps
        RightNow.Event.fire('evt_AddStep', { "description": "Seleccione número de HH" });
        RightNow.Event.fire('evt_AddStep', { "description": "Información de solicitud" });
        RightNow.Event.fire('evt_AddStep', { "description": "Confirmación de solicitud" });
        RightNow.Event.fire('evt_ChangeStep', { "index": 1 });
    },

    handler_suggested_type: function (e) {
        var value = this.suggested_type.input.get('value');

        // var def_suggested_type = {"215":{"visit":false,"phone":true},"216":{"visit":false,"phone":true},"217":{"visit":false,"phone":true},"218":{"visit":false,"phone":true},"219":{"visit":false,"phone":true},"220":{"visit":true,"phone":false},"221":{"visit":false,"phone":true},"222":{"visit":false,"phone":true},"223":{"visit":false,"phone":true},"224":{"visit":false,"phone":true},"225":{"visit":false,"phone":true},"226":{"visit":true,"phone":false},"227":{"visit":false,"phone":true},"228":{"visit":true,"phone":false},"230":{"visit":false,"phone":true}};
        var def_suggested_type = JSON.parse(RightNow.Interface.getConfig('CUSTOM_CFG_PHONE_REQUEST_LIST'));
        if (def_suggested_type.hasOwnProperty(value) && def_suggested_type[value].phone) {
            this.message_phone.show();
        } else {
            this.message_phone.hide();
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
        window.location.href = '/app/sv/request/history';
    },

    /**
     * Evento del botón 'Realizar Otra Solicitud'
     *
     * @param e {event}
     */
    handler_btn_new_request: function (e) {
        window.location.href = '/app/sv/request/technical2';
    },

    /**
     * Evento del botón 'Obtener Datos de HH'
     *
     * @param e {event}
     */
    handler_btn_get_hh: function (e) {
        var hh = this.hh_selected.input.get('value');
        data = {};
        data.hh = hh;
        x = this.data.js.list.list_hh;

        for (var i = 0; i < x.length; i++) {
            if (x[i].ID === hh) {
                data.trx_id_erp = x[i].trx_id_erp;
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
        e.preventDefault();

        // Mapeo de elementos del DOM
        if (!this.validate()) {
            return false;
        }

        this.btn_submit.set('disabled', true);
        this.btn_cancel.set('disabled', true);

        // Variables
        data = {};
        data.i_id = this.incident_id;
        data.info_hh = this.info_hh;
        data.info_form = {};
        //data.info_form.bw_counter = parseInt(this.hh_counter_bw.input.get('value'));
        //data.info_form.color_counter = parseInt(this.hh_counter_color.input.get('value'));
        data.info_form.dispatch_address = parseInt(this.dispatch_address.input.get('value'));
        data.info_form.direccion_incorrecta = this.direccion_incorrecta.input.filter(function (obj) { return obj.checked; }).item(0).get('value');
        data.info_form.direccion_correcta = this.direccion_correcta.input.get('value');
        data.info_form.contact_name = this.contact_name.input.get('value');
        data.info_form.contact_phone = parseInt(this.contact_phone.input.get('value'));
        data.info_form.contact_email = this.contact_email.input.get('value');
        data.info_form.equipo_detenido_cliente = this.equipo_detenido_cliente.input.filter(function (obj) { return obj.checked; }).item(0).get('value');
        data.info_form.codigo_error = this.codigo_error.input.get('value');
        data.info_form.contact_detail = this.contact_detail.input.get('value');
        data.info_form.suggested_type = this.suggested_type.input.get('value');

        this.requestIncident_ajax_endpoint(data);

        return true;
    },

    /**
     * ?
     *
     * @param e {event}
     */
    handler_direccion_incorrecta: function (e) {
        var input = e.target;
        var value = input.get('value');

        if (value === '1') {
            this.Y.one(this.direccion_correcta.baseSelector).show();
        } else {
            this.Y.one(this.direccion_correcta.baseSelector).hide();
        }
    },

    /**
     * ?
     *
     * @param e {event}
     */
    // handler_dispatch_address: function(e) {
    // 	var input = e.target;
    // 	var value = input.get('value');

    // 	if (value !== '') {
    // 		this.disabled_infoContact(false);
    // 	}
    // },

    /**
     * Cancela la solictud
     *
     * @param e {event}
     */
    handler_btn_cancel: function (e) {
        window.location.reload();
    },

    /**
     * Cambia el valor de la HH a solicitar según el valor seleccionado de la lista
     *
     * @param e {event}
     */
    handler_change_brand_hh_list: function (e) {
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

    /**
     * Cambia el valor de la HH a solicitar según el valor seleccionado de la lista
     *
     * @param e {event}
     */
    handler_hh_selector: function (e) {
        var value = e.target.get('value');
        // e.target.set('value', ''); 
        if (value) {
            this.hh_selected.input.set('value', value);
            // supplier_link = this.Y.one('.rn_StepGroup.rn_Step2 .rn_Info a');
            // supplier_link_value = RightNow.Url.addParameter(supplier_link.get('href'), 'Incident.Threads', 'HH: ' + value + "\n", true);
            // supplier_link.set('href', supplier_link_value);
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
    requestIncident_ajax_endpoint: function (params) {
        this.disabled_infoHH(true, false, true);

        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.requestIncident_ajax_endpoint, eventObj.data, {
            successHandler: this.requestIncident_ajax_endpointCallback,
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
        if (response && response.hasOwnProperty('success') && response.success) {
            this.incident_id = response.id;
            this.incident_refNo = response.refNo;

            // this.screenForm.hide();
            // this.screenSuccess.show();
            this.screenSuccess.one('#p_refNo').one('a').setHTML('#' + this.incident_refNo);
            this.screenSuccess.one('#p_refNo').one('a').set('href', '/app/account/questions/detail/i_id/' + this.incident_id);
            this.screenSuccess.one('#p3').one('a').set('href', '/app/account/questions/detail/i_id/' + this.incident_id);

            RightNow.Event.fire('evt_ChangeStep', { "index": 3 });
        } else {
            this.btn_submit.set('disabled', false);
            this.btn_cancel.set('disabled', false);
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_REQUEST_DENIED"));
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
        if (!this.validate()) {
            this.dialog('Error', 'Complete el formulario antes de avanzar.');
            this.disabled_infoHH(false);

            return false;
        }

        this.btn_get_hh.set('value', 'Procesando...');
        // this.hh_brand.input.set('value', '');
        // this.hh_model.input.set('value', '');

        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });

        RightNow.Ajax.makeRequest(this.data.attrs.getHHDataSelected_ajax_endpoint, eventObj.data, {
            successHandler: this.getHHDataSelected_ajax_endpointCallback,
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
        this.btn_get_hh.set('value', 'Siguiente');
        RightNow.Event.fire('evt_EnableJump', { "index": 1, "enabled": true });

        this.disabled_infoHH(false);
        // TODO: Determinar si es impresora o PC, para saber qué lista presentar
        // Equipo: filter_computo_maquina
        // Computo: filter_computo
        //filter = (computo)?filter_computo:filter_computo_maquina;
        Integer.appendOptions(this.suggested_type, this.data.js.list.suggested_type, 'select', [216, 220, 227, 270, 272, 217, 226, 222, 271, 225, 271, 286],false);

        // Maneja un error en el servicio
        if (!response.success) {
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_HH_DOESNT_EXIST"));
            this.disabled_infoDispatch(true, true);

            this.info_hh = null;
            return;
        }

        this.info_hh = JSON.parse(response.response);
        // Valida que la HH solicitada esté en convenio
        if (!this.info_hh.respuesta.Convenio) {
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_HH_WITHOUT_AGREEMENT"));
            return;
        }

        // Valida que la HH solicitada esté en convenio
        // if (this.info_hh.respuesta.TipoContrato === 'Cargo') {
        //   this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_INVALID_CONTRACT"));
        // 	return;
        // }

        // Valida que la HH solicitada tenga un convenio de insumos
        // if (!this.info_hh.respuesta.convenio_insumos) {
        // 	this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_HH_NOAGREEMENT"));

        // 	return;
        // }

        // Valida que la HH solicitada no esté bloqueda
        /*if (this.info_hh.respuesta.Direccion.Bloqueado) {
            this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_HH_BLOCKED"));
            return;
        }*/
        debugger;
        if (this.data.js.list.HHbloqued === null) {
            found = false;
        }
        else {
            if (this.data.js.list.HHbloqued.HHBloqueoList.List) {
                if (this.data.js.list.HHbloqued.HHBloqueoList.List.data.HH == this.info_hh.respuesta.ID_HH) {
                    found = this.data.js.list.HHbloqued.HHBloqueoList.List.data;
                } else {
                    if (!this.data.js.list.HHbloqued.HHBloqueoList.List.data.HH) {
                        found = this.data.js.list.HHbloqued.HHBloqueoList.List.data.find(element => element.HH === this.info_hh.respuesta.ID_HH);
                    }
                    else {
                        found = false;
                    }
                }
            } else {
                found = false;
            }
        }




        if (found) {
            this.dialog_screen('Error', found);
            return;
        }

        // Valida que la dirección de la HH esté dentro de las posibles direcciones asociadas a su empresa,
        // en caso de existir la dirección se preselecciona en el campo dirección de los datos del despacho.
        // if (!this.dispatch_address.setSelectItemFromValue(this.info_hh.respuesta.Direccion.ID_direccion)) {
        // 	this.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_HH_NOT_COMPANY_ADDRESS"));
        // 	return;
        // }

        //20200820  RTC
        /*        for (var i_dir = 0; i_dir < this.data.js.list.list_dir.length; i_dir++) {
                    if (this.data.js.list.list_dir[i_dir].ID == this.info_hh.respuesta.Direccion.ID_direccion) {
                        this.dispatch_address.input.set('value', this.data.js.list.list_dir[i_dir].name);
                    }
                }
            */

        this.dispatch_address.input.set('value', this.info_hh.respuesta.Direccion.Direccion);
        //20200820  RTC

        RightNow.Event.fire('evt_ChangeStep', { "index": 2 });

        // this.hh_brand.input.set('value', this.info_hh.respuesta.Marca);
        // this.hh_model.input.set('value', this.info_hh.respuesta.Modelo);

        // this.hh_counter_bw.input.set('value',this.info_hh.respuesta.lastCounters.copia_bn);
        // this.hh_counter_color.input.set('value',this.info_hh.respuesta.lastCounters.copia_color);

        // this.hh_counter_bw.data.attrs.min_value    = 0;
        // this.hh_counter_bw.message_min_value       = RightNow.Interface.getMessage("CUSTOM_MSG_INKCOUNT_GREATERTHANPREVIOUS");
        // this.hh_counter_color.data.attrs.min_value = 0;
        // this.hh_counter_color.message_min_value    = RightNow.Interface.getMessage("CUSTOM_MSG_INKCOLOUR_GREATERTHANPREVIOUS");

        this.hh_saved = this.hh_selected.input.get('value');
        if (this.info_hh.trx_id_erp == 1662) {
            this.dialog_Pago('Información', this);
        }
        // this.disabled_infoHH(true);

    },

    /** #######################################################################
        UTILIDADES
    ######################################################################## */
    /**
        * Presenta un dialogo
        *
        * @param title {string} Título del dialogo
        * @param msg {string} Cuerpo del dialogo
        */
    dialog_Pago: function (title, obj) {
        var nodeDom = "<div id='rn_dialog_info'>";
        nodeDom += '<div class="rn_FieldDisplay rn_Output "  align="center">';
        //nodeDom += "<p>" + JSON.stringify(this.data.js.main) +"</p>";
        nodeDom += '<div><p ><h1>Le recordamos que para hacer efectiva esta solicitud debe  cancelar previamente la la visita Técnica.<br> Una vez generada la solicitud se le enviará un correo con las intrucciones para hacer el pago.</h1></p></div>';
        nodeDom += '<div><p ><h1>Desea Continuar</h1></p></div>';

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

    exitCallback: function () {
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
        }
    },
    CancelCallback: function () {
        document.location.reload();
    },
    destroy_dialog: function () {
        console.debug("destroy_dialog");
        live_box_button = this.Y.one('#rnDialog1');
        live_box = this.Y.one(".yui3-widget-mask");
        live_box.hide();
        live_box_button.hide();
        this._dialog.hide();
        return true;
    },
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
        clean = clean || false;
        resetSuppplier = resetSuppplier || false;

        this.hh_brand_list.setDisabled(disabled);
        // this.hh_brand.setDisabled(disabled);
        this.hh_selector.setDisabled(disabled);
        this.hh_selected.setDisabled(disabled);
        //this.hh_counter_bw.setDisabled(disabled);
        //this.hh_counter_color.setDisabled(disabled);
        /*
                if (clean) {
                    this.hh_counter_bw.input.set('value', '');
                    this.hh_counter_color.input.set('value', '');
                }
        */

        if (disabled) {
            this.disabled_infoDispatch(true, clean);
            this.btn_get_hh.set('disabled', true);
        } else {
            this.btn_get_hh.set('disabled', false);
        }
    },

    /**
     * Desabilita la sección del despacho
     *
     * @param disabled {boolean}
     * @param clean {boolean}
     */
    disabled_infoDispatch: function (disabled, clean) {
        clean = clean || false;

        // this.dispatch_address.setDisabled(disabled);

        // if (clean) {
        // 	this.dispatch_address.input.set('value', '');
        // }

        if (disabled) {
            this.disabled_infoContact(false, clean);
        }
    },

    /**
     * Desabilita la sección de información de contacto
     *
     * @param disabled {boolean}
     * @param clean {boolean}
     */
    disabled_infoContact: function (disabled, clean) {
        clean = clean || false;

        this.contact_name.setDisabled(disabled);
        this.contact_phone.setDisabled(disabled);
        this.contact_email.setDisabled(disabled);

        if (clean) {
            this.contact_name.input.set('value', '');
            this.contact_phone.input.set('value', '');
            this.contact_email.input.set('value', '');
        }
    },

    /**
     * Presenta un dialogo
     *
     * @param title {string} Título del dialogo
     * @param msg {string} Cuerpo del dialogo
     */
    dialog: function (title, msg) {
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
        var nodeDom = "<div id='rn_landing'>";

        debugger;
        nodeDom += '<div class="rn_FieldDisplay rn_Output "  align="center">';
        //nodeDom += "<p>" + JSON.stringify(this.data.js.main) +"</p>";

        nodeDom += '<div>';
        nodeDom += '<p ><h1>Nuestros Sistemas indican que existen restricciones para generar solicitudes para ';
        nodeDom += 'HH : ' + hhinfo.HH + '</h1></p>';
        nodeDom += '<p >La sucursal  <h1>" ' + hhinfo.DIR + '"</h1> se encuentra bloqueda para solicitar Servicio</p> ';
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