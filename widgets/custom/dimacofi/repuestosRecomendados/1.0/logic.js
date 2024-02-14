RightNow.namespace('Custom.Widgets.dimacofi.repuestosRecomendados');
Custom.Widgets.dimacofi.repuestosRecomendados = RightNow.Widgets.extend({
	/**
	 * Widget constructor.
	 */
	constructor: function () {


		this.list_items = {};
		this._getRecomended();
	},


	/**
	 * Obtiene el valor del Json de predictiondata,
	 * transforma en un objeto,
	 * recorre el arreglo,
	 * obtiene el articulo,
	 * agrega el articulo a la tabla
	 */


	_getRecomended: function () {


		var data = {};
		data = {};
		data.ref_no = this.data.attrs.ref_no; // <- numero ticket

		RightNow.Event.fire('evt_ShowLoading')

		var list = new Array();
		RightNow.Ajax.makeRequest('/cc/ServiceReparation/getPredictionData', {
			data: JSON.stringify(data)
		}, {

			successHandler: function (e) {

				if (e.status) {
					var predictionList = [];
					var prediccion = e.prediction;

					var data = prediccion;

					RightNow.Ajax.makeRequest('/cc/ServiceReparation/productPrediction', {

						data: data


					}, {

						successHandler: function (e) {
							RightNow.Event.fire('evt_HideLoading');


							var list_items = {
								'list_items': e
							};

							RightNow.Event.fire("evt_setDataRecomendados", list_items);

						},
						failureHandler: function (e) {
							RightNow.Event.fire('evt_HideLoading');


							var title = 'Error';
							var msg = 'Error del servicio.';

							RightNow.UI.Dialog.messageDialog(msg, {
								title: title
							});
						}


					});


				}
			}
		});
	}

});