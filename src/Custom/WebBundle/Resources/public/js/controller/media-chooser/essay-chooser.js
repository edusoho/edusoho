define(function(require,exports,module){
	var Widget = require('widget');
	var BaseChooser = require('./base-chooser');
	var EssayChooser = BaseChooser.extend({
		attrs: {

		},

		events: {
			"click [data-role=essay-trigger]": "open"
		}


	});

	module.exports = EssayChooser;
});