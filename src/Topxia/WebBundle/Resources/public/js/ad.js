define(function(require, exports, module) {

	window.$ = window.jQuery = require('jquery');
	require('bootstrap');
	require('common/bootstrap-modal-hack');



	$(document).ready(function() {

		$.post(url, data, success) {

			if (data.ad_config.run) {


				require('wookmark');

				require('dialog-css');

				var Dialog = require('dialog');

				new Dialog({
					width: 800,
					content: window.ad_config.showUrl,
					zIndex: 9999
				}).show();


			}



		}



	});



});