RightNow.namespace('Custom.Widgets.dimacofi.PredictionList.PredictionList');
Custom.Widgets.dimacofi.PredictionList.PredictionList = RightNow.Widgets.extend({ 
       /**
     * Widget constructor.
     */
    constructor: function() {
		
		

		
        this.results_table = this.Y.one(this.baseSelector + " table");
        this.results_tbody = this.results_table.one("tbody");
        this.list_items = {};

        RightNow.Event.subscribe("evt_setDataRecomendados", this._setDataRecomendados, this);

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

    _showResultsPrediction: function() {
        this.results_tbody.setHTML('');

        for (var item in this.list_items) {
			if(this.list_items[item].id != 0){
            this._addItemPrediction(this.list_items[item]);
			}
			
        }
		
        this.btns_quantity = this.Y.all(this.baseSelector + " .rn_ControlButton");
        this.btns_add = this.Y.all(this.baseSelector + " .rn_ItemAdd");
		

        this.btns_quantity.on("click", this._setQtyPrediction, this);
        this.btns_add.on("click", this._showActiondialog , this);
		
    },

    /**
     *
     */
    _addItemRequestPrediction: function(e) {
		
        var row = e.currentTarget.get('parentNode').get('parentNode').get('parentNode');
        var index = row.get('rowIndex');
        var dataItem = this.list_items[index-1];
        RightNow.Event.fire("evt_AddItem", dataItem);
    },

    _setDataRecomendados: function(e, data) {

        this.list_items = data[0].list_items;
		console.log(this.list_items);
        if (this.list_items.length) {
            // Define la estructura base
            for (var item in this.list_items) {
                
                this.list_items[item] = Integer._extend({}, this.item_default, this.list_items[item]);
            }
            this._showResultsPrediction();
        } else {
            this._noResultsPrediction();
        }
    },

    /**
     *
     */
    _noResultsPrediction: function() {
        var row = '<tr><td colspan="5">No hay resultados</td></tr>';

        this.results_tbody.setHTML(row);
    },

    /**
     *
     */
    _addItemPrediction: function(item) {
		

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
	  
		item.utilization = !item.utilization ?  0:item.utilization;
        
		
             
				var row = '<tr><td data-title="Código Delfos">' + item.code_delfos + '</td>';
                row += '<td data-title="Número de Parte">' + item.partNumber + '</td>';
                row += '<td data-title="Nombre">' + item.name + '</td>';
                row += '<td data-title="costo">$' +  item.UnitCostPrice.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.")  + '</td>';
				row += '<td data-title="Historico">' + item.historico  + '</td>';
				row += '<td data-title="Utilizacion">' + item.utilization  + '%</td></tr>';
				this.results_tbody.setHTML(this.results_tbody.getHTML() + row);
        
    },

    /**
     *
     */
    _setQtyPrediction: function(e) {
        var btn = e.currentTarget;
        var content = btn.get('parentNode');
        var quantity = content.one('span.rn_Quantity');
        var index = e.currentTarget.get('parentNode').get('parentNode').get('parentNode').get('rowIndex');
        var dataItem = this.list_items[index - 1];

        var addValue = parseInt(btn.get('value'));
        var actualValue = parseInt(dataItem.quantity);
        var totalValue = actualValue + addValue;

        if (totalValue <= 0) {
            totalValue = 1;
        }

        quantity.setHTML(totalValue);
        dataItem.quantity = totalValue;
    },
	
	
		/** Declara e Inicializa el diálogo */
		_showActiondialog: function(e) {
			
		  this.row = e.currentTarget.get('parentNode').get('parentNode').get('parentNode');	 
		  
		  this.message_dom = Y.Node.create('Al agregar este repuesto su solicitud pasara a supervision<br/><br/><strong>¿Desea avanzar?</strong>');

		  this.actionDialog = RightNow.UI.Dialog.actionDialog('Ojo al Charqui!!',
			this.message_dom, {
			buttons: [{
				  text: 'Sí, dale',
				  handler: {
					fn: this._submit,
					scope: this
				  },
				  name: 'submit'
				},
				{
				  text: 'no,que miedo',
				  handler: {
					fn: this._hideDialog,
					scope: this,
					href: 'javascript:void(0)'
				  }
				}
			],
			close: true,
			width: '300px',
			height: '100%'
		  });

		  this.actionDialog.show()
		},
	


		/** Método Submit */
		_submit: function(e,row) {
		  
		  var index = this.row.get('rowIndex');
		  var dataItem = this.list_items[index-1];
		  RightNow.Event.fire("evt_AddItem", dataItem);
		  
		  this.actionDialog.hide()
		  
		},
		
		/** Método Submit */
		_hideDialog: function(e) {
		  this.actionDialog.hide()
		}

});