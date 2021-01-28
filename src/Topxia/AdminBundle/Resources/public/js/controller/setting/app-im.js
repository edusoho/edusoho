define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $('.app-im-open, .app-im-close').click(function(e){
        	e.preventDefault();
        	var url = $(this).data('url');
        	var status = $(this).data('status');
        	
            $(this).button('loading');
        	$.post(url, {status:status},function(res){
        		if (res) {
        			Notify.success(Translator.trans('admin.setting.operation_success_hint'));
        			window.location.reload();
        		} else {
        			Notify.danger(Translator.trans('admin.setting.operation_fail_hint'));
        		}
        		
        	})
        })
    }

})