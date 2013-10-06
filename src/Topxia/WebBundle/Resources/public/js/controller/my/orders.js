define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');

    exports.run = function() {
    	$("#orders-table").on('click', '.cancel-refund', function(){
    		if (!confirm('真的要取消退款吗？')) {
    			return false;
    		}

    		$.post($(this).data('url'), function() {
    			Notify.success('退款申请已取消成功！');
    			window.location.reload();
    		});
    	});

    };

});