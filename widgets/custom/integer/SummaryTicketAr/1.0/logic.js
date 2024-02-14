RightNow.namespace('Custom.Widgets.integer.SummaryTicketAr');

Custom.Widgets.integer.SummaryTicketAr = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function () {


        this.btn_request = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_request"]');
        this.btn_cancel = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_cancel"]');

        this.btn_upload = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_upload"]');





        this.btn_contadores = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_contadores"]');
        this.btn_cobertura = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_cobertura"]');

        this.rb_equipo_detenido = this.Y.one(this.baseSelector + ' input[type="checkbox"][name="rb_equipo_detenido"]');




        if (this.btn_request) this.btn_request.on("click", this._request, this);
        if (this.btn_cancel) this.btn_cancel.on("click", this._cancel, this);
        if (this.btn_contadores) this.btn_contadores.on("click", this._upload_contadores, this);

        this.arflow = this.Y.one(this.baseSelector + ' [name="arflow"]');
        if (this.arflow) this.arflow.on("change", this._Arflow, this);



        select_status = document.getElementById("select_status").value;
        if (select_status == 299) {
            opcion = document.getElementById("arflow");
            if (opcion.value == 283) {

                document.getElementById("motivoar").removeAttribute("disabled");
                document.getElementById("motivo_solucion").value = 95;
                document.getElementById("diagnostico").value = 94;

            }
            else {
                document.getElementById("motivoar").setAttribute('disabled', 'disabled');
                document.getElementById("motivoar").value = 300;
            }
        }
        else {
            document.getElementById("att").style.display = "none";

        }


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
                'username': '',
                'username': '',
                'disposition': '',
                'seguimiento_tecnico': '',
                'motivo_solucion': 106,
                'equipo_detenido': false,
                'diagnostico': 0,
                'nota': '',
                'tipo_contrato': '',
                'VisitNumber': this.data.attrs.VisitNumber,
                'tipo_id': ''
            }
        };


        /**
         * TODO: Se podría pasar el JS a un Utils con carga prioritaria.
         * FIXME: Corregir IE
         */
        window.tryInteger = window.setInterval(
            (function (parent) {
                return function () {
                    if (typeof this.Integer !== 'undefined') {
                        window.clearInterval(this.tryInteger);
                        parent._clearData();
                    }
                };
            })(this), 100);


    },
    _modal: function () {
        z = document.getElementById("myProgress");
        z.style.top = screen.height / 2 + 'px';
        z.style.position = 'fixed';
        z.style.width = screen.width / 1.1 + 'px';
        z.style.visibility = 'visible';


        //window.alert("Your screen resolution is: " + screen.height + 'x' + screen.width);

    },

    // cuando elija sin solicion AR debe asignar tecnico de BI
    _Arflow: function () {

        opcion = document.getElementById("arflow");

        if (opcion.value == 283) {

            document.getElementById("motivoar").removeAttribute("disabled");
            document.getElementById("motivo_solucion").value = 95;
            document.getElementById("diagnostico").value = 94;

        }
        else {
            document.getElementById("motivoar").setAttribute('disabled', 'disabled');
            document.getElementById("motivoar").value = 300;
        }
    },
    /**
     * Gets the File object from the input element
     * @return {Object|null} File object or null if there are none or the browser
     *                            doesn't support it
     */
    _upload: function (e, action) {



        z = document.getElementById("myProgress");
        z.style.top = screen.height / 2 + 'px';
        z.style.position = 'fixed';
        z.style.width = screen.width / 1.1 + 'px';
        z.style.visibility = 'visible';

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
        xhr.onload = function (e) {
            document.location.reload();
        };

        if (xhr.upload) {
            xhr.upload.onprogress = (event) => {
                //console.log('upload onprogress', event);
                x = document.getElementById("outter_1");
                x.style = "height:20px;width:" + (event.loaded / event.total) * 100 + "%";
            };
        }

        xhr.onerror = function (e) {
            console.error(xhr.statusText);
        };
        xhr.send(formData);

    },

    _upload_contadores: function (e, action) {

        debugger;
        this.data.js.order_detail.VisitNumber = document.getElementById("VisitNumber").value;
        if (document.getElementById("VisitNumber").value == "") {
            this.data.js.order_detail.VisitNumber = 1;
        }
        z = document.getElementById("myProgress");
        z.style.top = screen.height / 2 + 'px';
        z.style.position = 'fixed';
        z.style.width = screen.width / 1.1 + 'px';
        z.style.visibility = 'visible';

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
        xhr.onload = function (e) {
            document.location.reload();
        };

        if (xhr.upload) {
            xhr.upload.onprogress = (event) => {
                //console.log('upload onprogress', event);
                x = document.getElementById("outter_1");
                x.style = "height:20px;width:" + (event.loaded / event.total) * 100 + "%";
            };
        }

        xhr.onerror = function (e) {
            console.error(xhr.statusText);
        };
        xhr.send(formData);

    },

    _upload_ot: function (e, action) {

        this.data.js.order_detail.VisitNumber = document.getElementById("VisitNumber").value;
        if (document.getElementById("VisitNumber").value == "") {
            this.data.js.order_detail.VisitNumber = 1;
        }
        z = document.getElementById("myProgress");
        z.style.top = screen.height / 2 + 'px';
        z.style.position = 'fixed';
        z.style.width = screen.width / 1.1 + 'px';
        z.style.visibility = 'visible';

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
        xhr.onload = function (e) {
            document.location.reload();
        };

        if (xhr.upload) {
            xhr.upload.onprogress = (event) => {
                //console.log('upload onprogress', event);
                x = document.getElementById("outter_1");
                x.style = "height:20px;width:" + (event.loaded / event.total) * 100 + "%";
            };
        }

        xhr.onerror = function (e) {
            console.error(xhr.statusText);
        };
        xhr.send(formData);

    },
    _rb_equipo_detenido: function (e) {
        this.data.js.order_detail.seguimiento_tecnico = document.getElementById("seguimiento_tecnico").value;
        if (document.getElementById('rb_equipo_detenido').checked) {
            document.getElementById('id_btn_contadores').style.display = 'none';
            document.getElementById('id_frm_fileinfo_contadores').style.display = 'none';
            document.getElementById('id_contadores').style.display = 'none';

        } else {
            document.getElementById('id_btn_contadores').style.display = '';
            document.getElementById('id_frm_fileinfo_contadores').style.display = '';
            document.getElementById('id_contadores').style.display = '';
        }
    },

    _clearData: function () {

        this.data.js = Integer._extend({}, this.js_default, this.data.js);


    },



    /**
     * Almacena la solicitud como borrador.
     * TODO: Bloquear todos los botones de la interfaz una vez ejecutado el evento.
     *
     * @param {Event} Mouse Event
     */
    _cancel: function (e) {
        //document.location.reload();

        document.location.href = '/app/reparacion/ar_request'
    },

    /**
     * Realiza la solicitud de repuestos.
     *
     * @param {Event} Mouse Event
     */
    _request: function (e) {
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
    _setOrder: function (e, action) {



        var btn = e._currentTarget;

        btn.setAttribute('disabled', 'disabled');


        this.data.js.order_detail.action = action;

        this.data.js.order_detail.select_status = document.getElementById("select_status").value;
        this.data.js.order_detail.seguimiento_tecnico = document.getElementById("seguimiento_tecnico").value;
        this.data.js.order_detail.tipo_contrato = document.getElementById("tipo_contrato").value;
        this.data.js.order_detail.nota = document.getElementById("nota").value;
        this.data.js.order_detail.status_prev = document.getElementById("id_status_prev").value;
        this.data.js.order_detail.VisitNumber = document.getElementById("VisitNumber").value;


        if (document.getElementById("VisitNumber").value == "") {
            this.data.js.order_detail.VisitNumber = 1;
        }
        this.data.js.order_detail.tipo_id = document.getElementById("tipo_id").value;
        if (this.data.js.order_detail.seguimiento_tecnico == 299 || this.data.js.order_detail.seguimiento_tecnico == 43) {
            this.data.js.order_detail.equipo_detenido = document.getElementById("rb_equipo_detenido").checked;
            this.data.js.order_detail.cont1_hh = document.getElementById("cont1_hh").value;
            this.data.js.order_detail.cont2_hh = document.getElementById("cont2_hh").value;
            this.data.js.order_detail.motivo_solucion = document.getElementById("motivo_solucion").value;
            this.data.js.order_detail.diagnostico = document.getElementById("diagnostico").value;
            this.data.js.order_detail.ArFlow = document.getElementById("arflow").value;
            this.data.js.order_detail.motivoar = document.getElementById("motivoar").value;

        }
        this.data.js.order_detail.disposition = document.getElementById("disposition").value;
        if (this.data.js.order_detail.seguimiento_tecnico == 299) {
            this.data.js.order_detail.equipo_detenido = document.getElementById("rb_equipo_detenido").checked;
            this.data.js.order_detail.Description = document.getElementById("Description").value;
            this.data.js.order_detail.Solution = document.getElementById("Solution").value;

        }
        //console.log("2->%s", JSON.stringify(this.data.js));
        RightNow.Ajax.makeRequest('/cc/ServiceWeb/updateIncidentStateAr', {
            data: JSON.stringify(this.data.js),
        }, {
            scope: {
                _btn: btn,
                _action: 1,
                _parent: this
            },
            successHandler: function (e) {
                var title = 'Error';
                var msge = 'Ocurrió un error inesperado.';
                var msgBN = 'Contador B/N Menor al Actual.';
                var msgColor = 'Contador B/N Menor al Actual.';

                var response = e.response;
                var msg = "";



                msg = response.msg;

                /*RightNow.UI.Dialog.messageDialog(msg, {
                    title: response.ref_no,
                    exitCallback: function() {
                        document.location.reload();
                    }
                });*/

                if (response.status !== true || response.validar > 0) {
                    RightNow.UI.Dialog.messageDialog(msg, {
                        title: title,
                        exitCallback: function () {
                            document.location.reload();
                        }
                    });
                } else {
                    RightNow.UI.Dialog.messageDialog('Datos Guardados Exitosamente', {
                        title: 'Ticket ' + response.ref_no,
                        exitCallback: function () {
                            if (response.select_status == 43 || response.select_status == 24) {

                                if (response.tipo_contrato == 'Cargo') {
                                    document.location.href = '/app/reparacion/cargo';
                                } else {
                                    document.location.href = '/app/reparacion/home'
                                }
                            } else {
                                if (response.select_status == 19 || response.select_status == 17 || response.select_status == 297) {
                                    document.location.href = '/app/reparacion/ar_request'

                                } else {

                                    document.location.reload();
                                }
                            }
                        }
                    });

                }

            },
            failureHandler: function (e) {
                this._btn.removeAttribute('disabled');

                var title = 'Error';
                var msg = 'Error del servicio.';
                var response = e.response;

                RightNow.UI.Dialog.messageDialog(msg, {
                    title: title
                });
            }
        });
    },


});