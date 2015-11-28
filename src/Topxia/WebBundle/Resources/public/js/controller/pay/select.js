define(function(require, exports, module){

	var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $(".order-pay .check ").on('click',  function() {
            $(this).addClass('active').siblings().removeClass('active').find('.icon').addClass('hide');
            
            if($(this).attr('id') == 'quickpay'){
                $('.pay-agreement').show();
            }else{
                $('.pay-agreement').hide();
            }
            $(this).find('.icon').removeClass('hide');
            $("input[name='payment']").val($(this).attr("id"));
        });

            
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

        $(".closed").on("click",function(){
            if(!confirm('确定解除绑定该银行卡吗')){
                return;
            }
            var orderId = $("input[name='orderId']").val();
            var payAgreementId = $(this).parents(".pay-bank").find("input").val();
            var payment = $("input[name='payment']").val();

            $.post($(this).data('url'),{'orderId':orderId,'payAgreementId':payAgreementId,'payment':payment},function(response){

            })
        })
    };

});