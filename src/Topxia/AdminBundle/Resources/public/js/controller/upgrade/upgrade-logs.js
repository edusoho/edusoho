define(function(require, exports, module) {

	exports.run = function() {

		$("a[rel=popover]")
			.popover({
				html: true,
				placement: 'left',
				trigger: 'hover'
			})
			.click(function(e) {
				e.preventDefault()
			});

	};

});