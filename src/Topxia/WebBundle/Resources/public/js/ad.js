define(function(require, exports, module) {

	window.$ = window.jQuery = require('jquery');
	require('bootstrap');
	require('common/bootstrap-modal-hack');

	targetUrl = window.location.pathname;

	$.post('/ad/get', {targetUrl:targetUrl}, function(ad){
		if (ad.run) {
			if(ad.showMode==0){
				require('dialog-css');
				var Dialog = require('dialog');
				new Dialog({
					width: 800,
					content: ad.showUrl,
					zIndex: 9999
				}).show();
			}else if(ad.showMode==1){
				var Dialog = require('sticky');

			}else if(ad.showMode==2){

			}
			
		}

	});

	

});