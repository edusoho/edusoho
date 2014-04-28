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
				$(document).ready(function(){
					$('#ad-top').append(ad.showUrl);
					$('#ad-top').css({zIndex:9999});

					var sticky = require('sticky');

					sticky("#ad-top",{top:0,bottom:30},function(s){});

					//$('#ad').css({top:"0",left:"0",zIndex:9999});
				});
				

			}else if(ad.showMode==2){

				$(document).ready(function(){
					$('#ad-bottom').append(ad.showUrl);
					$('#ad-bottom').css({zIndex:9999});

					var sticky = require('sticky');
					
					sticky("#ad-bottom",{top:30,bottom:0},function(s){});
				});
				



			}
			
		}

	});

	

});