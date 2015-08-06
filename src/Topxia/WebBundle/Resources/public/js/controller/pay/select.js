define(function(require, exports, module){
	var Notify = require('common/bootstrap-notify');
    exports.run = function() {

        $(".order-pay .check ").on('click',  function() {
            $(this).addClass('active').siblings().removeClass('active').find('.icon').addClass('hide');
            $(this).find('.icon').removeClass('hide');
            $("input[name='payment']").val($(this).attr("id"));
        });

        $('.link-light').click( function(){
            var that = this;
            $.post($(this).data('url'), function(data) {
                if(data!=true) {
                    Notify.danger('订单取消失败！');
                }
                Notify.success('订单已取消成功！');
                window.location.href = $(that).data('goto');
            });
        });

    };

});