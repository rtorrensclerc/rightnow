RightNow.namespace('Custom.Widgets.parts.SearchList');
Custom.Widgets.parts.SearchList = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {
        this.results_table = this.Y.one(this.baseSelector + " table");
        this.results_tbody = this.results_table.one("tbody");
        this.list_items = {};

        RightNow.Event.subscribe("evt_SetDataItems", this._setDataItems, this);
        this._noResults();

        this.item_default = {
            "delete": false,
            "id": 0,
            "line_id": undefined,
            "name": '',
            "code_delfos": 0,
            "partNumber": 0,
            "quantity": 1,
            "stock": 0
        };
    },

    _showResults: function() {
        this.results_tbody.setHTML('');

        for (var item in this.list_items) {
            this._addItem(this.list_items[item]);
        }

        this.btns_quantity = this.Y.all(this.baseSelector + " .rn_ControlButton");
        this.btns_add = this.Y.all(this.baseSelector + " .rn_ItemAdd");

        this.btns_quantity.on("click", this._setQty, this);
        this.btns_add.on("click", this._addItemRequest, this);
    },

    /**
     *
     */
    _addItemRequest: function(e) {
        var row = e.currentTarget.get('parentNode').get('parentNode').get('parentNode');
        var index = row.get('rowIndex');
        var dataItem = this.list_items[index - 1];

        RightNow.Event.fire("evt_AddItem", dataItem);
    },

    _setDataItems: function(e, data) {
        // data = JSON.parse(data);
        // this.list_items = data.response.list_items;
        this.list_items = data[0].list_items;
        if (this.list_items.length) {
            // Define la estructura base
            for (var item in this.list_items) {
                this.list_items[item] = Integer._extend({}, this.item_default, this.list_items[item]);
            }
            this._showResults();
        } else {
            this._noResults();
        }
    },

    /**
     *
     */
    _noResults: function() {
        var row = '<tr><td colspan="5">No hay resultados</td></tr>';

        this.results_tbody.setHTML(row);
    },

    /**
     *
     */
    _addItem: function(item) {

      var stock="";
      if(item.stock==0)
      {
        stock='SIN STOCK';
        item.quantity=0;
      }
      else {
        stock='OK';
        item.quantity=1;
      }
        var row = '<tr><td data-title="Código Delfos">' + item.code_delfos + '</td>';
        row += '<td data-title="Número de Parte">' + item.partNumber + '</td>';
        row += '<td data-title="Nombre">' + item.name + '</td>';
        row += '<td data-title="Cantidad Seleccionada"><div class="wrap rn_Controls"><button type="button" value="-1" class="btn rn_ControlButton rn_DecreaseButton"><span class="ico_decrease rn_Assets"></span></button><span class="rn_Quantity">';
        row += item.quantity + '</span><button type="button" value="1" class="btn rn_ControlButton rn_IncreaseButton"><span class="ico_increase rn_Assets"></span></button></div></td>';
        row += '<td data-title="Agregar"><div class="wrap"><button type="button" value="false" class="btn btn-green rn_ItemAdd"><span class="ico_add rn_Assets"></span></button></div></td>';
        row += '<td data-title="Stock">' + stock  + '</td></tr>';
        this.results_tbody.setHTML(this.results_tbody.getHTML() + row);
    },

    /**
     *
     */
    _setQty: function(e) {
        var btn = e.currentTarget;
        var content = btn.get('parentNode');
        var quantity = content.one('span.rn_Quantity');
        var index = e.currentTarget.get('parentNode').get('parentNode').get('parentNode').get('rowIndex');
        var dataItem = this.list_items[index - 1];

        var addValue = parseInt(btn.get('value'));
        var actualValue = parseInt(dataItem.quantity);
        // var actualValue = 5;
        var totalValue = actualValue + addValue;

        if (totalValue <= 0) {
            totalValue = 1;
        }

        quantity.setHTML(totalValue);
        dataItem.quantity = totalValue;
    }

});
