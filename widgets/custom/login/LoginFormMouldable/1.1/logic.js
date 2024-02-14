RightNow.namespace('Custom.Widgets.login.LoginFormMouldable');
Custom.Widgets.login.LoginFormMouldable = RightNow.Widgets.extend({
  constructor: function() {
    this._usernameField = this.Y.one(this.baseSelector + "_Username");
    this._passwordField = this.Y.one(this.baseSelector + "_Password");
    this._form_container = this.Y.one('.rn_' + this.instanceID + "_Form-container");
    this._enterField = this.Y.one(this.baseSelector + "_Enter");
    this._submit = this.Y.one(this.baseSelector + "_Enter");
    this._dialog;

    if (this.data.attrs.is_dialog) {
      this._enterField.on('click', this._onLoginTriggerClick, this);
    } else {
      this.Y.one(this.baseSelector + "_Submit").on("click", this._onSubmit, this);
    }

    // TODO: Cambiar por parámetro
    this.data.attrs.label_cancel_button = 'Cancelar'; // RightNow.Interface.getMessage("CUSTOM_MSG_CANCEL");
    this.data.attrs.label_dialog_title =  'Conéctese con una cuenta existente' //RightNow.Interface.getMessage("CUSTOM_MSG_START_SESSION");

    RightNow.Widgets.formTokenRegistration(this);

    // Precarga de base de mensajes
    RightNow.Interface.getMessage("CUSTOM_MSG_ERROR");
  },

  /**
   * Event handler for when login control is clicked.
   *
   * @param {String} event Event name
   * @param {Object} args DOM event
   */
  _onLoginTriggerClick: function(event, args) {
    // get a new f_tok value each time the dialog is opened
    RightNow.Event.fire("evt_formTokenRequest",
      new RightNow.Event.EventObject(this, {
        data: {
          formToken: this.data.js.f_tok
        }
      }));

    this._dialog || (this._dialog = this._createDialog());
    // this._clearErrorMessage();

    this._dialog.show();
    this._form_container.show();

    RightNow.UI.Dialog.enableDialogControls(this._dialog, this._keyListener);
  },

  /**
   * Creates the dialog
   * @return {Object} Y.Panel dialog instance
   */
  _createDialog: function() {
    var dialog = RightNow.UI.Dialog.actionDialog(this.data.attrs.label_dialog_title,
      this._form_container, {
        buttons: [{
            text: this.data.attrs.label_login_button,
            handler: {
              fn: this._onSubmit,
              scope: this
            },
            name: 'submit'
          },
          {
            text: this.data.attrs.label_cancel_button,
            handler: {
              fn: this._onCancel,
              scope: this,
              href: 'javascript:void(0)'
            }
          }
        ],
        width: '300px',
        height: '100%'
      });

    // Set up keylistener for <enter> to run onSubmit()
    this._keyListener = RightNow.UI.Dialog.addDialogEnterKeyListener(dialog, this._onSubmit, this);

    //override default YUI validation to return false: don't want YUI to try to submit the form
    dialog.validate = function() {
      return false;
    };

    RightNow.UI.show(this._container);

    if (RightNow.Env('module') === 'standard') {
      //Perform dialog close cleanup if the [x] cancel button or esc is used
      //(only standard page set has [x] or uses esc button)
      dialog.cancelEvent.subscribe(this._onCancel, null, this);
    }

    return dialog;
  },

  /**
   * Function used to parse out the URL where we should redirect to
   * after a successful login
   * @param result Object The response object returned from the server
   * @return String The URL to redirect to
   */
  _getRedirectUrl: function(result) {
    var redirectUrl;
    if (this.data.js && this.data.js.redirectOverride)
      redirectUrl = RightNow.Url.addParameter(this.data.js.redirectOverride, 'session', result.sessionParm.substr(result.sessionParm.lastIndexOf("/") + 1));
    else
      redirectUrl = (this.data.attrs.redirect_url || result.url) + ((result.addSession) ? result.sessionParm : "");

    redirectUrl += this.data.attrs.append_to_url;

    if (result.forceRedirect) {
      redirectUrl = RightNow.Url.addParameter(result.forceRedirect, 'redirect', encodeURIComponent(redirectUrl));
    }

    return redirectUrl;
  },

  /**
   * Event handler for when login has returned. Handles either successful login or failed login
   * @param response {Object} Result from server
   * @param originalEventObject {Object} Original request object sent in request
   */
  _onLoginResponse: function(response, originalEventObject) {
    if (!RightNow.Event.fire("evt_loginFormSubmitResponse", {
        data: originalEventObject,
        response: response
      })) {
      return;
    }

    this._toggleLoading(false);

    if (response.success) {
      this.Y.one(this.baseSelector + "_Content").set("innerHTML", response.message);
      var redirectUrl = this._getRedirectUrl(response);
      if (this.Y.UA.ie && this.Y.UA.ie < 9 && RightNow.Text.beginsWith(redirectUrl, '/ci/fattach/get/'))
        this.Y.one(this.baseSelector).set('innerHTML', RightNow.Text.sprintf(RightNow.Interface.getMessage("PLS_CLCK_HREF_EQS_PCT_S_THAN_S_MSG"), redirectUrl));
      else
        RightNow.Url.navigate(redirectUrl);
    } else {
      this._addErrorMessage(response.message, this.baseDomID + '_Username', response.showLink);
    }
  },

  /**
   * Event handler for when login button is clicked.
   * @param {Object} e YUI Event facade
   */
  _onSubmit: function(e) {
    var username = (this._usernameField) ? this.Y.Lang.trim(this._usernameField.get("value")) : "",
      errorMessage, eventObject;

    if (username.indexOf(' ') > -1)
      errorMessage = RightNow.Text.sprintf(RightNow.Interface.getMessage("PCT_S_MUST_NOT_CONTAIN_SPACES_MSG"), RightNow.Interface.getMessage("USERNAME_LBL"));
    else if (username.indexOf('"') > -1)
      errorMessage = RightNow.Text.sprintf(RightNow.Interface.getMessage("PCT_S_CONTAIN_DOUBLE_QUOTES_MSG"), RightNow.Interface.getMessage("USERNAME_LBL"));
    else if (username.indexOf("<") > -1 || username.indexOf(">") > -1)
      errorMessage = RightNow.Text.sprintf(RightNow.Interface.getMessage("PCT_S_CNT_THAN_MSG"), RightNow.Interface.getMessage("USERNAME_LBL"));

    if (errorMessage) {
      this._addErrorMessage(errorMessage, this.baseDomID + '_Username');
      return false;
    }

    eventObject = new RightNow.Event.EventObject(this, {
      data: {
        login: username,
        password: ((!this.data.attrs.disable_password && this._passwordField) ? this._passwordField.get("value") : ""),
        url: window.location.pathname,
        w_id: this.data.info.w_id,
        f_tok: this.data.js.f_tok
      }
    });
    if (RightNow.Event.fire("evt_loginFormSubmitRequest", eventObject)) {
      this._toggleLoading(true);

      if (RightNow.Event.noSessionCookies()) {
        //Attempt to set a test login cookie
        RightNow.Event.setTestLoginCookie();
      }
      RightNow.Ajax.makeRequest(this.data.attrs.login_ajax, eventObject.data, {
        successHandler: this._onLoginResponse,
        scope: this,
        data: eventObject,
        json: true
      });

      if (this.Y.UA.ie && window.external && "AutoCompleteSaveForm" in window.external) {
        //since this form is submitted by script, force ie to do auto_complete
        var form = document.getElementById(this.baseDomID + "_Form");
        if (form)
          window.external.AutoCompleteSaveForm(form);
      }
    }
  },

  /**
   * Utility function to display an error message
   * @param message String  The error message to display
   * @param focusElement String The ID of the element to focus when clicking on the error message
   * @param showLink [optional] Boolean Denotes if error message should be surrounded in a link tag
   */
  _addErrorMessage: function(message, focusElement, showLink) {
    var error = this.Y.one(this.baseSelector + "_ErrorMessage");
    if (error) {
      error.addClass('rn_MessageBox rn_ErrorMessage');
      //add link to message so that it can receive focus for accessibility reasons
      if (showLink === false) {
        error.set("innerHTML", message);
      } else {
        error.set("innerHTML", '<a href="javascript:void(0);" onclick="document.getElementById(\'' + focusElement + '\').focus(); return false;">' + message + '</a>');
        error.one('a').focus();
      }
    }
  },

  /**
   * Toggles the state of loading indicators:
   * Fades the form out/in (for decent browsers)
   * Disables/enables form inputs
   * Adds/Removes loading indicator class
   * @param {Boolean} turnOn Whether to add or remove the loading indicators.
   */
  _toggleLoading: function(turnOn) {
    this._widgetContent || (this._widgetContent = this.Y.one(this.baseSelector + '_Content'));

    this._widgetContent.all('input')[(turnOn) ? 'setAttribute' : 'removeAttribute']('disabled', true);

    if (!this.Y.UA.ie || this.Y.UA.ie > 8) {
      // YUI's animation causes JS execution in IE7-8 to fail in weird ways, like failing to redirect the page
      // when a user's successfully logged in...
      this._widgetContent.transition({
        opacity: turnOn ? 0 : 1,
        duration: 0.4
      });
      this.Y.one(this.baseSelector)[(turnOn) ? 'addClass' : 'removeClass']('rn_Loading');
    }
  },

  /**
   * User cancelled. Cleanup and close the dialog.
   */
  _onCancel: function() {
    // this._clearErrorMessage();
    RightNow.UI.Dialog.disableDialogControls(this._dialog, this._keyListener);
    // this._toggleWarningMessageOnSocialAction(true);
    this._dialog.hide();
  },
});
