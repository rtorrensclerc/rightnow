RightNow.namespace('Custom.Widgets.supplier.SupplierRequestMultiple');

Custom.Widgets.supplier.SupplierRequestMultiple = RightNow.Widgets.extend({
    /**
     * Formulario de solicitud de insumos
     */

    constructor: function() {
        if (this.data.attrs.read_only) {
            return false;
        }

        window.form = this;

        //
        this.dataset = null;

        // Variables de configuración
        this.maxQtyItem = RightNow.Interface.getConfig('CUSTOM_CFG_MAX_QTY_ITEM');

        // Generales
        this.widget = this.Y.one(this.baseSelector);
        this.form = this.widget.one(".rn_ScreenForm");
        this.errors_container = this.widget.one('#rn_ErrorLocation');
        this.btn_submit = this.widget.one("#btn_submit");
        this.btn_submit2 = this.widget.one("#btn_submit2");
        this.btn_requests = this.widget.one("#btn_requests");
        this.btn_new_request = this.widget.one("#btn_new_request");
        this.upload_input = this.widget.one(".upload_box input");

        this.incident_refNo = null;
        this.items = {};

        // Pantallas
        this.screenForm = this.widget.one('.rn_ScreenForm');
        this.screenSuccess = this.widget.one('.rn_ScreenSuccess');

        // this.screenForm.show();
        // this.screenSuccess.hide();

        // Pantalla de Éxito
        this.screenForm_ReferenceNumber = this.screenSuccess.one('.rn_ReferenceNumber');

        this.btn_requests.on('click', this.handler_btn_requests, this);
        this.btn_new_request.on('click', this.handler_btn_new_request, this);

        // Tabla de errores
        this.errors_supplierMultipleItems_table = this.widget.one('#errors_supplierMultipleItems');
        this.errors_supplierMultipleItems_tbody = this.errors_supplierMultipleItems_table.one('tbody');
        this.errors_supplierMultipleItems_templateRow = this.errors_supplierMultipleItems_tbody.one('.template');
        this.errors_supplierMultipleItems_initialRow = this.errors_supplierMultipleItems_tbody.one('.no_data');

        // Tabla de insumos
        this.items_supplierMultipleItems_table = this.widget.one('#items_supplierMultipleItems');
        this.items_supplierMultipleItems_tbody = this.items_supplierMultipleItems_table.one('tbody');
        this.items_supplierMultipleItems_templateRow = this.items_supplierMultipleItems_tbody.one('.template');
        this.items_supplierMultipleItems_templateChildRow = this.items_supplierMultipleItems_tbody.one('.template_child');
        this.items_supplierMultipleItems_initialRow = this.items_supplierMultipleItems_tbody.one('.no_data');
        this.ContentTab_Loading = this.widget.one('.rn_ContentTab_Loading');
        // Botón Subir CSV
        this.btn_upload_csv = this.widget.one("#btn_upload_csv");
        this.btn_upload_csv2 = this.widget.one("#btn_upload_csv2");
        this.btn_upload_csv2.on('click', this.upload2, this);
        // Variables
        this.errors = [];
        this.noerrors = [];



        // Eventos
        this.btn_upload_csv.on('click', this.upload, this);
        this.btn_submit.on('click', this.handler_btn_submit, this);
        this.btn_submit2.on('click', this.handler_btn_submit2, this);

        // Subscipción de eventos
        RightNow.Event.subscribe("evt_SendFile2", this.handler_btn_upload_csv2, this);
        RightNow.Event.subscribe("evt_SendFile", this.handler_btn_upload_csv, this);
        RightNow.Event.subscribe("evt_Table_DeleteRow", this.delete_row, this);
        RightNow.Event.subscribe("evt_Table_NumberWidgetAction", this.change_number, this);

        // Init
        this.init();

        // Iniciación de Mensajes
        // RightNow.Interface.getMessage("CUSTOM_MSG_XXX");

        // Ejecuta `init` una vez realizada la carga de los widgets de entrada
        // this.loadWidgets = window.setInterval((function (_parent) {
        //   return function () {
        //     var x = [];

        //     RightNow.Event.fire('evt_GetInstanceByInputName', x, 'hh_selector');

        //     if (x) {
        //       _parent.init();
        //       window.clearInterval(_parent.loadWidgets);
        //     }
        //   }
        // })(this), 100);
    },


    /**
     * Se ejecuta al cambiar el valor de la dirección
     */
    change_dir: function(e, arr_args) {
        var node = e.target;
        var cell = node.ancestor('td');
        var row = node.ancestor('tr');
        var table = node.ancestor('table');

        var data_node = (table.get('id') === 'errors_supplierMultipleItems') ? 'errors' : 'no_errors';
        var row_helpers_counter = 0;

        if (table.one('tr.template')) row_helpers_counter++;
        if (table.one('tr.template_child')) row_helpers_counter++;
        if (table.one('tr.no_data')) row_helpers_counter++;

        var index_node_child = row.get('rowIndex') - row_helpers_counter;
        this.dataset[data_node][index_node_child - 1].id_dir_selected = e.target.get('value');
    },

    /**
     * Se ejecuta al cambiar el valor numerico
     */
    change_number: function(e, arr_args) {
        var node = arr_args[1];
        var cell = node.ancestor('td');
        var row = node.ancestor('tr');
        var table = node.ancestor('table');
        var row_helpers_counter = 0;

        if (table.one('tr.template')) row_helpers_counter++;
        if (table.one('tr.template_child')) row_helpers_counter++;
        if (table.one('tr.no_data')) row_helpers_counter++;

        var index_node_child = row.get('rowIndex') - row_helpers_counter;
        var widget = node.ancestor('.rn_Quantity');
        var quantity = parseInt(widget.one('.qty').get('text'));
        var data_node = (table.get('id') === 'errors_supplierMultipleItems') ? 'errors' : 'no_errors';

        data_template_child = (typeof table.one('tbody').getData('template_child') !== 'undefined') ? JSON.parse(table.one('tbody').getData('template_child')) : [];

        while (row.hasClass('template_child_instance')) {
            row = row.previous();
        }

        var index_node = row.get('rowIndex') - row_helpers_counter;
        index_node_child = index_node_child - index_node;

        var has_template_child = false;

        if (typeof data_template_child[index_node_child - 1] !== 'undefined' && typeof data_template_child[index_node_child - 1][cell.getData('key')] !== 'undefined') {
            has_template_child = true;
        }

        var child_key = (has_template_child) ? data_template_child[index_node_child - 1][cell.getData('key')] : cell.getData('key');
        this.dataset[data_node][index_node - 1][child_key] = parseInt(quantity);
        return true;
    },

    /**
     * Eliminar las celdas de las tablas
     */
    delete_row: function(e, arr_args) {
        var node = arr_args[1];
        var table = node.ancestor('table');
        var row_helpers_counter = 0;

        if (table.one('tr.template')) row_helpers_counter++;
        if (table.one('tr.template_child')) row_helpers_counter++;
        if (table.one('tr.no_data')) row_helpers_counter++;


        var index_node = (node.ancestor('tr').get('rowIndex') - row_helpers_counter) - 1;
        var data_node = (table.get('id') === 'errors_supplierMultipleItems') ? 'errors' : 'no_errors';

        this.dataset[data_node].splice(index_node, 1);

        row = node.ancestor('tr');
        row_next = row.next();

        do {
            if (row_next && row_next.hasClass('template_child_instance')) {
                row_next.remove();
            }

            row = node.ancestor('tr');
            row_next = row.next();
        } while (row_next && row_next.hasClass('template_child_instance'))

        if (this.dataset[data_node].length === 0) {
            if (data_node === 'errors') {
                this.errors_supplierMultipleItems_initialRow.show();
            } else {
                this.items_supplierMultipleItems_initialRow.show();
            }
        }

        this.validateForm();

        return true;
    },

    /**
     * Función inicial
     */
    init: function() {
        this.btn_submit.set('disabled', true);

        // Librerias
        YUI({
            filter: "raw"
        }).use('uploader', this.create_file_upload);
    },

    upload: function(e) {
        if (e.target.hasAttribute('disabled')) {
            return false;
        }

        if (!this.upload_input.get('files').size()) {
            Integer.dialog('Debe seleccionar un archivo CSV.');

            return false;
        }

        e.target.setAttribute('disabled', 'disabled');

        this.f = this.upload_input.get('files')._nodes[0];
        this.reader = new FileReader();

        this.reader.onload = (function(theFile, btn) {
            return function(e) {
                RightNow.Event.fire("evt_SendFile", null, e.target.result);
            };

        })(this.f, e.target);

        this.reader.readAsText(this.f);
    },

    upload2: function(e) {
        if (e.target.hasAttribute('disabled')) {
            return false;
        }

        if (!this.upload_input.get('files').size()) {
            Integer.dialog('Debe seleccionar un archivo CSV.');

            return false;
        }

        e.target.setAttribute('disabled', 'disabled');

        this.f = this.upload_input.get('files')._nodes[0];
        this.reader = new FileReader();

        this.reader.onload = (function(theFile, btn) {
            return function(e) {
                RightNow.Event.fire("evt_SendFile2", null, e.target.result);
            };

        })(this.f, e.target);

        this.reader.readAsText(this.f);
    },

    /** #######################################################################
        EVENTOS DE BOTONES
    ######################################################################## */
    /**
     * Evento del botón 'Ver Mis Solicitudes'
     *
     * @param e {event}
     */
    handler_btn_requests: function(e) {
        window.location.href = '/app/sv/request/history';
    },

    /**
     * Evento del botón 'Realizar Otra Solicitud'
     *
     * @param e {event}
     */
    handler_btn_new_request: function(e) {
        window.location.href = '/app/sv/supplier/form_multiple';
    },

    /**
     * Suma o resta cantidades al insumo
     *
     * @param e {event}
     */
    handler_btn_actions: function(e) {
        var btn = (e.target.ancestor('.action')) ? e.target.ancestor('.action') : e.target;
        var qty = btn.ancestor('td').one('.qty');
        var actualQty = parseInt(qty.get('text'));
        var idItem = btn.ancestor('tr')._node.getAttribute('data-id');
        var newQty = actualQty;
        var validform = false;

        if (btn.hasClass('subtract') && actualQty >= 0) {
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
    handler_btn_upload_csv: function(e, arr_args) {
        this.btn_upload_csv.setAttribute('disabled', 'disabled');

        // Variables
        data = {};
        data.data = arr_args[1];
        debugger;
        this.sendCSV_ajax_endpoint(data);

        return true;
    },


    /**
     * Envía el formulario de solicitud
     *
     * @param e {event}
     */
    handler_btn_upload_csv2: function(e, arr_args) {
        Integer.resetTable(this.errors_supplierMultipleItems_table);
        Integer.resetTable(this.items_supplierMultipleItems_table);
        this.ContentTab_Loading.show();
        // Variables
        data = {};
        data.data = arr_args[1];
        debugger;
        //this.sendCSV_ajax_endpoint(data);
        data.id = 0;
        data.no_errors = {};
        data.errors = {};
        this.parseCSV_ajax_endpoint(data);

        return true;
    },

    /**
     * Envía el formulario de solicitud
     *
     * @param e {event}
     */
    handler_btn_submit: function(e) {
        e.preventDefault();
        this.btn_submit.set('disabled', true);

        // Mapeo de elementos del DOM
        if (!this.validate()) { // this.btn_submit.set('disabled', false);
            return false;
        }

        // Variables
        data = {};

        // valores
        data.lines_items = this.dataset.no_errors;

        // data.lines_items.push(obj_line);

        this.requestIncident_ajax_endpoint(data);

        return true;
    },

    /**
     * Envía el formulario de solicitud
     *
     * @param e {event}
     */
    handler_btn_submit2: function(e) {
        e.preventDefault();
        this.btn_submit2.set('disabled', true);
        this.ContentTab_Loading.show();

        // Mapeo de elementos del DOM
        if (!this.validate()) { // this.btn_submit.set('disabled', false);
            return false;
        }

        // Variables
        data = {};
        data.id = 0;
        // valores
        data.lines_items = this.dataset.no_errors;

        // data.lines_items.push(obj_line);

        this.CreateTickettIncident_ajax_endpoint(data);

        return true;
    },

    /** ####################################################################### */

    /**
     * Endpoint para crear la solicitud de insumos múltiples
     *
     * @param {event} e Evento que invóca al método
     * @return {boolean}
     */
    CreateTickettIncident_ajax_endpoint: function(params) {

        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });
        var s = document.getElementById('registro');
        s.value = "Procesando " + params.id + " de " + 1;
        RightNow.Ajax.makeRequest(this.data.attrs.CreateTickettIncident_ajax_endpoint, eventObj.data, {
            successHandler: this.CreateTickettIncident_ajax_endpointCallback,
            scope: this,
            timeout: 120000,
            data: eventObj,
            json: true
        });
    },
    /**
     * Manejador del la respuesta del endpoint #sendCSV_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del endpoint
     */

    CreateTickettIncident_ajax_endpointCallback: function(response, originalEventObj) {

        this.dataset = response;

        if (response.id > 4) {
            response.id = 0;

        } else {
            this.CreateTickettIncident_ajax_endpoint(response);
        }

        //debugger;

    },



    /** #######################################################################
        SERVICIOS
    ######################################################################## */

    /**
     * Endpoint para crear la solicitud de insumos múltiples
     *
     * @param {event} e Evento que invóca al método
     * @return {boolean}
     */
    parseCSV_ajax_endpoint: function(params) {
        this.ContentTab_Loading.show();


        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });

        // debugger;


        RightNow.Ajax.makeRequest(this.data.attrs.parseCSV_ajax_endpoint, eventObj.data, {
            successHandler: this.sendCSV_ajax_endpoint2Callback,
            failureHandler: function() {
                this.btn_upload_csv2.removeAttribute('disabled');
            },
            timeout: 260000,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    /** #######################################################################
        SERVICIOS
    ######################################################################## */

    /**
     * Endpoint para crear la solicitud de insumos múltiples
     *
     * @param {event} e Evento que invóca al método
     * @return {boolean}
     */
    processCSV_ajax_endpoint: function(params) {
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });

        //debugger;


        RightNow.Ajax.makeRequest(this.data.attrs.processCSV_ajax_endpoint, eventObj.data, {
            successHandler: this.sendCSV_ajax_endpoint2Callback,
            failureHandler: function() {
                this.btn_upload_csv2.removeAttribute('disabled');
            },
            timeout: 260000,
            scope: this,
            data: eventObj,
            json: true
        });
    },
    /**
     * Manejador del la respuesta del endpoint #sendCSV_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del endpoint
     */

    processCSV_ajax_endpointCallback: function(response, originalEventObj) {

        this.dataset = response;
        debugger;
        this.ContentTab_Loading.hide();
        this.btn_upload_csv2.removeAttribute('disabled');

        this.validateForm();


        if (this.dataset.errors) {
            if (this.dataset.errors.length) {
                Integer.fillTable(this.dataset.errors, this.errors_supplierMultipleItems_tbody);
            } else {
                this.errors_supplierMultipleItems_initialRow.show();
            }
        } else {
            this.errors_supplierMultipleItems_initialRow.show();
        }

        if (this.dataset.no_errors) {
            if (this.dataset.no_errors.length) {
                Integer.fillTable(this.dataset.no_errors, this.items_supplierMultipleItems_tbody, response.address);
            } else {
                this.items_supplierMultipleItems_initialRow.show();
            }
        } else {
            this.items_supplierMultipleItems_initialRow.show();
        }

        //debugger;

    },

    /**
     * Manejador del la respuesta del endpoint #sendCSV_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del endpoint
     */
    sendCSV_ajax_endpoint2Callback: function(response, originalEventObj) {

        //debugger;
        var s = document.getElementById('registro');

        var num = Object.keys(response.a_data['csv']).length;

        if (response.no_errors) {
            if (response.no_errors[0]) {
                this.noerrors.push(response.no_errors[0]);
            }
        }
        if (response.errors) {
            if (response.errors[0]) {
                this.errors.push(response.errors[0]);
            }
        }

        if (response.id < num) {

            //debugger;
            this.processCSV_ajax_endpoint(response);
        } else {
            s.value = "";
            this.dataset = response;

            this.ContentTab_Loading.hide();
            this.btn_upload_csv2.removeAttribute('disabled');

            this.validateForm();


            if (this.errors) {
                if (this.errors.length) {
                    Integer.fillTable(this.errors, this.errors_supplierMultipleItems_tbody);
                } else {
                    this.errors_supplierMultipleItems_initialRow.show();
                }
            } else {
                this.errors_supplierMultipleItems_initialRow.show();
            }

            if (this.noerrors) {
                if (this.noerrors.length) {
                    Integer.fillTable(this.noerrors, this.items_supplierMultipleItems_tbody, response.a_address);
                } else {
                    this.items_supplierMultipleItems_initialRow.show();
                }
            } else {
                this.items_supplierMultipleItems_initialRow.show();
            }

            //debugger;

        }

    },



    /** #######################################################################
        SERVICIOS
    ######################################################################## */

    /**
     * Endpoint para crear la solicitud de insumos múltiples
     *
     * @param {event} e Evento que invóca al método
     * @return {boolean}
     */
    sendCSV_ajax_endpoint: function(params) {
        this.ContentTab_Loading.show();
        Integer.resetTable(this.errors_supplierMultipleItems_table);
        Integer.resetTable(this.items_supplierMultipleItems_table);

        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });


        RightNow.Ajax.makeRequest(this.data.attrs.sendCSV_ajax_endpoint, eventObj.data, {
            successHandler: this.sendCSV_ajax_endpointCallback,
            failureHandler: function() {
                this.btn_upload_csv.removeAttribute('disabled');
            },
            timeout: 260000,
            scope: this,
            data: eventObj,
            json: true
        });
    },

    /**
     * Manejador del la respuesta del endpoint #sendCSV_ajax_endpoint.
     *
     * @param {object} response respuesta JSON del servidor
     * @param {object} originalEventObj objeto principal del endpoint
     */
    sendCSV_ajax_endpointCallback: function(response, originalEventObj) {
        this.ContentTab_Loading.hide();
        this.btn_upload_csv.removeAttribute('disabled');
        debugger;
        if (response.success) {
            this.dataset = response;

            this.validateForm();

            if (response.errors.length) {
                Integer.fillTable(response.errors, this.errors_supplierMultipleItems_tbody);
            } else {
                this.errors_supplierMultipleItems_initialRow.show();
            }

            if (response.no_errors.length) {
                Integer.fillTable(response.no_errors, this.items_supplierMultipleItems_tbody, response.address);
            } else {
                this.items_supplierMultipleItems_initialRow.show();
            }

            this.select_dir = this.items_supplierMultipleItems_tbody.all('select');
            this.select_dir.on('change', this.change_dir, this);
        } else {
            this.btn_submit.set('disabled', false);
            Integer.dialog(response.message, RightNow.Interface.getMessage("CUSTOM_MSG_REQUEST_DENIED"));
        }
    },

    /**
     * Determina si debe desabiltar la posibilidad de envío del formulario
     */
    validateForm: function() {
        var has_error = false;

        // Recorre el dataset validando que no existan líneas sin cantidades
        if (this.dataset.no_errors) {
            for (var i = 0, cant = this.dataset.no_errors.length; i < cant; i++) {
                if (this.dataset.no_errors.count_black === 0 && this.dataset.no_errors.count_color === 0) {
                    has_error = true;
                }
            }
        }
        // Determina si tiene algun error o no
        if (this.dataset.no_errors && this.dataset.errors) {
            if ((has_error || this.dataset.errors.length) || !this.dataset.no_errors.length) {
                this.btn_submit.setAttribute('disabled', 'disabled');
            } else {
                this.btn_submit.removeAttribute('disabled');
            }
        } else {
            this.btn_submit.removeAttribute('disabled');
        }
        return true;
    },

    /** ####################################################################### */

    /**
     * Endpoint para crear la solicitud de insumos múltiples
     *
     * @param {event} e Evento que invóca al método
     * @return {boolean}
     */
    requestIncident_ajax_endpoint: function(params) {
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.requestIncident_ajax_endpoint, eventObj.data, {
            successHandler: this.requestIncident_ajax_endpointCallback,
            scope: this,
            timeout: 120000,
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
    requestIncident_ajax_endpointCallback: function(response, originalEventObj) {
        //console.log(response);
        if (response.success === true) {
            this.screenForm.hide();
            this.screenSuccess.show();
            this.screenForm_ReferenceNumber.one('a').setHTML(response.incident_refNo);
            this.screenForm_ReferenceNumber.one('a').set('href', '/app/sv/supplier/form_multiple/i_id/' + response.incident_id);
        } else {
            this.btn_submit.set('disabled', false);
            // Integer.dialog('Error', RightNow.Interface.getMessage("CUSTOM_MSG_REQUEST_DENIED"));
            Integer.dialog(response.message);
        }
    },

    /** #######################################################################
        UTILIDADES
    ######################################################################## */

    /**
     * Método de validación de la solicitud
     */
    validate: function() { // Variables
        this.errors = [];
        this.errors_messages = [];
        this.is_valid = true;

        // RightNow.Event.fire('evt_ValidateInput', this.errors);

        // for (var error in this.errors) {
        // 	if (!this.errors[error].valid) {
        // 		this.errors_messages.push(this.errors[error].message);
        // 		this.is_valid = false;
        // 	}
        // }

        // if (!this.is_valid) {
        // 	this.errors_container.one('.messages').setHTML('<p>' + this.errors_messages.join('</p><p>') + '</p>');
        // 	this.errors_container.show();
        // 	this.btn_submit.set('disabled', true);
        // 	window.scrollTo(this.errors_container.getX(), this.errors_container.getY());
        // 	return false;
        // } else {
        // 	this.errors_container.hide();
        // 	this.errors_container.one('.messages').setHTML('');
        // }

        return true;
    }
});