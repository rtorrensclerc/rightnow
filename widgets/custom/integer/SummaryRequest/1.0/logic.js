RightNow.namespace('Custom.Widgets.integer.SummaryRequest');
Custom.Widgets.integer.SummaryRequest = RightNow.Widgets.extend({
    /**
     * Constructor.
     */
    constructor: function() {
        if(this.Y.one('textarea[name="shipping_instructions"]'))
        {
          this.shippingInstructions = this.Y.one('textarea[name="shipping_instructions"]');
          RightNow.Event.subscribe("evt_ShippingInstructions", this._getShippingInstructions, this);
      
          this.despachar =  this.Y.one(this.baseSelector + ' [name="despachar"]');
        
        
          if (this.despachar) this.despachar.on("change", this._despachar, this);
        } 
    },
    _despachar:function(e){
        
        
        switch(this.despachar._node.value) {
            case "1":
                this.shippingInstructions._node.value='Enviar a ' + this.despachar._node[1].innerText;
                this.shippingInstructions.setAttribute('disabled', 'disabled');
              break;
            case "2":
                this.shippingInstructions._node.value='Entregar en sucursal Currier';
                this.shippingInstructions.setAttribute('disabled', 'disabled');
              break;
            case "3":
                this.shippingInstructions._node.value='Enviar a  Dirección del Cliente';
                this.shippingInstructions.setAttribute('disabled', 'disabled');
              break;  
            case "4":
                this.shippingInstructions._node.value='';
                this.shippingInstructions.removeAttribute('disabled', 'disabled');
              break;
            default:
                this.shippingInstructions._node.value='';
                this.shippingInstructions.setAttribute('disabled', 'disabled');
                
          }
         

    },
    /**
     * @param {Event}
     * @param {String}
     */
    _getShippingInstructions: function(e, instanceID) {
        var instance = RightNow.Widgets.getWidgetInstance(instanceID);
        if (this.shippingInstructions)
            instance.data.js.order_detail.shipping_instructions = this.shippingInstructions._node.value;
    }
});
