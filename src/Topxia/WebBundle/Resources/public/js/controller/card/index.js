define(function(require, exports, module) {

	exports.run = function (){
		var Cookie = require('cookie');
		$('a[role=filter-change]').click(function(event){
			window.location.href = $(this).data('url');
		});
		$('.receive-modal').click();
			Cookie.set('receive',"");
		// 
	};
});