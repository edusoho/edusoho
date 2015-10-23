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

            $("#orders-table").on('click', '.pay', function(){
                    $.post($(this).data('url'), {orderId: $(this).data('orderId')} ,function(html) {
                            $("body").html(html);
                    });
            });

        $("#orders-table").on('click', '.cancel', function(){
            if (!confirm('真的要取消订单吗？')) {
                return false;
            }

            $.post($(this).data('url'), function(data) {
                if(data!=true) {
                    Notify.danger('订单取消失败！'); 
                }
                Notify.success('订单已取消成功！');
                window.location.reload();
            });
        });

    };

});