define(function(require, exports, module){
	var Notify = require('common/bootstrap-notify');
    exports.run = function() {

        $(".form-paytype").on('click','.check', function() {
            var $this = $(this);
            if (!$this.hasClass('active')) {
                $this.addClass('active').siblings().removeClass('active');
                $("input[name='payment']").val($this.attr("id"));
            }
        });

        $(".form-paytype").on( 'click','.js-order-cancel',function(){
            var $this = $(this);
            $.post($this.data('url'), function(data) {
                if(data!=true) {
                    Notify.danger('订单取消失败！');
                }
                Notify.success('订单已取消成功！');
                window.location.href = $this.data('goto');
            });
        });

    };

});