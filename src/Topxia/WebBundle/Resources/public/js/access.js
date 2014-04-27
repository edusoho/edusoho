define(function(require, exports, module) {

	window.$ = window.jQuery = require('jquery');
	require('bootstrap');
	require('common/bootstrap-modal-hack');

	targetUrl = window.location.pathname;
	  		
    $.post('/access/log', {accessUrl:accessUrl}, function(data){
    	
	});

});