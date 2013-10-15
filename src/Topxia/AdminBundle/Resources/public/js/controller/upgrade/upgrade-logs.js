define(function(require, exports, module) {

	exports.run = function() {

		$(".detail").popover({
			html: true,
			trigger: 'hover'
		});

	};

});