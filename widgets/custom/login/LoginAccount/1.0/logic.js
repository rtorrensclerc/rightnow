/**
 * TODO: Enviar el formulario al presionar <Enter>
 */
RightNow.namespace('Custom.Widgets.login.LoginAccount');
Custom.Widgets.login.LoginAccount = RightNow.Widgets.extend({

    /**
     * Constructor.
     */
    constructor: function() {
        this._connect_input = this.Y.one('input[name="btn_connect"]');
        this._disconnect_input = this.Y.one('input[name="btn_disconnect"]');
        this._username_input = this.Y.one('input[name="username"]');
        this._password_input = this.Y.one('input[name="password"]');

        if (this._connect_input) this._connect_input.on("click", this._connect, this);
        if (this._disconnect_input) this._disconnect_input.on("click", this._disconnect, this);
    },


    /**
     * Realiza la conexión mediante un modelo que establece las variables de sesión
     *
     * @param {Event} Objeto del evento click que invoca la función
     */
    _connect: function(e) {
        var error = this.Y.one(this.baseSelector + "_ErrorMessage");

        this._connect_input.setAttribute('disabled', 'disabled');

        RightNow.Ajax.makeRequest('/cc/LoginAccount/connect', {
            'username': this._username_input.get('value'),
            'password': this._password_input.get('value')
        }, {
            scope: {
                parent: this
            },
            successHandler: function(e) {
                var response = JSON.parse(e.responseText);
                // var response = e.response;
                if (response === true) {
                    window.location.assign(this.parent.data.attrs.url_redirect);
                } else {
                    error.set("innerHTML", response.errorLogin);
                    error.addClass('rn_MessageBox rn_ErrorMessage');

                    if (this.parent._connect_input) this.parent._connect_input.removeAttribute('disabled');
                    if (this.parent._disconnect_input) this.parent._disconnect_input.removeAttribute('disabled');
                }
            },
            failureHandler: function(e) {
                var title = 'Error';
                var msg = 'Error del servicio.';

                RightNow.UI.Dialog.messageDialog(msg, {
                    title: title
                });

                if (this.parent._connect_input) this.parent._connect_input.removeAttribute('disabled');
                if (this.parent._disconnect_input) this.parent._disconnect_input.removeAttribute('disabled');
            }
        });
    },

    /**
     * Realiza la desconexión de un técnico mediante el borrado de las varibles
     * de sesión.
     *
     * @param {Event} Objeto del evento click que invoca la función
     */
    _disconnect: function(e) {
        this._disconnect_input.setAttribute('disabled', 'disabled');
        var url_redirect_disconnect = this.data.attrs.url_redirect_disconnect;

        RightNow.Ajax.makeRequest('/cc/LoginAccount/disconnect', {}, {
            scope: {
                parent: this
            },
            successHandler: function(e) {
                var response = JSON.parse(e.responseText);
                if (response === true) {
                    window.location.assign(url_redirect_disconnect);
                } else {
                    var title = 'Error';
                    var msg = response.errorLogin;
                    RightNow.UI.Dialog.messageDialog(msg, {
                        title: title
                    });
                }
            },
            failureHandler: function(e) {
                var title = 'Error';
                var msg = 'Error del servicio.';

                RightNow.UI.Dialog.messageDialog(msg, {
                    title: title
                });
                this.parent._disconnect_input.removeAttribute('disabled');
            }
        });
    }
});
