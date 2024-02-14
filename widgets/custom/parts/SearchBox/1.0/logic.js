RightNow.namespace('Custom.Widgets.parts.SearchBox');
Custom.Widgets.parts.SearchBox = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {
        this.btn_search = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_search"]');
        this.btn_clear = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_clear"]');

        this.txt_partCode = this.Y.one(this.baseSelector + ' input[name="txt_partCode"]');
        this.txt_delfosCode = this.Y.one(this.baseSelector + ' input[name="txt_delfosCode"]');
        this.select_type = this.Y.one(this.baseSelector + ' select[name="select_type"]');
        
        this.btn_search.on("click", this._search, this);
        this.btn_clear.on("click", this._clear, this);

        this.item_default = {
            "delete": false,
            "id": 0,
            "line_id": undefined,
            "name": 0,
            "partNumber": 0,
            "quantity": 1
        };
    },

    /**
     *
     */
    _clear: function(e) {
        this.txt_partCode.set('value', '');
        this.txt_delfosCode.set('value', '');
        if(this.select_type) this.select_type.set('value', 0);
    },

    /**
     *
     */
    _search: function(e) {

        var btn = e.currentTarget;

        var data = {};
        data.search_items = {};
        data.search_items.type_id = 40; // Disposición (40 o 41)
        data.search_items.q_partCode = this.txt_partCode.get('value');
        data.search_items.q_delfosCode = this.txt_delfosCode.get('value');
        data.search_items.q_type = (this.data.js.onlyParts)?2:this.select_type.get('value');
        data.search_items.ref_no=this.data.attrs.ref_no;
        
        this.btn_search.setAttribute('disabled', 'disabled');
        RightNow.Ajax.makeRequest('/cc/ServiceReparation/searchProducts', {
            data: JSON.stringify(data)
        }, {
            scope: {
                _btn: btn
            },
            successHandler: function(e) {
                this._btn.removeAttribute('disabled');

                // var response = JSON.parse(e.responseText);
                var response = e.response;

                if (typeof response.errors !== 'undefined') {
                    var title = 'Error';
                    var msg = response.errors.message;

                    RightNow.UI.Dialog.messageDialog(msg, {
                        title: title
                    });
                } else {
                    // var setDataItems = RightNow.Event.fire("evt_SetDataItems", e.responseText);
                    var setDataItems = RightNow.Event.fire("evt_SetDataItems", e.response);
                }
            },
            failureHandler: function(e) {
                this._btn.removeAttribute('disabled');

                var title = 'Error';
                var msg = 'Error del servicio.';

                RightNow.UI.Dialog.messageDialog(msg, {
                    title: title
                });
            }
        });
    }
});
