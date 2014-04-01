define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var validator = new Validator({
            element: '#course-buy-form',
            autoSubmit: true
        });
        if ($('#course-buy-form').find('input[name="mobile"]').length > 0){
            validator.addItem({
                element: '[name="mobile"]',
                rule: 'phone',
                required: true
            });
        }

        if ($('#course-buy-form').find('input[name="truename"]').length > 0){
            validator.addItem({
                element: '[name="truename"]',
                rule: 'chinese byte_minlength{min:4} byte_maxlength{max:10}',
                required: true
            });
        }

        if ($('#course-buy-form').find('input[name="qq"]').length > 0){
            validator.addItem({
                element: '[name="qq"]',
                rule: 'qq'
            });
        }





        $('#show-coupon-input').on('click', function(){
            $(this).parents('.form-group').find('.input-group').show();
        });

        $('.btn-cancel-coupon').on('click', function(){
            $(this).parents('.form-group').find('.input-group').remove();
        });

        $('.btn-use-coupon').on('click', function(){

            coupon_code = $('[name=coupon]').val();

            $.post($(this).data('url'), {code:coupon_code}, function(response){
                if (response.useable == 'yes') {

                    var html = '<span class="control-text"><strong class="money">'
                            + response.afterAmount
                            + '</strong><span class="text-muted"> 元</span> - <span class="text-muted">已优惠 </span><strong>'
                            + response.decreaseAmount
                            + '</strong><span class="text-muted"> 元</span></span>';

                    $('.money-text').html(html);

                    $('.coupon-error').hide();

                } else {
                    var message = '<span class="text-danger">'+response.message+'</span>';
                    $('.coupon-error').html(message).show();
                }
            });
        });


    };

});