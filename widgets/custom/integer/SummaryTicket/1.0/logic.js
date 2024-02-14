RightNow.namespace('Custom.Widgets.integer.SummaryTicket');

Custom.Widgets.integer.SummaryTicket = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {


        this.btn_request = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_request"]');
        this.btn_cancel = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_cancel"]');
        this.btn_sign = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_sign"]');
        this.btn_upload = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_upload"]');

        
        


        this.btn_contadores = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_contadores"]');
        this.btn_cobertura = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_cobertura"]');

        this.rb_equipo_detenido = this.Y.one(this.baseSelector + ' input[type="checkbox"][name="rb_equipo_detenido"]');

        this.rb_sin_cobertura = this.Y.one(this.baseSelector + ' input[type="checkbox"][name="rb_sin_cobertura"]');

        this.rb_equipo_detenido = this.Y.one(this.baseSelector + ' input[type="checkbox"][name="rb_equipo_detenido"]');

        if (this.btn_request) this.btn_request.on("click", this._request, this);
        if (this.btn_cancel) this.btn_cancel.on("click", this._cancel, this);
        if (this.btn_sign) this.btn_sign.on("click", this._btn_sign, this);
        if (this.btn_upload) this.btn_upload.on("click", this._upload, this); // upload file OK


        if (this.btn_cobertura) this.btn_cobertura.on("click", this._upload_ot, this)
        if (this.btn_contadores) this.btn_contadores.on("click", this._upload_contadores, this);

        if (this.rb_equipo_detenido) this.rb_equipo_detenido.on("click", this._rb_equipo_detenido, this);
        if (this.rb_sin_cobertura) this.rb_sin_cobertura.on("click", this._rb_sin_cobertura, this);


        if (document.getElementById('rb_equipo_detenido').checked) {
            document.getElementById('id_btn_contadores').style.display = 'none';
            document.getElementById('id_frm_fileinfo_contadores').style.display = 'none';

            if (document.getElementById("seguimiento_tecnico").value == 18) {
                document.getElementById('id_btn_sign').style.display = '';
            }
            document.getElementById('id_contadores').style.display = 'none';

        } else {
            document.getElementById('id_btn_contadores').style.display = '';
            document.getElementById('id_frm_fileinfo_contadores').style.display = '';
            if (document.getElementById("seguimiento_tecnico").value == 18) {
                document.getElementById('id_btn_sign').style.display = '';
            }
            document.getElementById('id_contadores').style.display = '';
        }

        if (document.getElementById('rb_sin_cobertura').checked) {
            document.getElementById('id_btn_cobertura').style.display = '';
            document.getElementById('id_frm_fileinfo_ot').style.display = '';
            if (document.getElementById("seguimiento_tecnico").value == 18) {
                document.getElementById('id_btn_sign').style.display = 'none';
            }

        } else {
            document.getElementById('id_btn_cobertura').style.display = 'none';
            document.getElementById('id_frm_fileinfo_ot').style.display = 'none';
            if (document.getElementById("seguimiento_tecnico").value == 18) {
                document.getElementById('id_btn_sign').style.display = '';
            }
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
                        parent._clearData();
                    }
                };
            })(this), 100);


    },
    _modal: function() {
        z = document.getElementById("myProgress");
        z.style.top = screen.height / 2 + 'px';
        z.style.position = 'fixed';
        z.style.width = screen.width / 1.1 + 'px';
        z.style.visibility = 'visible';


        //window.alert("Your screen resolution is: " + screen.height + 'x' + screen.width);

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
    _rb_equipo_detenido: function(e) {
        this.data.js.order_detail.seguimiento_tecnico = document.getElementById("seguimiento_tecnico").value;
        if (document.getElementById('rb_equipo_detenido').checked) {
            document.getElementById('id_btn_contadores').style.display = 'none';
            document.getElementById('id_frm_fileinfo_contadores').style.display = 'none';
            if (this.data.js.order_detail.seguimiento_tecnico == 18) {
                document.getElementById('id_btn_sign').style.display = '';
            }
            document.getElementById('id_contadores').style.display = 'none';

        } else {
            document.getElementById('id_btn_contadores').style.display = '';
            document.getElementById('id_frm_fileinfo_contadores').style.display = '';
            if (this.data.js.order_detail.seguimiento_tecnico == 18) {
                document.getElementById('id_btn_sign').style.display = '';
            }
            document.getElementById('id_contadores').style.display = '';
        }
    },
    _rb_sin_cobertura: function(e) {
        this.data.js.order_detail.seguimiento_tecnico = document.getElementById("seguimiento_tecnico").value;
        if (document.getElementById('rb_sin_cobertura').checked) {
            document.getElementById('id_btn_cobertura').style.display = '';
            document.getElementById('id_frm_fileinfo_ot').style.display = '';
            if (this.data.js.order_detail.seguimiento_tecnico == 18) {
                document.getElementById('id_btn_sign').style.display = 'none';
            }

        } else {
            document.getElementById('id_btn_cobertura').style.display = 'none';
            document.getElementById('id_frm_fileinfo_ot').style.display = 'none';
            if (this.data.js.order_detail.seguimiento_tecnico == 18) {
                document.getElementById('id_btn_sign').style.display = '';
            }
        }
    },


    _btn_sign: function(e) {

        RightNow.Event.fire("evt_ShippingInstructions", this.instanceID);

        var btn = e._currentTarget;

        btn.setAttribute('disabled', 'disabled');


        this.data.js.order_detail.action = 1;


        this.data.js.order_detail.select_status = document.getElementById("select_status").value;
        this.data.js.order_detail.seguimiento_tecnico = document.getElementById("seguimiento_tecnico").value;
        this.data.js.order_detail.gasto = document.getElementById("gasto").value;
        this.data.js.order_detail.expend_type = document.getElementById("expend_type").value;
        this.data.js.order_detail.gsto_detail = document.getElementById("gsto_detail").value;

        this.data.js.order_detail.tipo_contrato = document.getElementById("tipo_contrato").value;
        this.data.js.order_detail.nota = document.getElementById("nota").value;
        this.data.js.order_detail.NoDataMobile = document.getElementById("rb_sin_cobertura").checked;
        this.data.js.order_detail.status_prev = document.getElementById("id_status_prev").value;
        this.data.js.order_detail.VisitNumber = document.getElementById("VisitNumber").value;
        if (document.getElementById("VisitNumber").value == "") {
            this.data.js.order_detail.VisitNumber = 1;
        }
        this.data.js.order_detail.tipo_id = document.getElementById("tipo_id").value;
        if (this.data.js.order_detail.seguimiento_tecnico == 18 || this.data.js.order_detail.seguimiento_tecnico == 43) {
            this.data.js.order_detail.equipo_detenido = document.getElementById("rb_equipo_detenido").checked;
            this.data.js.order_detail.AlternativeEmails = document.getElementById("AlternativeEmails").value;
            this.data.js.order_detail.cont1_hh = document.getElementById("cont1_hh").value;
            this.data.js.order_detail.cont2_hh = document.getElementById("cont2_hh").value;
            this.data.js.order_detail.motivo_solucion = document.getElementById("motivo_solucion").value;

            this.data.js.order_detail.diagnostico = document.getElementById("diagnostico").value;

        }
        this.data.js.order_detail.disposition = document.getElementById("disposition").value;
        if (this.data.js.order_detail.seguimiento_tecnico == 18) {
            this.data.js.order_detail.equipo_detenido = document.getElementById("rb_equipo_detenido").checked;
            this.data.js.order_detail.Description = document.getElementById("Description").value;
            this.data.js.order_detail.Solution = document.getElementById("Solution").value;
            this.data.js.order_detail.Temperture = document.getElementById("Temperture").value;
            this.data.js.order_detail.IssueCausa = document.getElementById("IssueCausa").value;
            this.data.js.order_detail.ElectricalCondition = document.getElementById("ElectricalCondition").value;
            this.data.js.order_detail.EnviromentCondit = document.getElementById("EnviromentCondit").value;

            this.data.js.order_detail.Reception_Name = document.getElementById("Reception_Name").value;



        }
        if ((this.data.js.order_detail.disposition == 27 || this.data.js.order_detail.disposition == 28) && this.data.js.order_detail.seguimiento_tecnico == 18) {
            this.data.js.order_detail.IpNumber = document.getElementById("IpNumber").value;
            this.data.js.order_detail.Copy = document.getElementById("Copy").value;
            this.data.js.order_detail.Scan = document.getElementById("Scan").value;
            this.data.js.order_detail.Printer = document.getElementById("Printer").value;
            this.data.js.order_detail.Fax = document.getElementById("Fax").value;
            this.data.js.order_detail.motivo_solucion = document.getElementById("motivo_solucion").value;
            this.data.js.order_detail.PrintFlow = document.getElementById("PrintFlow").value;
            this.data.js.order_detail.OperatingSystem = document.getElementById("OperatingSystem").value;

            this.data.js.order_detail.Area = document.getElementById("Area").value;
            this.data.js.order_detail.CostCenter = document.getElementById("CostCenter").value;
        }
        RightNow.Ajax.makeRequest('/cc/ServiceWeb/updateIncidentState', {
            data: JSON.stringify(this.data.js),
        }, {
            scope: {
                _btn: btn,
                _action: 1,
                _parent: this
            },
            successHandler: function(e) {
                var title = 'Error';
                var msge = 'Ocurrió un error inesperado.';
                var msgBN = 'Contador B/N Menor al Actual.';
                var msgColor = 'Contador B/N Menor al Actual.';

                var response = e.response;
                var msg = "";



                msg = response.msg;

                if (response.status !== true || response.validar > 0) {
                    RightNow.UI.Dialog.messageDialog(msg, {
                        title: title,
                        exitCallback: function() {
                            document.location.reload();
                        }
                    });
                } else {

                    location.href = "/app/reparacion/signature/ref_no/" + response.ref_no;



                }
            },
            failureHandler: function(e) {
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

    _clearData: function() {

        this.data.js = Integer._extend({}, this.js_default, this.data.js);


    },



    /**
     * Almacena la solicitud como borrador.
     * TODO: Bloquear todos los botones de la interfaz una vez ejecutado el evento.
     *
     * @param {Event} Mouse Event
     */
    _cancel: function(e) {
        //document.location.reload();

        document.location.href = '/app/reparacion/last_request'
    },

    /**
     * Realiza la solicitud de repuestos.
     *
     * @param {Event} Mouse Event
     */
    _request: function(e) {
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

        RightNow.Event.fire("evt_ShippingInstructions", this.instanceID);

        var btn = e._currentTarget;

        btn.setAttribute('disabled', 'disabled');


        this.data.js.order_detail.action = action;

        this.data.js.order_detail.select_status = document.getElementById("select_status").value;
        this.data.js.order_detail.seguimiento_tecnico = document.getElementById("seguimiento_tecnico").value;
        this.data.js.order_detail.gasto = document.getElementById("gasto").value;
        this.data.js.order_detail.expend_type = document.getElementById("expend_type").value;
        this.data.js.order_detail.gsto_detail = document.getElementById("gsto_detail").value;

        this.data.js.order_detail.tipo_contrato = document.getElementById("tipo_contrato").value;
        this.data.js.order_detail.nota = document.getElementById("nota").value;
        this.data.js.order_detail.NoDataMobile = document.getElementById("rb_sin_cobertura").checked;
        this.data.js.order_detail.status_prev = document.getElementById("id_status_prev").value;
        this.data.js.order_detail.VisitNumber = document.getElementById("VisitNumber").value;
        if (document.getElementById("VisitNumber").value == "") {
            this.data.js.order_detail.VisitNumber = 1;
        }
        this.data.js.order_detail.tipo_id = document.getElementById("tipo_id").value;
        if (this.data.js.order_detail.seguimiento_tecnico == 18 || this.data.js.order_detail.seguimiento_tecnico == 43) {
            this.data.js.order_detail.equipo_detenido = document.getElementById("rb_equipo_detenido").checked;
            this.data.js.order_detail.AlternativeEmails = document.getElementById("AlternativeEmails").value;
            this.data.js.order_detail.cont1_hh = document.getElementById("cont1_hh").value;
            this.data.js.order_detail.cont2_hh = document.getElementById("cont2_hh").value;
            this.data.js.order_detail.motivo_solucion = document.getElementById("motivo_solucion").value;
            this.data.js.order_detail.diagnostico = document.getElementById("diagnostico").value;

        }
        this.data.js.order_detail.disposition = document.getElementById("disposition").value;
        if (this.data.js.order_detail.seguimiento_tecnico == 18) {
            this.data.js.order_detail.equipo_detenido = document.getElementById("rb_equipo_detenido").checked;
            this.data.js.order_detail.AlternativeEmails = document.getElementById("AlternativeEmails").value;
            this.data.js.order_detail.Description = document.getElementById("Description").value;
            this.data.js.order_detail.Solution = document.getElementById("Solution").value;
            this.data.js.order_detail.Temperture = document.getElementById("Temperture").value;
            this.data.js.order_detail.IssueCausa = document.getElementById("IssueCausa").value;
            this.data.js.order_detail.ElectricalCondition = document.getElementById("ElectricalCondition").value;
            this.data.js.order_detail.EnviromentCondit = document.getElementById("EnviromentCondit").value;

            this.data.js.order_detail.Reception_Name = document.getElementById("Reception_Name").value;

        }
        if ((this.data.js.order_detail.disposition == 27 || this.data.js.order_detail.disposition == 28) && this.data.js.order_detail.seguimiento_tecnico == 18) {

            this.data.js.order_detail.IpNumber = document.getElementById("IpNumber").value;
            this.data.js.order_detail.Copy = document.getElementById("Copy").value;
            this.data.js.order_detail.Scan = document.getElementById("Scan").value;
            this.data.js.order_detail.Printer = document.getElementById("Printer").value;
            this.data.js.order_detail.Fax = document.getElementById("Fax").value;
            this.data.js.order_detail.motivo_solucion = document.getElementById("motivo_solucion").value;
            this.data.js.order_detail.PrintFlow = document.getElementById("PrintFlow").value;
            this.data.js.order_detail.OperatingSystem = document.getElementById("OperatingSystem").value;
            this.data.js.order_detail.Area = document.getElementById("Area").value;
            this.data.js.order_detail.CostCenter = document.getElementById("CostCenter").value;

        }
        //console.log("2->%s", JSON.stringify(this.data.js));
        RightNow.Ajax.makeRequest('/cc/ServiceWeb/updateIncidentState', {
            data: JSON.stringify(this.data.js),
        }, {
            scope: {
                _btn: btn,
                _action: 1,
                _parent: this
            },
            successHandler: function(e) {
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
                        exitCallback: function() {
                            document.location.reload();
                        }
                    });
                } else {
                    RightNow.UI.Dialog.messageDialog('Datos Guardados Exitosamente', {
                        title: 'Ticket ' + response.ref_no,
                        exitCallback: function() {
                            if (response.select_status == 43 || response.select_status == 24) {

                                if (response.tipo_contrato == 'Cargo') {
                                    document.location.href = '/app/reparacion/cargo';
                                } else {
                                    document.location.href = '/app/reparacion/home'
                                }
                            } else {
                                if (response.select_status == 19 || response.select_status == 98) {
                                    document.location.href = '/app/reparacion/last_request'

                                } else {

                                    document.location.reload();
                                }
                            }
                        }
                    });

                }

            },
            failureHandler: function(e) {
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