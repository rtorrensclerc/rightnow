RightNow.namespace('Custom.Widgets.Info.waiting');
Custom.Widgets.Info.waiting = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {
        this.widget = this.Y.one(this.baseSelector);
        // Get the modal
        var modal = document.getElementById("myModal");
        modal.style.display = "block";
      
    },

    /**
     * Sample widget method.
     */
    methodName: function() {

    }
    
    }
);