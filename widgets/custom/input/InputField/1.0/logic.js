RightNow.namespace('Custom.Widgets.input.InputField');

Custom.Widgets.input.InputField = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {
        if (this.data.attrs.read_only) {
            return false;
        }
        this.widget = this.Y.one(this.baseSelector);
        this.input = (this.data.attrs.display_type !== 'boolean' && this.data.attrs.display_type !== 'scoring' && this.data.attrs.display_type !== 'multiple') ? this.widget.one('input') || this.widget.one('textarea') : this.widget.all('input');

        if (this.data.attrs.show_group) {
            this.input.on('change', this.show_group, this);
        }

        if (this.data.attrs.display_type == 'date') {
            this.inputId = this.input.get('id');
            this.obj = {};
            this.obj[this.inputId] = '%d-%m-%Y';
            this.newDate = new Date();
            this.lowRange = new Date(1900, 0, 1);
            /* 20200924 RTC
                  this.datapiker = datePickerController.createDatePicker({
                    formElements: this.obj,
                    showWeeks: false,
                    rangeLow: this.lowRange,
                    statusFormat: "%l, %d %F %Y",
                    disabledDays: [0, 0, 0, 0, 0, 0, 0]
                  });
            */
        }

        RightNow.Event.subscribe("evt_ValidateInput", this.validate, this);
        RightNow.Event.subscribe("evt_SetRequired", this.setRequired, this);
        RightNow.Event.subscribe("evt_GetInput", this.getInput, this); // Deprecated
        RightNow.Event.subscribe("evt_ClearInput", this.clearInput, this);
        RightNow.Event.subscribe('evt_GetInstanceByInputName', this.getInstanceByInputName, this);
        RightNow.Event.subscribe('evt_GetValues', this.getValues, this);
        RightNow.Event.subscribe('evt_GetData', this.getData, this);

        // Mensajes
        this.message_min_value = 'El valor ingresado para <strong>{{label}}</strong> debe ser mayor o igual a "{{value}}".';

        if (this.data.attrs.show_count) {
            this.countChar();
        }

    },

    /**
     * Complementa el array de valores en notación key - value
     *
     * @param e {Event} Evento que invóca al método
     * @param arr {Array} Array de parámetros
     * @return {Array}
     */
    getData: function(e, arr) {
        var name = (typeof this.input._node === 'undefined') ? this.widget.one('input:checked').get('name') : this.input.get('name');
        var value = (typeof this.input._node === 'undefined') ? this.widget.one('input:checked').get('value') : this.input.get('value');

        if (!e && !arr) {
            obj = {};
            obj[name] = value;
            return obj;
        }

        arr[0]._data = arr[0]._data || {};

        if (this.data.attrs.display_type === 'multiple') {
            arr[0]._data[name] = this.widget.all('input:checked').get('value').join(',');
        } else {
            arr[0]._data[name] = value;
        }

        return arr;
    },

    /**
     * Complementa el array de valores
     *
     * @param e {Event} Evento que invóca al método
     * @param arr {Array} Array de parámetros
     * @return {Array}
     */
    getValues: function(e, arr) {
        arr[0].push(this.input.get('value'));

        return arr;
    },

    /**
     * Complementa el array de inputs
     *
     * @param e {Event} Evento que invóca al método
     * @param arr {Array} Array de parámetros
     * @return {Array}
     */
    getInput: function(e, arr) {
        arr[0].push(this.input);

        return arr;
    },

    /**
     * Limpia el campo
     *
     * @param e {Event} Evento que invóca al método
     * @return {Array}
     */
    clearInput: function(e) {
        this.input.set('value', '');

        return true;
    },

    /**
     * Retorna la instancia según el atributo 'name' o 'id' del campo
     *
     * @param e {Event} Evento que invóca al método
     * @param param {array} Array de parámetros
     * @param {Object}
     */
    getInstanceByInputName: function(e, param) {
        var instance = this;

        if (instance.data.attrs.id === param[1] || instance.data.attrs.name === param[1]) {
            param[0].push(instance);
        }

        return instance;
    },

    /**
     * Muestra el contenedor
     *
     * @return {Boolean}
     */
    show_group: function() {
        var value = this.widget.all('input:checked').get('value');

        if (value.indexOf(this.data.attrs.show_group_value) != -1) {
            this.Y.all('.' + this.data.attrs.show_group).show();
        } else {
            this.Y.all('.' + this.data.attrs.show_group).hide();
        }
    },

    /**
     * Obtiene la instancia del form que lo contiene
     *
     * @param formIDToUse {String}
     * @return {Object}
     */
    parentForm: function(formIDToUse) {
        var lookupNode = formIDToUse || this.baseDomID;
        return RightNow.Form.find(lookupNode, this.instanceID);
    },

    /**
     * Establece la obligatoriedad del campo
     *
     * @param isMandatory {Boolean}
     * @return {Boolean}
     */
    setMandatory: function(isMandatory) {
        this.data.attrs.required = isMandatory;

        if (isMandatory) {
            this.widget.one('.rn_Required').show();
        } else {
            this.widget.one('.rn_Required').hide();
        }

        return true;
    },

    /**
     * Establece si el campo se desabilita o no
     *
     * @param isDisabled {Boolean}
     * @return {Boolean}
     */
    setDisabled: function(isDisabled) {
        this.data.attrs.disabled = isDisabled;
        field = this.input;

        if (this.data.attrs.display_type === 'boolean') {
            field = this.widget.all('input');
        }

        if (isDisabled) {
            field.set('disabled', 'disabled');
        } else {
            field.set('disabled', '');
        }

        return true;
    },

    /**
     * Realiza validación del formato de RUT para cuando el tipo corresponda
     * 
     * @return {array}
     */
    validate_rut: function(error) {
        error = error || [];

        if (this.input.get('value').length && this.data.attrs.display_type === 'rut') {
            if (!Integer.validateRut(Integer.formatRut(this.input.get('value'), 2))) {
                error.push({
                    valid: false,
                    name: this.baseDomID,
                    instance: this.instanceID,
                    message: 'El valor ingresado para <strong>' + this.data.attrs.label_input + '</strong> debe ser valido.'
                });
            } else if (this.input.get('value').length) {
                this.input.set('value', Integer.formatRut(this.input.get('value'), 2));
            }
        }

        return error;
    },

    /**
     * Realiza el recuento restante de caracteres disponibles para el campo
     *
     * @return {boolean}
     */
    countChar: function() {
        this.widget.one('span.countMax').set('value', this.data.attrs.maxlength);

        this.input.on('change', function(e) {
            this.widget.one("span.countLeft").setHTML(this.input.get('value').length);
        }, this);

        this.input.on('keyup', function(e) {
            this.widget.one("span.countLeft").setHTML(this.input.get('value').length);
        }, this);

        return true;
    },

    /**
     * Valida el campo
     *
     * @param e {Event} Evento que invóca al método
     * @param errors {Array} Array de errores
     * @param {Object}
     */
    validate: function(e, errors) {
        var error = [];

        if (this.widget.ancestor('[hidden]') || this.widget._isHidden()) {
            return errors;
        }

        // Number Validation
        if (this.input.get('value').length && this.data.attrs.display_type === 'number') {
            if (!/\d+/.test(this.input.get('value'))) {
                error.push({
                    valid: false,
                    name: this.baseDomID,
                    instance: this.instanceID,
                    message: 'El valor ingresado para <strong>' + this.data.attrs.label_input + '</strong> debe ser un número entero.'
                });
            } else if (this.input.get('value').length) {
                this.input.set('value', this.input.get('value').replace(/\D+/, ''));
            }
        }

        // RUT Validation
        if (this.input.get('value').length && this.data.attrs.display_type === 'rut') {
            if (!Integer.validateRut(Integer.formatRut(this.input.get('value'), 2))) {
                error.push({
                    valid: false,
                    name: this.baseDomID,
                    instance: this.instanceID,
                    message: 'El valor ingresado para <strong>' + this.data.attrs.label_input + '</strong> debe ser valido.'
                });
            } else if (this.input.get('value').length) {
                this.input.set('value', Integer.formatRut(this.input.get('value'), 2));
            }
        }

        // Email Validation
        if (this.input.get('value').length && this.data.attrs.display_type === 'email') {
            if (!Integer.validateEmail(this.input.get('value'))) {
                error.push({
                    valid: false,
                    name: this.baseDomID,
                    instance: this.instanceID,
                    message: 'El valor ingresado para <strong>' + this.data.attrs.label_input + '</strong> debe tener un formato de correo electrónico valido.'
                });
            }
        }

        // Date Validation
        if (this.input.get('value').length && this.data.attrs.display_type === 'date') {
            if (!Integer._isValidDate(this.input.get('value'))) {
                error.push({
                    valid: false,
                    name: this.baseDomID,
                    instance: this.instanceID,
                    message: 'El valor ingresado para <strong>' + this.data.attrs.label_input + '</strong> debe tener un formato de fecha valido.'
                });
            }
        }

        // Hour Validation
        if (this.input.get('value').length && this.data.attrs.display_type === 'hour') {
            if (!Integer._isValidHour(this.input.get('value'))) {
                error.push({
                    valid: false,
                    name: this.baseDomID,
                    instance: this.instanceID,
                    message: 'El valor ingresado para <strong>' + this.data.attrs.label_input + '</strong> debe tener un formato de hora valido.'
                });
            }
        }

        // Min Value Validation
        if (typeof this.data.attrs.min_value !== 'undefined' && parseInt(this.input.get('value')) < this.data.attrs.min_value) {
            error.push({
                valid: false,
                name: this.baseDomID,
                instance: this.instanceID,
                message: this.message_min_value.replace('{{label}}', this.data.attrs.label_input).replace('{{value}}', this.data.attrs.min_value)
            });
        }

        // Max Value Validation
        if (typeof this.data.attrs.max_value !== 'undefined' && parseInt(this.input.get('value')) >= this.data.attrs.max_value) {
            error.push({
                valid: false,
                name: this.baseDomID,
                instance: this.instanceID,
                message: 'El valor ingresado para <strong>' + this.data.attrs.label_input + '</strong> debe ser menor o igual a "' + this.data.attrs.max_value + '".'
            });
        }

        // Required Validation
        if (this.data.attrs.required && !this.widget.ancestor('[hidden]')) {
            if (typeof this.input._node !== 'undefined') {
                if (!this.input.get('value')) {
                    error.push({
                        valid: false,
                        name: this.baseDomID,
                        instance: this.instanceID,
                        message: 'El campo <strong>' + this.data.attrs.label_input + '</strong> es obligatorio.'
                    })
                }
            } else {
                if (!this.widget.one('input:checked')) {
                    error.push({
                        valid: false,
                        name: this.baseDomID,
                        instance: this.instanceID,
                        message: 'El campo <strong>' + this.data.attrs.label_input + '</strong> es obligatorio.'
                    })
                }
            }
        }

        if (error.length) {
            this.Y.each(error, function(value, i) {
                errors[0].push(value);
            });
        } else {
            errors[0].push({
                valid: true,
                name: this.baseDomID,
                message: ''
            });
        }

        return errors;
    }

});