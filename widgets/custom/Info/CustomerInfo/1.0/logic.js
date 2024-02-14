RightNow.namespace('Custom.Widgets.Info.CustomerInfo');

Custom.Widgets.Info.CustomerInfo = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     * 
     */

    constructor: function() {
        window.widget_CustomerInfo = this;
        var bloqued = "0";
        var nodeDom = "<div id='rn_landing'>";


        nodeDom += '<div class="rn_FieldDisplay rn_Output "  align="center">';
        //nodeDom += "<p>" + JSON.stringify(this.data.js.main) +"</p>";

        nodeDom += '<div>';
        nodeDom += '<p ><h1>Nuestros Sistemas indican que existen restricciones para generar solicitudes ';
        nodeDom += 'RUT : ' + this.data.js.datos.Customer.CustomerData.Customer.tRUT + '</p>';
        if (this.data.js.datos.Customer.CustomerData.Customer.tBLOQUEADO == "SI" || this.data.js.datos.Customer.CustomerData.Customer.tbloqued == "Y") {


            if (this.data.js.datos.Customer.CustomerData.Customer.tBLOQUEADO == "SI" || this.data.js.datos.Customer.CustomerData.Customer.tbloqued == "Y") {
                bloqued = "1";
            }
            nodeDom += '</div >';
        }






        if (bloqued == "1" && (this.data.js.datos.Customer.CustomerData.Customer.tBLOQUEADO == 'SI' || this.data.js.datos.Customer.CustomerData.Customer.tbloqued == "Y")) {
            nodeDom += '<p><h2>Facturas pendientes de pago</h2></p>';
            nodeDom += '<p><h2>solicitar detalle a ';
            nodeDom += '<b>cobranza@dimacofi.cl</b> o ver en el módulo de ';
            nodeDom += '<a href="/app/sv/billing_payments/invoice_payments">facturación</a>';
            nodeDom += ' de esta plataforma.</h2></p>';
            this.bloqued = "Y";
            nodeDom += '<div style="border:3px;border-style:solid;">';
            nodeDom += '<table border="1" width="100%" >';

            if (this.data.js.datos.Invoice.InvoiceData == undefined || this.data.js.datos.Invoice.InvoiceData === null) {
                x = 1;
            } else {


                if (this.data.js.datos.Invoice.InvoiceData.Invoices.TRX_NUMBER == undefined) {


                    this.data.js.datos.Invoice.InvoiceData.Invoices.forEach(element => {

                        if (element.CT_REFERENCE == null) {
                            Contrato = '-';
                        } else {
                            Contrato = element.CT_REFERENCE;
                        }
                        nodeDom += '<tr><th>Nro Factura: ' + element.TRX_NUMBER + '</th>';
                        nodeDom += '<th>Contrato: ' + Contrato + '</th>';
                        nodeDom += '<th>Monto: $ ' + element.AMOUNT.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ".") + '</th>';
                        dt1 = new Date(element.DUE_DATE);
                        nodeDom += '<th>Fecha:  ' + element.DUE_DATE + ' </th></tr>';
                    });

                } else {
                    if (this.data.js.datos.Invoice.InvoiceData.Invoices.CT_REFERENCE == null) {
                        Contrato = '-';
                    } else {
                        Contrato = this.data.js.datos.Invoice.InvoiceData.Invoices.CT_REFERENCE;
                    }
                    nodeDom += '<tr><th>Nro Factura : ' + this.data.js.datos.Invoice.InvoiceData.Invoices.TRX_NUMBER + ' </th>';
                    nodeDom += '<th>Contrato: ' + Contrato + ' </th>';
                    nodeDom += '<th>Monto: $ ' + this.data.js.datos.Invoice.InvoiceData.Invoices.AMOUNT.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ".") + ' </th>';
                    dt1 = new Date(this.data.js.datos.Invoice.InvoiceData.Invoices.DUE_DATE);
                    nodeDom += '<th>Fecha:  ' + this.data.js.datos.Invoice.InvoiceData.Invoices.DUE_DATE + ' </th></tr>';
                }
            }
            nodeDom += "</table>";
            nodeDom += '</div >';

        }

        if (this.data.js.datos.Customer.CustomerData.Customer.tBLOQUEO_DEUDAS == 'SI') {
            nodeDom += '<p> Bloqueado por Castigo de Deudas Antiguas favor contactar a <B>cobranza@dimacofi.cl</B> </p>';
        }
        if (this.data.js.datos.Customer.CustomerData.Customer.tBLOQUEO_FACTURACION == 'SI') {
            nodeDom += '<p> Bloqueado por Rechazo de Facturas favor contactar a <B>administracion@dimacofi.cl</B></p>';
        }
        if (this.data.js.datos.Customer.CustomerData.Customer.tBLOQUEO_INFORMACION == 'SI') {
            nodeDom += '<p> Bloqueado por Informacion Financiera incompleta favor contactar a <B>facturacion@dimacofi.cl</B> </p>';
        }
        if (this.data.js.datos.Customer.CustomerData.Customer.tBLOQUEO_RIESGO == 'SI') {
            nodeDom += '<p> Bloqueado por Situacion de Riesgo favor contactar a <B>credito@dimacofi.cl</B></p>';
        }
        if (bloqued == "1" || bloqued == "2") {

            nodeDom += '<p><h2><B> En caso quenos envie correo, favorindicar el RUT y Razón Social </B></h2></p>';
        }


        nodeDom += '<div>';
        nodeDom += '<p><h2>"En caso de cualquier consulta adicional favor  dejar su inquietud en el módulo <a href="https://soportedimacoficl.custhelp.com/app/sv/request/contact">Servicio al Cliente</a>"</h2></p>';
        nodeDom += '<div>';

        if (this.data.js.datos.BlockAddress.List && 1 == 1) {

            nodeDom += '<div style="border:3px;border-style:solid;border-radius: 20px;"  align="center">';
            nodeDom += '<p>' + 'NO podra solicitar algunos servicios Hasta que regularice situación en estas Sucursales.' + '</p>';
            nodeDom += '<table border="1" width="100%" >';
            if (this.data.js.datos.BlockAddress.List.data.DIRECCION == undefined) {

                this.data.js.datos.BlockAddress.List.data.forEach(element => {
                    nodeDom += '<tr><td data-title="Sucursal" align="left">' + element.DIRECCION.substring(0, 70) + '</td><td data-title="Estado">BLOQUEADA</td></tr>';
                    bloqued = "2";
                });

            } else {
                nodeDom += '<tr><td align="left">' + this.data.js.datos.BlockAddress.List.data.DIRECCION.substring(0, 30) + '</td><td>BLOQUEADA POR CREDITO</td></tr>';
                bloqued = "2";
            }
            nodeDom += '</table>';
            nodeDom += '</div >';
        }

        nodeDom += '</div>';
        nodeDom += '</div>';
        nodeDom += '</div>';

        dialogDiv = this.Y.Node.create(nodeDom);
        var dialogOptions = {
            'cssClass': 'rn_showDialog_dialog',
            exitCallback: function() {
                document.location.reload();
            }
        };

        this._dialog = RightNow.UI.Dialog.actionDialog('Atención', dialogDiv, dialogOptions);
        if (bloqued == 1 || bloqued == 2) {
            this._dialog.show();
        }
    }
});