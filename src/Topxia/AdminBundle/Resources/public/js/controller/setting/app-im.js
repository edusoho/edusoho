define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $('.app-im-open, .app-im-close').click(function(e){
        	e.preventDefault();
        	var url = $(this).attr('href');
        	var status = $(this).data('status');
        	
        	$.post(url, {status:status},function(res){
        		if (res) {
        			Notify.success(Translator.trans('操作成功！'));
        			window.location.reload();
        		} else {
        			Notify.danger(Translator.trans('操作失败！'));
        		}
        		
        	})
        })
    }

})