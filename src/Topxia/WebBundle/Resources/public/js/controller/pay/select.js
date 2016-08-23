define(function(require, exports, module){

	var Notify = require('common/bootstrap-notify');

    exports.run = function() {
            
        var $modal = $('#modal');

        $(".form-paytype").on('click','.check', function() {
            var $this = $(this);
            if (!$this.hasClass('active') && !$this.hasClass('disabled')) {
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

        }).on("click" ,'.js-pay-bank',function(e){
            e.stopPropagation();
            var $this = $(this);
            $this.addClass('checked').siblings('li').removeClass('checked');
            $this.find('input').prop("checked", true);

        }).on('click', '.js-pay-bank .closed', function() {

            if(!confirm('确定解除绑定该银行卡吗')){
                return;
            }

            var $this = $(this);
            var payAgreementId = $this.closest(".js-pay-bank").find("input").val();
                        
            $.post($this.data('url'),{'payAgreementId':payAgreementId},function(response){
                if(response.success == false){
                    Notify.danger(response.message);
                }else{
                    $modal.modal('show');　
               　    $modal.html(response);
                }　
            })
        })

        $("input[name='payment']").val($('div .active').attr("id"));

        $("#copy").on('click',function(event){
            var textarea = document.createElement("textarea");
            textarea.style.position = 'fixed';
            textarea.style.top = 0;
            textarea.style.left = 0;
            textarea.style.border = 'none';
            textarea.style.outline = 'none';
            textarea.style.resize = 'none';
            textarea.style.background = 'transparent';
            textarea.style.color = 'transparent';

            textarea.value = document.location.href;
            var ele = $(textarea);
            $(this).append(ele);

            textarea.select();
            document.execCommand('copy');

            ele.remove();
            Notify.success('复制成功！');
        })
    };

});