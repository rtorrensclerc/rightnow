RightNow.namespace('Custom.Widgets.Info.PopUpNoMonitor');

Custom.Widgets.Info.PopUpNoMonitor = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     * 
     */

    constructor: function () {
        window.widget_PopUpNoMonitor = this;
        var bloqued = "0";
        var nodeDom = '<div   align="center">';
        nodeDom += '<img width="1000px" height="641px" src="/euf/assets/images/POPUPSINMONITOREO.png"/>';

        nodeDom += '<p style="text-align:center;"  align="center"><b><h1>Todas las consultas y solicitudes relacionadas al monitoreo las puedes realizar haciendo';
        nodeDom += ' <a href="https://soportedimacoficl.custhelp.com/app/sv/request/form/p/66" class="buttonlink">Clic Acá</a>';
        nodeDom += '</h1></b></p></div>';

        dialogDiv = this.Y.Node.create(nodeDom);
        var dialogOptions = {
            exitCallback: function () {
                document.location.reload();

            }
        };

        this._dialog = RightNow.UI.Dialog.actionDialog('Atención', dialogDiv, dialogOptions);

        this._dialog.show();
    }
});