define(function(require, exports, module){

	var Notify = require('common/bootstrap-notify');

    exports.run = function() {
            
        $(".form-paytype").on('click','.check', function() {
            var $this = $(this);
            if (!$this.hasClass('active')) {
                $this.addClass('active').siblings().removeClass('active');
                $("input[name='payment']").val($this.attr("id"));
            }
            if($this.attr('id') == 'quickpay'){
                $('.js-pay-agreement').show();
            }else{
                $('.js-pay-agreement').hide();
            }

        }).on( 'click','.js-order-cancel',function(){
            var $this = $(this);
            $.post($this.data('url'), function(data) {
                if(data!=true) {
                    Notify.danger('订单取消失败！');
                }
                Notify.success('订单已取消成功！');
                window.location.href = $this.data('goto');
            });

        }).on("click" ,'.js-pay-agreement li',function(){
            var $this = $(this);
            $this.addClass('checked').siblings('li').removeClass('checked');
            $this.find('input').prop("checked", true);

        }).on('click', '.js-pay-agreement .closed', function() {

            if(!confirm('确定解除绑定该银行卡吗')){
                return;
            }

            var $this = $(this);
            var orderId = $("input[name='orderId']").val();
            var payAgreementId = $this.closest(".js-pay-bank").find("input").val();
            var payment = $("input[name='payment']").val();

            $.post($this.data('url'),{'orderId':orderId,'payAgreementId':payAgreementId,'payment':payment},function(response){
                if(response.success){
                    $this.closest('.js-pay-bank').remove();
                    Notify.success(response.message);
                }else{
                    Notify.danger(response.message);
                }
            })
        })
    };

});