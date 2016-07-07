define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');

    exports.run = function() {

    	$("#orders-table").on('click', '.cancel-refund', function(){
    		if (!confirm(Translator.trans('真的要取消退款吗？'))) {
    			return false;
    		}

    		$.post($(this).data('url'), function() {
    			Notify.success(Translator.trans('退款申请已取消成功！'));
    			window.location.reload();
    		});
    	});

            $("#orders-table").on('click', '.pay', function(){
                    $.post($(this).data('url'), {orderId: $(this).data('orderId')} ,function(html) {
                            $("body").html(html);
                    });
            });

        $("#orders-table").on('click', '.cancel', function(){
            if (!confirm(Translator.trans('真的要取消订单吗？'))) {
                return false;
            }

            $.post($(this).data('url'), function(data) {
                if(data!=true) {
                    Notify.danger(Translator.trans('订单取消失败！')); 
                }
                Notify.success(Translator.trans('订单已取消成功！'));
                window.location.reload();
            });
        });

    };

});