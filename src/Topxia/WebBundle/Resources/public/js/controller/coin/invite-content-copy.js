define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	exports.run = function (){
		$('#copy').click(function(){
	        $("#content").select();
	        document.execCommand("Copy"); 
	        Notify.success(Translator.trans('链接复制成功'));
    	});
	}
});