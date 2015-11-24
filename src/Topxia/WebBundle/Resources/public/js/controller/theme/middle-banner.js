define(function(require, exports, module) {
	exports.run = function() {
		$("#middle-banner").on('click',function(){
			window.open($(this).data('url'));
		});
	};
});