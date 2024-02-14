RightNow.namespace('Custom.Widgets.integer.InformeTecnico');
Custom.Widgets.integer.InformeTecnico = RightNow.Widgets.extend({
    /**
     * Widget constructor.
     */
    constructor: function() {
      this.btn_request = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_request"]');
      this.btn_cancel = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_cancel"]');
   //   this.btn_upload = this.Y.one(this.baseSelector + ' input[type="button"][name="btn_upload"]');

    if (this.btn_request) this.btn_request.on("click", this._request, this);
    if (this.btn_cancel) this.btn_cancel.on("click", this._cancel, this);
   // if (this.btn_upload) this.btn_upload.on("click", this._upload, this);


       this.js_default = {
           'order_detail': {
               'ref_no': this.data.attrs.ref_no,
               'select_status': this.data.attrs.select_status,
               'nota':''
           }
       };
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
    _clearData: function() {

      this.data.js = Integer._extend({}, this.js_default, this.data.js);


    },
    _upload: function(e,action) {



      z=document.getElementById("myProgress");
    z.style.top=screen.height/2 + 'px';
    z.style.position='fixed';
    z.style.width= screen.width/1.1+'px';
     z.style.visibility= 'visible';

       z.style.visibility= 'visible';
      //this._openModal();
      var btn = e._currentTarget;

      btn.setAttribute('disabled', 'disabled');
      x=document.getElementById("outter");


      var fileInput = document.getElementById('file');
  var file = fileInput.files[0];
  var formData = new FormData();
  formData.append('file', file);
  formData.append('ref_no', this.data.js.order_detail.ref_no);
  var xhr = new XMLHttpRequest();
  // Add any event handlers here...
  xhr.open('POST', "/cc/Services/upload", true);
  xhr.onload =  function(e) {
      document.location.reload();
  };

  if (xhr.upload) {
      xhr.upload.onprogress = (event) => {
        //console.log('upload onprogress', event);
        x=document.getElementById("outter_1");
        x.style="height:20px;width:" + (event.loaded / event.total ) * 100 + "%";
      };
    }

  xhr.onerror = function (e) {
   console.error(xhr.statusText);
  };
  xhr.send(formData);

    },
     _cancel: function(e) {
      //document.location.reload();
      //document.location.href = '/app/reparacion/logistica';
	  document.location.href = '/app/reparacion/home';//Se agrega para redirecciones a home 28/08 R.S
      },
      /**
       * Realiza el cambio de Estado.
       *
       * @param {Event} Mouse Event
       */
      _request: function(e) {
        RightNow.Event.fire("evt_ShippingInstructions", this.instanceID);
        var btn = e._currentTarget;
        btn.setAttribute('disabled', 'disabled');
        this.data.js.order_detail.select_status=document.getElementById("select_status").value;

        RightNow.Ajax.makeRequest('/cc/Services/updateIncidentInforme', {
            data: JSON.stringify(this.data.js),
            }, {
            scope: {
                _btn: btn,
                _parent: this
            },
            successHandler: function(e) {
                var response = e.response;
                var msg="";

                msg=response.msg;
                if (response.status !== true) {
                    RightNow.UI.Dialog.messageDialog(msg, {
                        title: 'Error Interno',
                        exitCallback: function() {
                            document.location.reload();
                        }
                    });
                } else {
                  RightNow.UI.Dialog.messageDialog(msg, {
                      title: 'Error Interno',
                      exitCallback: function() {
                          document.location.href = '/app/reparacion/home';
                      }
                  });
                    document.location.href = '/app/reparacion/home';//pendiente de crear
                }
            },
            failureHandler: function(e) {
                this._btn.removeAttribute('disabled');

                var title = 'Error';
                var msg = 'Error del servicio.';
                  var response = e.response;

                RightNow.UI.Dialog.messageDialog(msg, {
                    title: title
                });
            }
        });

      },

});
