define(function(require, exports, module){
	var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        
        $modal = $('#modal');
        $('#pay-form').on("submit",function(){
            var payment = $("input[name=payment]").val();
            var payAgreementId = $('.pay-bank option:selected').val();
            var orderId = $('input[name=orderId]').val();
            if(payment == 'quickpay' && payAgreementId){
                $modal.modal('show');
                $.post($('.pay-button').data('url'),{'payAgreementId':payAgreementId,'orderId':orderId,'payment':payment},function(html){
                    $('#modal').html(html);
                });
                return false;
            }
            return true; 
        })

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