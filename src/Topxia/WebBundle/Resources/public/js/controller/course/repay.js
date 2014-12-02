define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $(".cancel").on('click', function(){
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