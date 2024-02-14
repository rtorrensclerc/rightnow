RightNow.namespace('Custom.Widgets.Info.PopUpHour');

Custom.Widgets.Info.PopUpHour = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     * 
     */

    constructor: function () {
        window.widget_PopUpHour = this;
        var bloqued = "0";
        var nodeDom = '<div   align="center">';
        nodeDom += '<img width="1000px" height="641px" src="/euf/assets/images/POPUPVREGION.png"/>';
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