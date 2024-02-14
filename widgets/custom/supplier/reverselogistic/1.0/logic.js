RightNow.namespace('Custom.Widgets.supplier.reverselogistic');
Custom.Widgets.supplier.reverselogistic = RightNow.Widgets.extend({     /**
     * Widget constructor.
     */
    constructor: function() {


        window.reverselogistic = this;
        this.widget = this.Y.one(this.baseSelector);
        this.incident_id = null;


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
        //this.dispatch_address = Integer.getInstanceByName('dispatch_address');
        this.dispatch = Integer.getInstanceByName('dispatch');
        this.Cantidad = Integer.getInstanceByName('Cantidad');
        this.Direccion = Integer.getInstanceByName('dispatch');
        this.Comuna = Integer.getInstanceByName('comuna');

        this.Comments = Integer.getInstanceByName('Comments');
        this.btn_requests = this.widget.one("#btn_requests");
        this.btn_new_request = this.widget.one("#btn_new_request");
       // Pantallas
        this.screenForm = this.widget.one('.rn_ScreenForm');
        this.screenSuccess = this.widget.one('.rn_ScreenSuccess');
        // Pantalla de Éxito
        this.screenForm_ReferenceNumber = this.screenSuccess.one('.rn_ReferenceNumber');
        this.btn_requests.on('click', this.handler_btn_requests, this);
        this.btn_new_request.on('click', this.handler_btn_new_request, this);

       this.btn_submit = this.widget.one("#btn_submit");
      
       
       if (this.data.js.list && this.data.js.list.list_dir) Integer.appendOptions(this.dispatch_address, this.data.js.list.list_dir, 'select', null, 'Elija Dirección');
       if (this.data.js.list && this.data.js.list.brands) Integer.appendOptions(this.hh_brand_list, this.data.js.list.brands, 'select', null, 'Elija Marca HH');

       // Eventos
       //this.dispatch_address.input.on('change', this.handler_dispatch_address, this);
       this.hh_brand_list.input.on('change', this.handler_change, this);
       this.Cantidad.input.on('change', this.handler_change, this)
       this.Direccion.input.on('change', this.handler_change, this);
       this.Comuna.input.on('change', this.handler_change, this);
       this.btn_submit.on('click', this.handler_btn_submit, this);


       //this.dispatch.input.on('change', this.validform, this);
       RightNow.Event.fire('evt_AddStep', { "description": RightNow.Interface.getMessage("CUSTOM_MSG_LOGICTIC_REQUEST_REQUEST_INFO") });
       RightNow.Event.fire('evt_AddStep', { "description": RightNow.Interface.getMessage("CUSTOM_MSG_SUPPLIER_REQUEST_REQUEST_CONFIRMATION") });
       RightNow.Event.fire('evt_ChangeStep', { "index": 1 });
    },

    handler_change: function (e) {
        var value = e.target.get('value');
        debugger;
        if (value) {
           /*var instanceContador = Y.one( ).get('id').split(/_Incident/)[0].replace('rn_','');
           var instanceContador = RightNow.Widgets.getWidgetInstance(instanceContador);
           instanceContador.input.set('value', value);*/
           this.validform();
        } 
        /*else {
            this.btn_submit.setDisabled(true);
        }*/
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
            /*var instanceName_marca1 = Y.one('input[name="Incident.CustomFields.c.marca_hh"]').get('id').split(/_Incident/)[0].replace('rn_','');
            var instanceName_marca = Y.one('input[name="Contact.Name.First"]').get('id').split(/_Contact/)[0].replace('rn_','');
            var instance_marca1 = RightNow.Widgets.getWidgetInstance(instanceName_marca1);
            var instance_marca = RightNow.Widgets.getWidgetInstance(instanceName_marca);
            instance_marca1.input.set('value', instance_marca.input.get('value'));*/


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
           /*var instanceContador = Y.one( ).get('id').split(/_Incident/)[0].replace('rn_','');
           var instanceContador = RightNow.Widgets.getWidgetInstance(instanceContador);
           instanceContador.input.set('value', value);*/
           this.validform();
        } 
        /*else {
            this.btn_submit.setDisabled(true);
        }*/
    },
    handler_change_Direccion: function (e) {
        var value = e.target.get('value');
        debugger;
        if (value) {
           /*var instanceContador = Y.one( ).get('id').split(/_Incident/)[0].replace('rn_','');
           var instanceContador = RightNow.Widgets.getWidgetInstance(instanceContador);
           instanceContador.input.set('value', value);*/
           this.validform();
        } 
        /*else {
            this.btn_submit.setDisabled(true);
        }*/
    },
    handler_change_Comuna: function (e) {
        var value = e.target.get('value');
        debugger;
        if (value) {
           /*var instanceContador = Y.one( ).get('id').split(/_Incident/)[0].replace('rn_','');
           var instanceContador = RightNow.Widgets.getWidgetInstance(instanceContador);
           instanceContador.input.set('value', value);*/
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
           /*var instanceContador = Y.one('input[name="Incident.CustomFields.c.gasto"]').get('id').split(/_Incident/)[0].replace('rn_','');
           var instanceContador = RightNow.Widgets.getWidgetInstance(instanceContador);
           instanceContador.input.set('value', value);*/
           this.validform();

        } 
        /*else {
            this.btn_submit.setDisabled(true);
        }*/
    },
    validform: function (e)
    {


        hh_brand_list=this.hh_brand_list.input.get('value');
        //dispatch_address=this.dispatch_address.input.get('value');
        Cantidad=this.Cantidad.input.get('value');
        Comments =  this.Comments.input.get('value');
        Direccion=this.Direccion.input.get('value');
        Comuna =this.Comuna.input.get('value')
        //var comentario = Y.one('input[name="Incident.Threads"]').get('id').split(/_Incident/)[0].replace('rn_','');
        //var comentario = RightNow.Widgets.getWidgetInstance(comentario);
        
        if(hh_brand_list  && Cantidad && Direccion && Comuna)
        {
   
            this.btn_submit.set('disabled', false);
        }
        else
        {
            this.btn_submit.set('disabled', true);
        }
    },

    /**
     * Valida y obtiene la lista de insumos de la HH
     *
     * @param e {event}
     */
    handler_btn_submit: function (e) {
      
        var btn = e.target;
        data = {};
    
        this.btn_submit.set('disabled', true);
        var instanceName_First = Y.one('input[name="Contact.Name.First"]').get('id').split(/_Contact/)[0].replace('rn_','');
        var instanceName_First = RightNow.Widgets.getWidgetInstance(instanceName_First);
        var instanceName_Last = Y.one('input[name="Contact.Name.Last"]').get('id').split(/_Contact/)[0].replace('rn_','');
        var instanceName_Last = RightNow.Widgets.getWidgetInstance(instanceName_Last);
        var instanceName_Email = Y.one('input[name="Contact.Emails.PRIMARY.Address"]').get('id').split(/_Contact/)[0].replace('rn_','');
        var instanceName_Email = RightNow.Widgets.getWidgetInstance(instanceName_Email);




        // Datos de contacto
        data.contact_name = instanceName_First.input.get('value');
        data.contact_phone = instanceName_Last.input.get('value');
        data.contact_email = instanceName_Email.input.get('value');


        // Datos Solicitud
        data.brand_hh = this.hh_brand_list.input.get('value');
        data.Cantidad = this.Cantidad.input.get('value');
        data.Comments =  this.Comments.input.get('value');
        //data.dispatch =  this.dispatch.input.get('value');
        data.Direccion =this.Direccion.input.get('value');
        data.Comuna =this.Comuna.input.get('value');
        
        // Dirección
        //data.dir_id = parseInt(this.dispatch_address.input.get('value'));
        this.createIncident_ajax_endpoint(data);

    },

    /**
     * Evento del botón 'Realizar Otra Solicitud'
     *
     * @param e {event}
     */
    handler_btn_new_request: function (e) {
        window.location.href = '/app/sv/request/reverse_logistics';
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
            this.screenForm_ReferenceNumber.one('a').setHTML(this.incident_refNo);
            this.screenForm_ReferenceNumber.one('a').set('href', '/app/sv/request/reverse_logistics/i_id/' + this.incident_id);
            RightNow.Event.fire('evt_ChangeStep', { "index": 2 });


        }
        else
        {
            this.dialog('Falta Información', response.message);
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
        this.validform();
        return true;
    }
});