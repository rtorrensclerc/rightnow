RightNow.namespace('Custom.Widgets.integer.RequestList');
Custom.Widgets.integer.RequestList = RightNow.Widgets.extend({

    /**
     * Constructor.
     */
    constructor: function() {
        this.request_table = this.Y.one(this.baseSelector + " table");
        this.request_tbody = this.request_table.one("tbody");

        this.btn_create = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_create"]');
        this.btn_request = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_request"]');
        this.btn_draft = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_draft"]');
        this.btn_cancel = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_cancel"]');
        this.btn_newPart = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_newPart"]');
        this.btns_quantity = this.Y.all(this.baseSelector + " .partRow .rn_ControlButton");
        this.btns_delete = this.Y.all(this.baseSelector + " .partRow .rn_ItemDelete");

        RightNow.Event.subscribe("evt_AddItem", this._addItem, this);

        this.btns_quantity.on("click", this._setQty, this);
        this.btns_delete.on("click", this._hideItem, this);

        if (this.btn_create) this.btn_create.on("click", this._create, this);
        if (this.btn_request) this.btn_request.on("click", this._request, this);
        if (this.btn_draft) this.btn_draft.on("click", this._draft, this);
        this.btn_cancel.on("click", this._cancel, this);
        this.btn_newPart.on("click", this._newPart, this);

        this.js_default = {
            "order_detail": {
                "ref_no": "",
                "external_reference": "",
                "father_ref_no": this.data.attrs.ref_no,
                "shipping_instructions": "",
                "list_items": [],
                "list_items_not_found": []
            }
        };

        this.item_default = {
            "delete": false,
            "id": 0,
            "line_id": undefined,
            "name": 0,
            "partNumber": 0,
            "quantity": 1,
            "new": false
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

    /**
     * Homologa la estructura de datos principal
     */
    _clearData: function() {
        this._loadFromStorage();
        this.data.js = Integer._extend({}, this.js_default, this.data.js);

        if (!this.data.js.order_detail.list_items.length && !this.data.js.order_detail.list_items_not_found.length) {
            this._noResults();
        }
    },

    /**
     * Establece una fila indicando que no existen valores para presentar
     */
    _noResults: function() {
        var row = '<tr><td colspan="4">No hay elementos</td></tr>';

        this.request_tbody.setHTML(row);
    },

    /**
     * Agrega una nueva fila a la tabla de la solicitud, además en caso de no
     * existir un valor previo define el cuerpo de la tabla como vacío.
     *
     * @param {Event}
     * @param {Object} Valores del repuesto a añadir
     */
    _addItem: function(e, item) {
        item = Integer._extend({}, this.item_default, item[0]);
        this.data.js = Integer._extend({}, this.js_default, this.data.js);

        if (!this.data.js.order_detail.list_items.length && !this.data.js.order_detail.list_items_not_found.length)
            this.request_tbody.setHTML('');

        if ((!this._existDataItem(item.id) || item.new) && !this._limitMax()) {
            var row;

            if (item.new) {
                row = '<tr class="newPartRow"><td data-title="Número de Parte"><input type="hidden" name="idPart" value="' + item.id + '" /><input type="text" name="partNumber" value="' + item.partNumber + '" class="newPartInput mandatory' + ((!item.description) ? ' error' : '') + '" /></td>';
                row += '<td data-title="Nombre"><input type="text" name="description" value="' + ((item.description) ? item.description : '') + '" class="newPartInput mandatory' + ((!item.description) ? ' error' : '') + '" /></td>';
                row += '<td data-title="Cantidad"><div class="wrap rn_Controls"><button type="button" value="-1" class="btn rn_ControlButton rn_DecreaseButton"><span class="ico_decrease rn_Assets"></span></button><span class="rn_Quantity">' + item.quantity + '</span><button type="button" value="1" class="btn rn_ControlButton rn_IncreaseButton"><span class="ico_increase rn_Assets"></span></button></div></td>';
                row += '<td data-title="Eliminar"><div class="wrap"><button type="button" value="false" class="btn btn-red rn_ItemDelete"><span class="ico_delete rn_Assets"></span></button></div></td></tr>';
                this.request_tbody.appendChild(row);
            } else {
                row = '<tr class="partRow"><td data-title="Número de Parte"><input type="hidden" name="idPart" value="' + item.id + '" />' + item.partNumber + '</td>';
                row += '<td data-title="Nombre">' + item.name + '</td>';
                row += '<td data-title="Cantidad"><div class="wrap rn_Controls"><button type="button" value="-1" class="btn rn_ControlButton rn_DecreaseButton"><span class="ico_decrease rn_Assets"></span></button><span class="rn_Quantity">' + item.quantity + '</span><button type="button" value="1" class="btn rn_ControlButton rn_IncreaseButton"><span class="ico_increase rn_Assets"></span></button></div></td>';
                row += '<td data-title="Eliminar"><div class="wrap"><button type="button" value="false" class="btn btn-red rn_ItemDelete"><span class="ico_delete rn_Assets"></span></button></div></td></tr>';
                this.request_tbody.insertBefore(row, 0);
            }

            this.btns_quantity = this.Y.all(this.baseSelector + " .partRow .rn_ControlButton");
            this.btnsNewPart_quantity = this.Y.all(this.baseSelector + " .newPartRow .rn_ControlButton");
            this.btns_delete = this.Y.all(this.baseSelector + " .partRow .rn_ItemDelete");
            this.btnsNewPart_delete = this.Y.all(this.baseSelector + " .newPartRow .rn_ItemDelete");

            this.btns_quantity.detach("click", this._setQty, this);
            this.btnsNewPart_quantity.detach("click", this._setQtyNewItem, this);
            this.btns_delete.detach("click", this._hideItem, this);
            this.btnsNewPart_delete.detach("click", this._deleteItem, this);

            this.btns_quantity.on("click", this._setQty, this);
            this.btnsNewPart_quantity.on("click", this._setQtyNewItem, this);
            this.btns_delete.on("click", this._hideItem, this);
            this.btnsNewPart_delete.on("click", this._deleteItem, this);


            if (this.newPartInputs) this.newPartInputs.detach('change', this._changeNewPart, this);
            this.newPartInputs = this.Y.all('.newPartInput');
            this.newPartInputs.on('change', this._changeNewPart, this);

            if (item.new) {
                this.data.js.order_detail.list_items_not_found.push(item);
                // this._setItemStorage(item.id, item);
            } else {
                this.data.js.order_detail.list_items.unshift(item);
            }
        } else {
            this._showItem(this._getIndex(item.id), item.quantity);
        }
    },

    /**
     * Establece como visible una fila de la solicitud de repuestos
     *
     * @param {Integer} Índice del repuesto
     * @param {Integer} Cantidad del repuesto
     */
    _showItem: function(i, quantity) {
        if (i === -1) return false;
        var row = this.request_tbody.get('rows').item(i);

        if (typeof quantity !== 'undefined') {
            var quantity_span = row.one('span.rn_Quantity');
            if (quantity_span) {
                var dataItem = this.data.js.order_detail.list_items[i];

                if (!this._isDeleted(dataItem.id)) {
                    quantity += dataItem.quantity;
                } else if (this._limitMax()) {
                    return false;
                }
                quantity_span.setHTML(quantity);
                dataItem.quantity = quantity;
                dataItem.delete = false;
            }
        }

        row.show();
    },

    /**
     * Elimina una fila de la solicitud de repuesto nuevo
     *
     * @param {Event}
     */
    _deleteItem: function(e) {
        var row = e.currentTarget.get('parentNode').get('parentNode').get('parentNode');
        var id = parseInt(row.one('input[name="idPart"]').get('value'));
        this.data.js.order_detail.list_items_not_found.splice(this._getIndex(id, true), 1);
        var index = row.get('rowIndex');
        row.remove(index);
        // this._removeItemStorage(id);
    },

    /**
     * Oculta una fila de la solicitud de repuestos
     *
     * @param {Event}
     */
    _hideItem: function(e) {
        var row = e.currentTarget.get('parentNode').get('parentNode').get('parentNode');
        var index = row.get('rowIndex');
        var dataItem = this.data.js.order_detail.list_items[this._getActiveDataItemIndex(index - 1)];
        dataItem.delete = true;
        row.hide();
    },

    /**
     * Retorna el índice de la persistencia a partir del ID de repuesto
     *
     * @param {Integer} ID de repuesto
     * @return {Integer} Retorna el índice de la persistencia
     */
    _getIndex: function(id, newItems) {
        var i = 0;
        newItems = newItems || false;

        for (var item in this.data.js.order_detail[(!newItems) ? 'list_items' : 'list_items_not_found']) {
            if (this.data.js.order_detail[(!newItems) ? 'list_items' : 'list_items_not_found'][item].id == id) {
                return i;
            }
            i++;
        }

        return -1;
    },

    /**
     * Retorna la cantidad de registros activos en persistencia
     *
     * @return {Integer} Cantidad de registros activos
     */
    _activeItemsCount: function() {
        var i = 0;
        for (var item in this.data.js.order_detail.list_items) {
            if (!this.data.js.order_detail.list_items[item].delete) {
                i++;
            }
        }

        return i;
    },

    /**
     * Determina si un registro figura como eliminado en la persistencia a
     * partir de tu ID de repuesto.
     *
     * @param {Integer} ID de respuesto
     */
    _isDeleted: function(id) {
        for (var item in this.data.js.order_detail.list_items) {
            if (this.data.js.order_detail.list_items[item].id === id && this.data.js.order_detail.list_items[item].delete) {
                return true;
            }
        }

        return false;
    },

    /**
     * Determina si existe la persistencia un ítem a partir de su ID de repuesto.
     *
     * @param {Integer} ID de respuesto
     */
    _existDataItem: function(id) {
        for (var item in this.data.js.order_detail.list_items) {
            if (this.data.js.order_detail.list_items[item].id === id) {
                return true;
            }
        }

        return false;
    },

    /**
     * Retorna el indice activo en la persistencia según su equivalente visual
     * en la tabla de solicitud.
     *
     * @param {Integer} Índice del repuesto en la tabla de solicitud
     * @param {Integer} Índice del repuesto activo en la persistencia
     */
    _getActiveDataItemIndex: function(index, newItems) {
        var activeIndex = 0;
        newItems = newItems || false;
        for (var item in this.data.js.order_detail[(!newItems) ? 'list_items' : 'list_items_not_found']) {
            if (!this.data.js.order_detail[(!newItems) ? 'list_items' : 'list_items_not_found'][item].delete) {
                if (index === activeIndex)
                    return activeIndex;
                activeIndex++;
            }
        }

        return activeIndex;
    },

    /**
     * Establece la cantidad de un ítem
     *
     * @param {Event}
     */
    _setQty: function(e) {
        var btn = e.currentTarget;
        var content = btn.get('parentNode');
        var quantity = content.one('span.rn_Quantity');
        var id = content.get('parentNode').get('parentNode').one('input[name="idPart"]').get('value');
        var dataItem = this.data.js.order_detail.list_items[this._getIndex(id)];

        var addValue = parseInt(btn.get('value'));
        var actualValue = parseInt(dataItem.quantity);
        var totalValue = actualValue + addValue;

        if (totalValue <= 0) {
            totalValue = 1;
        }

        quantity.setHTML(totalValue);
        dataItem.quantity = totalValue;
    },

    /**
     * Crea la solicitud de repuestos
     * TODO: Bloquear todos los botones de la interfaz una vez ejecutado el evento.
     *
     * @param {Event} Mouse Event
     */
    _create: function(e) {
        if (this._validateLines())
            this._setOrder(e, 1);
    },

    /**
     * Almacena la solicitud como borrador.
     * TODO: Bloquear todos los botones de la interfaz una vez ejecutado el evento.
     *
     * @param {Event} Mouse Event
     */
    _cancel: function(e) {
        document.location.reload();
    },

    /**
     * Realiza la solicitud de repuestos.
     *
     * @param {Event} Mouse Event
     */
    _request: function(e) {
        if (this._validateLines())
            this._setOrder(e, 3);
    },

    /**
     * Almacena la solicitud como borrador.
     *
     * @param {Event} Mouse Event
     */
    _draft: function(e) {
        this._setOrder(e, 2);
    },

    // -----------------------------------------------------------------------------
    // REPUESTOS NUEVOS
    // -----------------------------------------------------------------------------

    /*
     * Cambia los valores el modelo persistente de los nuevos repuestos
     */
    _changeNewPart: function(obj, value, atribute, id) {
        if (obj) {
            var line = obj.currentTarget.get('parentNode').get('parentNode');
            id = parseInt(line.one('input[name="idPart"]').get('value'));
            atribute = obj.currentTarget.get('name');
            value = obj.currentTarget.get('value');

            if (!value.length) {
                obj.currentTarget.addClass('error');
            } else {
                obj.currentTarget.removeClass('error');
            }
        }
        var data = this.data.js.order_detail.list_items_not_found[this._getIndex(id, true)];
        // var storageItem = this._getItemStorageFromId(id);

        data[atribute] = value;
        // storageItem[atribute] = value;

        // this._setItemStorage(storageItem.id, storageItem);
    },

    /**
     * Crea una línea de repuesto nuevo
     *
     * @param {Event} Mouse Event
     */
    _newPart: function(e) {
        this._addItem('_newPart', [{
            "delete": false,
            "id": this._generateID(),
            "name": "",
            "code_delfos": "",
            "partNumber": "",
            "quantity": 1,
            "new": true
        }]);
    },

    /**
     * Genera un correlativo para identificar las persitencias locales las líneas
     * de nuevos repuestos
     *
     * @return {Integer}
     */
    _generateID: function() {
        if (!this.data.js.order_detail.list_items_not_found.length)
            return 0;

        return Math.max.apply(null, this.data.js.order_detail.list_items_not_found.map(function(d) {
            return d.id;
        })) + 1;
    },

    // -----------------------------------------------------------------------------
    // NUEVOS REPUESTOS -> LOCALSTORAGE
    // -----------------------------------------------------------------------------

    /**
    * Obtiene un elemento por su ID o ID compuesta
    *
    * @param {Integer} | {String}
    * @param {String} | {String} [opcional]
    * @return {Object} | false
    */
    _getItemStorageFromId: function(id, father_ref_no) {
        father_ref_no = father_ref_no || this.data.js.order_detail.father_ref_no;

        if (typeof id === 'number')
            id = this.data.js.order_detail.father_ref_no + '_' + id;

        for (var storage in window.localStorage) {
            var item = JSON.parse(window.localStorage[storage]);
            if (parseInt(id.replace(/.*?_/, '')) === item.id) {
                return item;
            }
        }

        return false;
    },

    /**
    * Define o reemplza un elemento por su ID o ID compuesta
    *
    * @param {Integer} | {String}
    * @param {String}
    * @return false
    */
    _setItemStorage: function(id, value) {
        if (typeof value !== 'string')
            value = JSON.stringify(value);

        if (typeof id === 'number')
            id = this.data.js.order_detail.father_ref_no + '_' + id;

        window.localStorage.setItem(id, value);

        return true;
    },

    /**
    * Elimina un elemento por su ID o ID compuesta
    *
    * @param {Integer} | {String}
    * @return true
    */
    _removeItemStorage: function(id) {
        if (typeof id === 'number')
            id = this.data.js.order_detail.father_ref_no + '_' + id;

        window.localStorage.removeItem(id);

        return true;
    },

    /**
    * Eliminar todos los elementos asociados a una solicitud
    *
    * @param {String} | {String} [opcional]
    * @return true
    */
    _removeItemsStorage: function(father_ref_no) {
        //father_ref_no = father_ref_no || this.data.js.order_detail.father_ref_no;
        father_ref_no = father_ref_no || this.data.attrs.ref_no;

        for (var storage in window.localStorage) {
            if (storage.indexOf(father_ref_no) !== -1) {
                var item = JSON.parse(window.localStorage[storage]);
                this._removeItemStorage(storage);
            }
        }

        return true;
    },

    /**
    * Carga al modelo todos los elementos asociados a una solicitud almacenados
    * localmente
    *
    * @param {String} | {String} [opcional]
    */
    _loadFromStorage: function(father_ref_no) {
        //father_ref_no = father_ref_no || this.data.js.order_detail.father_ref_no;
        father_ref_no = father_ref_no || this.data.attrs.ref_no;

        for (var storage in window.localStorage) {
            if (storage.indexOf(father_ref_no) !== -1) {
                var item = JSON.parse(window.localStorage[storage]);
                this._addItem('_loadFromStorage', [item]);
            }
        }
    },

    /**
    * almacena localmente todos los elementos asociados a una solicitud
    * persistentes en el modelo
    *
    * @param {String} | {String} [opcional]
    */
    _loadToStorage: function(father_ref_no) {
        //father_ref_no = father_ref_no || this.data.js.order_detail.father_ref_no;
        father_ref_no = father_ref_no || this.data.attrs.ref_no;

        this._removeItemsStorage(father_ref_no);

        for (var item in this.data.js.order_detail.list_items_not_found) {
            this._setItemStorage(this.data.js.order_detail.list_items_not_found[item].id, this.data.js.order_detail.list_items_not_found[item]);
        }

        return true;
    },

    // -----------------------------------------------------------------------------
    // VALIDACIONES
    // -----------------------------------------------------------------------------

    /**
     * Todo input inculpado de delito tiene derecho a que se presuma su inocencia
     * mientras no se establezca programaticamente su culpabilidad.
     *
     * @return {Boolean}
     */
    _validateLines: function() {
        var rows = this.request_tbody.get('rows');
        var countRows = rows.size();
        var count = this.data.js.order_detail.list_items.filter(function(obj){
          return (!obj.delete);
        }).length;
        var countNotFound = this.data.js.order_detail.list_items_not_found.length;
        var isValid = true;

        if (!(count + countNotFound)) {
            isValid = false;

            RightNow.UI.Dialog.messageDialog('Debes agregar repuestos a la solicitud.', {
                title: 'Sin Repuestos'
            });

            return isValid;
        }

        for (var i = 0; i < countRows; i++) {
            var mandatoryFields = rows.item(i).all('.mandatory');
            for (var iFields = 0, countFields = mandatoryFields.size(); iFields < countFields; iFields++) {
                if (!mandatoryFields.item(iFields).get('value').length) {
                    isValid = false;
                    mandatoryFields.item(iFields).addClass('error');
                } else {
                    mandatoryFields.item(iFields).removeClass('error');
                }
            }
        }

        if (!isValid) {
            RightNow.UI.Dialog.messageDialog('Debes completar los campos para repuestos nuevos.', {
                title: 'Campos Obligatorios'
            });
        }

        return isValid;
    },

    /**
     * Indica si se cumplió con el límite máximo de líneas
     *
     * @return {Boolean}
     */
    _limitMax: function() {
        if (this._activeItemsCount() >= this.limit) {
            var title = 'Error';
            var msg = 'Has alcanzado el número máximo de líneas por solicitud.';

            RightNow.UI.Dialog.messageDialog(msg, {
                title: title
            });

            return true;
        }
        return false;
    },

    /**
     * Establece la cantidad de un ítem nuevo
     *
     * @param {Event}
     */
    _setQtyNewItem: function(e) {
        var btn = e.currentTarget;
        var content = btn.get('parentNode');
        var quantity = content.one('span.rn_Quantity');
        var line = content.get('parentNode').get('parentNode');
        var id = parseInt(line.one('input[name="idPart"]').get('value'));
        var dataItem = this.data.js.order_detail.list_items_not_found.find(function(d) {
            if (d.id == this)
                return d;
        }, id);

        var addValue = parseInt(btn.get('value'));
        var actualValue = parseInt(dataItem.quantity);
        var totalValue = actualValue + addValue;

        if (totalValue <= 0) {
            totalValue = 1;
        }

        quantity.setHTML(totalValue);
        this._changeNewPart(null, totalValue, 'quantity', id);
        dataItem.quantity = totalValue;
    },

    // -----------------------------------------------------------------------------
    // SERVICIOS
    // -----------------------------------------------------------------------------

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
        
        
        if(action>1)
        {
            this.data.js.order_detail.despachar=document.getElementById("despachar").value;
     
        }
        this.data.js.order_detail.action = action;
        btn.setAttribute('disabled', 'disabled');

        //Alvaro, aca llamas a la función, pero no le pasas ningun parametro
        this._loadToStorage();

        RightNow.Ajax.makeRequest('/cc/ServiceReparation/setOrder', {
            data: JSON.stringify(this.data.js),
        }, {
            scope: {
                _btn: btn,
                _action: action,
                _parent: this
            },
            successHandler: function(e) {
                var title = 'Error';
                var msg = 'Ocurrió un error inesperado.';
                var response = e.response;

                if (typeof response.errors !== 'undefined')
                    msg = response.errors.message;

                if (response.status !== true) {
                    RightNow.UI.Dialog.messageDialog(msg, {
                        title: title,
                        exitCallback: function() {
                            document.location.reload();
                        }
                    });
                } else {
                    if (this._action === 3) {
                        this._parent._removeItemsStorage();
                    }

                    document.location.reload();
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
