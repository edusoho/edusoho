define(function(require, exports, module) {

	exports.run = function (){
		$('input:radio[name=filter-change]').change(function(event){
			window.location.href = $(this).val();
		});
		$('.receive-modal').click();
	};
});