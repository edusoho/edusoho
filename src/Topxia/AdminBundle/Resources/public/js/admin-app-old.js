seajs.config({
	alias: {
		'$' : 'gallery/jquery/1.8.2/jquery',
		'$-debug' : 'gallery/jquery/1.8.2/jquery-debug',
		'jquery' : 'gallery/jquery/1.8.2/jquery',
		'jquery-debug' : 'gallery/jquery/1.8.2/jquery-debug',
		'class': 'arale/class/1.0.0/class',
		'bootstrap': 'bootstrap/3.0.0/bootstrap',
		'jquery.cycle': 'jquery.cycle/1.3.2/jquery.cycle',
		'jquery.form': 'jquery.form/3.09/jquery.form-debug',
		'jquery.toggleval': 'jquery.toggleval/3.0/jquery.toggleval',
		'jquery.tools': 'jquery.tools/1.2.5/jquery.tools',
		'jquery.validate': 'jquery.validate/1.8.1/jquery.validate',
		'jquery.ui.sortable': 'jquery.ui/1.8.18/jquery.ui',
		'ajax_dialog' : 'utils/0.0.2/ajax_dialog',
		'jquery.autocomplete' : 'jquery.autocomplete/1.0.0/jquery.autocomplete',
		'jquery.lazyload' : 'jquery.lazyload/1.8.2/jquery.lazyload-debug'
	},
	preload: [],
	debug: 2
});

define(function(require, exports, module) {
	var $ = require('jquery');
	window.jQuery = $;
	window.$ = $;

	require('bootstrap')($);

	exports.load = function(name, options) {
		require.async('./modules/' + name + '.js', function(mod) {
			if (mod.bootstrap) {
				mod.bootstrap(options);
			}
		});
	};

	window.app_load = exports.load;

	exports.bootstrap = function() {
		$(function() {


		});
	};

});
