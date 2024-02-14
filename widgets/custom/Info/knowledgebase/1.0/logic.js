RightNow.namespace('Custom.Widgets.Info.knowledgebase');
Custom.Widgets.Info.knowledgebase = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {
        window.widget_Info_knowledgebase = this;
        this.widget = this.Y.one(this.baseSelector);
        // Get the modal
        var modal = document.getElementById("myModal");
        // Get the button that opens the modal
        var btn = document.getElementById("myBtn");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];
        this.btn_continuar = this.widget.one("#btn_continuar");
        this.btn_continuar.on('click', this.handler_btn_continuar, this);
        modal.style.display = "block";
    },
    /**
     * Envía el formulario de solicitud
     *
     * @param e {event}
     */
    handler_btn_continuar: function() {
        var modal = document.getElementById("myModal");
        modal.style.display = "none";
    },
    /**
     * Sample widget method.
     */
    methodName: function() {

    }


});