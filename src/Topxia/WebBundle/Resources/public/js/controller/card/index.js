define(function(require, exports, module) {

	exports.run = function (){
		$('a[role=filter-change]').click(function(event){
			window.location.href = $(this).data('url');
		});
		$('.receive-modal').click();

	};
});