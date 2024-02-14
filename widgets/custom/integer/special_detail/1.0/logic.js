RightNow.namespace('Custom.Widgets.integer.special_detail');
Custom.Widgets.integer.special_detail = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {




        /**
         * Sample widget method.
         */
        this.js_default = {
            "order_detail": {
                "action": '',
                "ref_no": this.data.attrs.ref_no,
                "select_status": this.data.attrs.select_status,
                "cont1_hh": this.data.attrs.cont1_hh,
                "cont2_hh": this.data.attrs.cont2_hh,
                "status_prev": "",
                "shipping_instructions": "",
                "file": null,
                "filedata": null,
                "contents": null,
                'Description': '-descripcion-',
                'Solution': '-solucion-',
                'Incident': null,
                'IpNumber': '-IpNumbe-',
                'Copy': true,
                'Scan': true,
                'Printer': true,
                'Fax': true,
                'Temperture': 'SV',
                'IssueCausa': 'SV',
                'ElectricalCondition': 'SV',
                'EnviromentCondit': 'SV',
                'PrintFlow': 'SV',
                'username': '',
                'username': '',
                'disposition': '',
                'seguimiento_tecnico': '',
                'motivo_solucion': 106,
                'equipo_detenido': false,
                'diagnostico': 0,
                'gasto': 0,
                'expend_type': 105,
                'gsto_detail': '',
                'AlternativeEmails': '-Sin Correos-',
                'nota': '',
                'tipo_contrato': '',
                'Area': 'Sin Valor',
                'CostCenter': 'Sin Valor',
                'Reception_Name': 'N/A',
                'NoDataMobile': false,
                'VisitNumber': this.data.attrs.VisitNumber,
                'tipo_id': ''
            }
        };


        /**
         * TODO: Se podría pasar el JS a un Utils con carga prioritaria.
         * FIXME: Corregir IE
         */
        window.tryInteger = window.setInterval(
            (function(parent) {
                return function() {
                    if (typeof this.Integer !== 'undefined') {
                        window.clearInterval(this.tryInteger);
                        parent.init();
                        parent._clearData();
                    }
                };
            })(this), 100);
    },

    /**
     * Función inicial
     */
    init: function() {
        // Mapeo de instancias
        lista = JSON.parse('[{"name": "Sin Valor", "ID": "100"},{"name": "SI", "ID": "0"},{"name": "NO", "ID": "1"}]');
        opciones = JSON.parse('[{"name": "Sin Valor", "ID": "100"},{"name": "SI", "ID": "1"},{"name": "NO", "ID": "0"}]');
        opcionesConexion = JSON.parse('[{"name": "Sin Valor", "ID": "100"},{"name": "DESCONECTADO", "ID": "1"},{"name": "USB", "ID": "2"},{"name": "RED", "ID": "3"}]');
        opcionesGeneral = JSON.parse('[{"name": "Sin Valor", "ID": "100"},{"name": "Operativo", "ID": "1"},{"name": "Detenido", "ID": "2"},{"name": "Backup", "ID": "3"}]');

        window.widget_special_detail = this;
        this.btn_request = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_request"]');
        this.btn_cancel = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_cancel"]');
        if (this.btn_request) this.btn_request.on("click", this._request, this);
        if (this.btn_cancel) this.btn_cancel.on("click", this._cancel, this);
        this.direccion_incorrecta = Integer.getInstanceByName('direccion_incorrecta');
        this.direccion_incorrecta.input.on("change", this._direccion_incorrecta, this);

        Integer.appendOptions(this.direccion_incorrecta, lista, 'select', null, null);

        this.Nueva_Etiqueta = Integer.getInstanceByName('Nueva_Etiqueta');
        Integer.appendOptions(this.Nueva_Etiqueta, opciones, 'select', null, null);

        this.IgualSerie = Integer.getInstanceByName('IgualSerie');
        Integer.appendOptions(this.IgualSerie, opciones, 'select', null, null);


        this.Cambio_Etiqueta = Integer.getInstanceByName('Cambio_Etiqueta');
        Integer.appendOptions(this.Cambio_Etiqueta, opciones, 'select', null, null);

        this.Alerta_Insumo = Integer.getInstanceByName('Alerta_Insumo');
        Integer.appendOptions(this.Alerta_Insumo, opciones, 'select', null, null);

        this.Estado_Conexion = Integer.getInstanceByName('Estado_Conexion');
        Integer.appendOptions(this.Estado_Conexion, opcionesConexion, 'select', null, null);
        this.Estado_Conexion.input.on("change", this._Estado_Conexion, this);


        this.Estado_General = Integer.getInstanceByName('Estado_General');
        Integer.appendOptions(this.Estado_General, opcionesGeneral, 'select', null, null);



        select_status = document.getElementById("select_status").value;
        if (select_status == 18) {
            this.EquipoDetenido_obj = Integer.getInstanceByName('EquipoDetenido');
            this.EquipoDetenido_obj.input.on("change", this._EquipoDetenido, this);
            this.rb_sin_cobertura = Integer.getInstanceByName('rb_sin_cobertura');
            this.rb_sin_cobertura.input.on("change", this._sin_cobertura, this);


            Integer.appendOptions(this.direccion_incorrecta, lista, 'select', null, null);

            this.btn_upload = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_upload"]');
            this.btn_cobertura = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_cobertura"]');
            this.btn_contadores = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_contadores"]');

            if (this.btn_upload) this.btn_upload.on("click", this._upload, this); // upload file OK
            if (this.btn_cobertura) this.btn_cobertura.on("click", this._upload_ot, this)
            if (this.btn_contadores) this.btn_contadores.on("click", this._upload_contadores, this);
        }
    },
    _modal: function() {
        z = document.getElementById("myProgress");
        z.style.top = screen.height / 2 + 'px';
        z.style.position = 'fixed';
        z.style.width = screen.width / 1.1 + 'px';
        z.style.visibility = 'visible';


        //window.alert("Your screen resolution is: " + screen.height + 'x' + screen.width);

    },
    _clearData: function() {

        this.data.js = Integer._extend({}, this.js_default, this.data.js);


    },

    /**
     * Sample widget method.
     */
    methodName: function() {

    },
    /**
     * Almacena la solicitud como borrador.
     * TODO: Bloquear todos los botones de la interfaz una vez ejecutado el evento.
     *
     * @param {Event} Mouse Event
     */
    _cancel: function(e) {
        //document.location.reload();

        document.location.href = '/app/reparacion/special/special_suport';
    },

    _Estado_Conexion: function(e) {
        // this.EquipoDetenido = Integer.getInstanceByName('EquipoDetenido');
        Estado_Conexion = this.Estado_Conexion.input.get('value');
        if (Estado_Conexion == "3") {
            document.getElementById('IPDiv').style.display = '';

        } else {
            document.getElementById('IPDiv').style.display = 'none';

        }
    },
    _direccion_incorrecta: function(e) {
        // this.EquipoDetenido = Integer.getInstanceByName('EquipoDetenido');
        direccion_incorrecta = this.direccion_incorrecta.input.get('value');
        if (direccion_incorrecta == "1") {
            document.getElementById('DireccionDespachoDiv').style.display = 'none';
            document.getElementById('DireccionCorrectaDiv').style.display = '';

        } else {
            document.getElementById('DireccionDespachoDiv').style.display = '';
            document.getElementById('DireccionCorrectaDiv').style.display = 'none';

        }
    },

    _EquipoDetenido: function(e) {
        // this.EquipoDetenido = Integer.getInstanceByName('EquipoDetenido');
        EquipoDetenido = this.EquipoDetenido_obj.input._node.checked;
        if (EquipoDetenido) {
            document.getElementById('id_frm_fileinfo_contadores').style.display = 'none';
            document.getElementById('id_btn_contadores').style.display = 'none';
            document.getElementById('id_contadores1').style.display = 'none';
            document.getElementById('id_contadores2').style.display = 'none';
        } else {
            document.getElementById('id_frm_fileinfo_contadores').style.display = '';
            document.getElementById('id_btn_contadores').style.display = '';
            document.getElementById('id_contadores1').style.display = '';
            document.getElementById('id_contadores2').style.display = '';

        }
    },
    _sin_cobertura: function(e) {
        rb_sin_cobertura = this.rb_sin_cobertura.input._node.checked;

        if (rb_sin_cobertura) {
            document.getElementById('id_btn_cobertura').style.display = '';
            document.getElementById('id_frm_fileinfo_ot').style.display = '';

        } else {
            document.getElementById('id_btn_cobertura').style.display = 'none';
            document.getElementById('id_frm_fileinfo_ot').style.display = 'none';


        }
    },

    _valida: function(param) {

        if (param.motivo_solucion == 0) { return 'Resultado Atención'; };
        if (param.Nueva_Etiqueta == 100) { return 'Nueva Etiqueta'; };
        if (param.Cambio_Etiqueta == 100) { return 'Cambio Etiqueta'; };
        if (param.Alerta_Insumo == 100) { return 'Alerta Insumo'; };
        if (param.IgualSerie == 100) { return 'Coincide  Serie'; };

        if (param.direccion_incorrecta == 100) { return 'direccion incorrecta'; };


        if (param.Reception_Name == "Indicar") { return 'Nombre Contacto'; };
        if (param.AlternativeEmails == "-Sin Correos-") { return 'Correo Contacto'; };
        if (param.Phone == 'Indicar') { return 'Telefono Contacto'; };
        if (param.Reception_Name2 == "Indicar") { return 'Nombre Otro Contacto'; };
        if (param.AlternativeEmails2 == "-Sin Correos-") { return 'Correo Otro Contacto'; };
        if (param.Phone2 == 'Indicar') { return 'Telefono otro Contacto'; };
        if (param.Estado_Conexion == 100) { return 'Estado Conexion'; };
        //if (param.EquipoDetenido == 0) { return false; };
        //if (param.sin_cobertura == 0) { return false; };

        if (param.motivo_solucion == 0) { return 'Resultado Atención'; };





        if (param.Estado_General == 100) { return 'Estado General'; };


        this.cont1_hh = Integer.getInstanceByName('cont1_hh');
        data.cont1_hh = this.cont1_hh.input.get('value');
        data.id_cont1_value = document.getElementById("id_cont1_value").value;

        this.cont2_hh = Integer.getInstanceByName('cont2_hh');
        data.cont2_hh = this.cont2_hh.input.get('value');
        data.id_cont2_value = document.getElementById("id_cont2_value").value;



        data.id_cont1_name = document.getElementById("id_cont1_name").value;

        if (param.cont1_hh == "") { return 'Ingrese ' + data.id_cont1_name; };

        //if (parseInt(param.cont1_hh) < parseInt(data.id_cont1_value)) { return data.id_cont1_name + ' Debe ser mayor o igual'; };


        if (param.id_cont2_existe == 1) {
            data.id_cont2_value = document.getElementById("id_cont2_value").value;
            data.id_cont2_name = document.getElementById("id_cont2_name").value;

            if (param.cont2_hh == "") { return 'Contador 2'; };
            // if (parseInt(param.cont2_hh) < parseInt(data.id_cont2_value)) { return data.id_cont2_name + ' Debe ser mayor o igual'; };

        }
        /* if (param.IpNumber) { return false; };
         if (param.Copy) { return false; };
         if (param.Scan) { return false; };
         if (param.Printer) { return false; };
         if (param.Fax) { return false; };
         */
        if (param.motivo_solucion == 0) { return 'motivo_solucion'; };
        if (param.PrintFlow == 0) { return 'PrintFlow'; };
        if (param.OperatingSystem == 0) { return 'OperatingSystem'; };
        if (param.ClickScanner == "") { return 'Click Scanner'; };
        if (param.UsersNumber == "") { return 'Numero de Usuarios'; };

        return 'OK';
    },
    _request: function(param) {
        data = {};

        data.ref_no = this.js_default.order_detail.ref_no;
        data.select_status = document.getElementById("select_status").value;
        // data.expend_type = document.getElementById("expend_type").value;
        // data.gasto = document.getElementById("gasto").value;
        // data.gsto_detail = document.getElementById("gsto_detail").value;
        data.id_status_prev = document.getElementById("id_status_prev").value;

        if (data.select_status == 18 && data.select_status == data.id_status_prev) {


            this.cont1_hh = Integer.getInstanceByName('cont1_hh');
            data.cont1_hh = this.cont1_hh.input.get('value');
            this.cont2_hh = Integer.getInstanceByName('cont2_hh');
            data.cont2_hh = this.cont2_hh.input.get('value');


            data.id_cont2_existe = document.getElementById("id_cont2_existe").value;

            data.id_cont2_value = document.getElementById("id_cont2_value").value;


            this.direccion_incorrecta = Integer.getInstanceByName('direccion_incorrecta');
            data.direccion_incorrecta = this.direccion_incorrecta.input.get('value');

            this.Nueva_Etiqueta = Integer.getInstanceByName('Nueva_Etiqueta');
            data.Nueva_Etiqueta = this.Nueva_Etiqueta.input.get('value');

            this.IgualSerie = Integer.getInstanceByName('IgualSerie');
            data.IgualSerie = this.IgualSerie.input.get('value');

            this.Alerta_Insumo = Integer.getInstanceByName('Alerta_Insumo');
            data.Alerta_Insumo = this.Alerta_Insumo.input.get('value');

            this.Cambio_Etiqueta = Integer.getInstanceByName('Cambio_Etiqueta');
            data.Cambio_Etiqueta = this.Cambio_Etiqueta.input.get('value');

            this.Estado_Conexion = Integer.getInstanceByName('Estado_Conexion');
            data.Estado_Conexion = this.Estado_Conexion.input.get('value');

            this.Estado_General = Integer.getInstanceByName('Estado_General');
            data.Estado_General = this.Estado_General.input.get('value');

            if (data.direccion_incorrecta == "1") {
                this.Direccion = Integer.getInstanceByName('Direccion');
                data.Direccion = this.Direccion.input.get('value');
                this.Comuna = Integer.getInstanceByName('Comuna');
                data.Comuna = this.Comuna.input.get('value');
                this.Region = Integer.getInstanceByName('Region');
                data.Region = this.Region.input.get('value');
            }

            this.EquipoDetenido = Integer.getInstanceByName('EquipoDetenido');
            data.EquipoDetenido = this.EquipoDetenido.input._node.checked;
            data.sin_cobertura = this.rb_sin_cobertura.input._node.checked;

            data.motivo_solucion = document.getElementById("motivo_solucion").value;
            //data.diagnostico = document.getElementById("diagnostico").value;
            //data.Description = document.getElementById("Description").value;
            //data.Solution = document.getElementById("Solution").value;


            data.Reception_Name = document.getElementById("Reception_Name").value;
            data.AlternativeEmails = document.getElementById("AlternativeEmails").value;
            data.Phone = document.getElementById("Phone").value;

            data.Reception_Name2 = document.getElementById("Reception_Name2").value;
            data.AlternativeEmails2 = document.getElementById("AlternativeEmails2").value;
            data.Phone2 = document.getElementById("Phone2").value;


            data.IpNumber = document.getElementById("IpNumber").value;
            data.Copy = document.getElementById("Copy").value;
            data.Scan = document.getElementById("Scan").value;
            data.Printer = document.getElementById("Printer").value;
            data.Fax = document.getElementById("Fax").value;
            data.motivo_solucion = document.getElementById("motivo_solucion").value;
            data.PrintFlow = document.getElementById("PrintFlow").value;
            data.OperatingSystem = document.getElementById("OperatingSystem").value;

            this.ClickScanner = Integer.getInstanceByName('ClickScanner');
            data.ClickScanner = this.ClickScanner.input.get('value');

            this.UsersNumber = Integer.getInstanceByName('UsersNumber');
            data.UsersNumber = this.UsersNumber.input.get('value');



            //data.Area = document.getElementById("Area").value;
            //data.CostCenter = document.getElementById("CostCenter").value;
            if (data.motivo_solucion < 2) {
                $msg = this._valida(data);
                if ($msg != "OK") {

                    RightNow.UI.Dialog.messageDialog($msg, {
                        title: 'Falta Información',
                        icon: "WARN"
                    });
                    return false;
                }
            }

        }

        /*

        data.Reception_Name = document.getElementById("Reception_Name").value;
        data.AlternativeEmails = document.getElementById("AlternativeEmails").value;
        data.Telefono_contacto = document.getElementById("Telefono_contacto").value;


        {}

        Cambio_Etiqueta
        data.Cambio_Etiqueta = document.getElementById("Cambio_Etiqueta").value;
            
        Nueva_Etiqueta
        data.Nueva_Etiqueta = document.getElementById("Nueva_Etiqueta").value;
            
        Alerta_insumos
        data.Alerta_insumos = document.getElementById("Alerta_insumos").value;

        Direccion_correcta
        data.Direccion_correcta = document.getElementById("Direccion_correcta").value;

        Direccion_Calle
        data.Direccion_Calle = document.getElementById("Direccion_Calle").value;

        Direccion_Comuna
        data.Direccion_Comuna = document.getElementById("Direccion_Comuna").value;

        Direccion_Region
        data.Direccion_Region = document.getElementById("Direccion_Region").value;

        Estado_Conexion
        data.Estado_Conexion = document.getElementById("Estado_Conexion").value;

        Estado_General
        data.Estado_General = document.getElementById("Estado_General").value;



        */


        this.SendRequest_ajax_endpoint(data);
    },
    /**
     * Makes an AJAX request for `SendRequest_ajax_endpoint`.
     */
    SendRequest_ajax_endpoint: function(params) {
        // Make AJAX request:
        this.widget = this.Y.one(this.baseSelector);
        this.ContentTab_Loading = this.widget.one('.rn_ContentTab_Loading');
        this.ContentTab_Loading.show();
        var eventObj = new RightNow.Event.EventObject(this, {
            data: {
                w_id: this.data.info.w_id,
                // Parameters to send
                data: JSON.stringify(params)
            }
        });
        RightNow.Ajax.makeRequest(this.data.attrs.SendRequest_ajax_endpoint, eventObj.data, {
            successHandler: this.SendRequest_ajax_endpointCallback,
            scope: this,
            data: eventObj,
            json: true
        });
    },
    /**
     * Gets the File object from the input element
     * @return {Object|null} File object or null if there are none or the browser
     *                            doesn't support it
     */
    _upload: function(e, action) {


        z = document.getElementById("myProgress");
        z.style.top = screen.height / 2 + 'px';
        z.style.position = 'fixed';
        z.style.width = screen.width / 1.1 + 'px';
        z.style.visibility = 'visible';
        //this._openModal();
        var btn = e._currentTarget;

        btn.setAttribute('disabled', 'disabled');
        x = document.getElementById("outter");


        var fileInput = document.getElementById('file');
        var file = fileInput.files[0];
        var formData = new FormData();
        formData.append('file', file);
        formData.append('ref_no', this.data.js.order_detail.ref_no);
        var xhr = new XMLHttpRequest();
        // Add any event handlers here...
        xhr.open('POST', "/cc/Services/upload", true);
        xhr.onload = function(e) {
            document.location.reload();
        };

        if (xhr.upload) {
            xhr.upload.onprogress = (event) => {
                //console.log('upload onprogress', event);
                x = document.getElementById("outter_1");
                x.style = "height:20px;width:" + (event.loaded / event.total) * 100 + "%";
            };
        }

        xhr.onerror = function(e) {
            console.error(xhr.statusText);
        };
        xhr.send(formData);

    },

    _upload_contadores: function(e, action) {

        this.data.js.order_detail.VisitNumber = document.getElementById("VisitNumber").value;
        if (document.getElementById("VisitNumber").value == "") {
            this.data.js.order_detail.VisitNumber = 1;
        }
        z = document.getElementById("myProgress");
        z.style.top = screen.height / 2 + 'px';
        z.style.position = 'fixed';
        z.style.width = screen.width / 1.1 + 'px';
        z.style.visibility = 'visible';
        //this._openModal();
        var btn = e._currentTarget;

        btn.setAttribute('disabled', 'disabled');
        x = document.getElementById("outter");


        var fileInput = document.getElementById('id_file_contadores');
        var file = fileInput.files[0];
        var formData = new FormData();
        formData.append('file', file);
        formData.append('ref_no', this.data.js.order_detail.ref_no);
        formData.append('VisitNumber', this.data.js.order_detail.VisitNumber);
        formData.append('type', 'Contadores');
        var xhr = new XMLHttpRequest();
        // Add any event handlers here...
        xhr.open('POST', "/cc/Services/upload", true);
        xhr.onload = function(e) {
            document.location.reload();
        };

        if (xhr.upload) {
            xhr.upload.onprogress = (event) => {
                //console.log('upload onprogress', event);
                x = document.getElementById("outter_1");
                x.style = "height:20px;width:" + (event.loaded / event.total) * 100 + "%";
            };
        }

        xhr.onerror = function(e) {
            console.error(xhr.statusText);
        };
        xhr.send(formData);

    },

    _upload_ot: function(e, action) {

        this.data.js.order_detail.VisitNumber = document.getElementById("VisitNumber").value;
        if (document.getElementById("VisitNumber").value == "") {
            this.data.js.order_detail.VisitNumber = 1;
        }
        z = document.getElementById("myProgress");
        z.style.top = screen.height / 2 + 'px';
        z.style.position = 'fixed';
        z.style.width = screen.width / 1.1 + 'px';
        z.style.visibility = 'visible';

        //this._openModal();
        var btn = e._currentTarget;

        btn.setAttribute('disabled', 'disabled');
        x = document.getElementById("outter");


        var fileInput = document.getElementById('id_fileinfo_ot');
        var file = fileInput.files[0];
        var formData = new FormData();
        formData.append('file', file);
        formData.append('ref_no', this.data.js.order_detail.ref_no);
        formData.append('VisitNumber', this.data.js.order_detail.VisitNumber);
        formData.append('type', 'OT');
        var xhr = new XMLHttpRequest();
        // Add any event handlers here...
        xhr.open('POST', "/cc/Services/upload", true);
        xhr.onload = function(e) {
            document.location.reload();
        };

        if (xhr.upload) {
            xhr.upload.onprogress = (event) => {
                //console.log('upload onprogress', event);
                x = document.getElementById("outter_1");
                x.style = "height:20px;width:" + (event.loaded / event.total) * 100 + "%";
            };
        }

        xhr.onerror = function(e) {
            console.error(xhr.statusText);
        };
        xhr.send(formData);

    },

    /**
     * Handles the AJAX response for `SendRequest_ajax_endpoint`.
     * @param {object} response JSON-parsed response from the server
     * @param {object} originalEventObj `eventObj` from #getDefault_ajax_endpoint
     */
    SendRequest_ajax_endpointCallback: function(response, originalEventObj) {
        // Handle response
        this.widget = this.Y.one(this.baseSelector);
        this.ContentTab_Loading = this.widget.one('.rn_ContentTab_Loading');
        //   this.ContentTab_Loading.hide();

        document.location.href = '/app/reparacion/special/special_detail/ref_no/' + response.ref_no;
    }
});