define(function(require, exports, module) {
	window.$ = window.jQuery = require('jquery');

	require('placeholder');

	require('bootstrap');
	require('common/bootstrap-modal-hack');

	exports.load = function(name) {
		if (name.substr(0, 7) == 'custom/') {
			name = '../../../bundles/customadmin/js/controller/' + name.substr(7);
		} else if (name.substr(0, 10) == 'vipbundle/') {
			name = '../../../bundles/vip/js/' + name.substr(10);
		} else if (name.substr(0, 13) == 'couponbundle/') {
			name = '../../../bundles/coupon/js/' + name.substr(13);
		} else {
			name = './controller/' + name;
		}
		require.async(name + '.js?' + window.app.version, function(controller){
			if ($.isFunction(controller.run)) {
				controller.run();
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