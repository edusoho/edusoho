define(function(require, exports, module) {
	window.$ = window.jQuery = require('jquery');

	require('placeholder');

	require('bootstrap');
	require('common/bootstrap-modal-hack2');

	exports.load = function(name) {
		if (window.app.jsPaths[name.split('/', 1)[0]] == undefined) {
			name = window.app.basePath + '/bundles/topxiaadmin/js/controller/' + name;
		}

		seajs.use(name, function(module) {
			if ($.isFunction(module.run)) {
				module.run();
			}
		});

	};
    
	window.app.load = exports.load;

	if (app.controller) {
		exports.load(app.controller);
	}

	$( document ).ajaxSend(function(a, b, c) {
		if (c.type == 'POST') {
			b.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
		}
	});

});