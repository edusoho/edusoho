define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
    	$("#refunds-table").on('click', '.cancel-refund', function(){
    		if (!confirm(Translator.trans('admin.order.refund_cancel_hint'))) {
    			return false;
    		}

    		$.post($(this).data('url'), function() {
    			Notify.success(Translator.trans('admin.order.refund_cancel_success_hint'));
    			window.location.reload();
    		});
    	});

    };

});