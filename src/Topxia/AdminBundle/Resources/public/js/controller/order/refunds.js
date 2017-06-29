define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
    	$("#refunds-table").on('click', '.cancel-refund', function(){
    		if (!confirm(Translator.trans('真的要取消退款吗？'))) {
    			return false;
    		}

    		$.post($(this).data('url'), function() {
    			Notify.success(Translator.trans('退款申请已取消成功！'));
    			window.location.reload();
    		});
    	});

    };

});