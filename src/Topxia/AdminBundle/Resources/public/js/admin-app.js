define(function(require, exports, module) {
	window.$ = window.jQuery = require('jquery');

	require('bootstrap');
	require('common/bootstrap-modal-hack');
	require('jquery.toastr');

	toastr.options = {
        positionClass: 'toast-bottom-right',
        fadeIn: 300,
        fadeOut: 500,
        timeOut: 3000
    };

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

});