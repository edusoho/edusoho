define(function(require, exports, module) {
	window.$ = window.jQuery = require('jquery');

	require('bootstrap');
	require('common/bootstrap-modal-hack');

	exports.load = function(name) {
		require.async('./controller/' + name + '.js?' + window.app.version, function(controller){
			if ($.isFunction(controller.run)) {
				controller.run();
			}
		});
	};
	window.app.load = exports.load;

	if (app.controller) {
		exports.load(app.controller);
	}

	$(document).ajaxError(function(event, jqxhr, settings, exception) {
		var json = jQuery.parseJSON(jqxhr.responseText);
			error = json.error;
		if (!error) {
			return ;
		}

		if (error.name == 'Unlogin') {
			$('.modal.in').modal('hide');

			$("#login-modal").modal('show');
			$.get($('#login-modal').data('url'), function(html){
				$("#login-modal").html(html);
			});
		}
	});

	




});