define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var validator = new Validator({
            element: '#course-buy-form',
            failSilently: true
        });

        if ($('#course-buy-form').find('input[name="mobile"]').length > 0){
            validator.addItem({
                element: '[name="mobile"]',
                onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#join-course-btn').button('submiting').addClass('disabled');
                 },
                required: true,
                rule: 'phone'
            });
        }

        if ($('#course-buy-form').find('input[name="truename"]').length > 0){
            validator.addItem({
                element: '[name="truename"]',
                required: true,
                rule: 'chinese byte_minlength{min:4} byte_maxlength{max:10}'
            });
        }

        if ($('#course-buy-form').find('input[name="qq"]').length > 0){
            validator.addItem({
                element: '[name="qq"]',
                required: true,
                rule: 'qq'
            });
        }

        validator.addItem({
            element: '[name="idcard"]',
            required: true,
            rule: 'idcard'
        });

        validator.addItem({
            element: '[name="gender"]',
            required: true
        });

        validator.addItem({
            element: '[name="company"]',
            required: true
        });

        validator.addItem({
            element: '[name="job"]',
            required: true
        });

        validator.addItem({
            element: '[name="weibo"]',
            required: true,
            rule: 'url',
            errormessageUrl: '微博地址不正确，须以http://开头。'
        });

        validator.addItem({
            element: '[name="weixin"]',
            required: true,
        });
        for(var i=1;i<=5;i++){
             validator.addItem({
                 element: '[name="intField'+i+'"]',
                 required: true,
                 rule: 'int'
             });

              validator.addItem({
                element: '[name="floatField'+i+'"]',
                required: true,
                rule: 'float'
             });

             validator.addItem({
                element: '[name="dateField'+i+'"]',
                required: true,
                rule: 'date'
             });
        }

        for(var i=1;i<=10;i++){
            validator.addItem({
                element: '[name="varcharField'+i+'"]',
                required: true
            });

            validator.addItem({
                element: '[name="textField'+i+'"]',
                required: true
            });

        }
        $('#show-coupon-input').on('click', function(){
            var $form = $(this).parents('form');
            if ($(this).data('status') == 'hide') {
                $form.find('.coupon-input-group').removeClass('hide');
                $form.find('#show-coupon').addClass('hide');
                $form.find('#hide-coupon').removeClass('hide');
                $(this).data('status', 'show');
            } else if ($(this).data('status') == 'show') {
                $form.find('.coupon-input-group').addClass('hide');
                $form.find('#show-coupon').removeClass('hide');
                $form.find('#hide-coupon').addClass('hide');
                $(this).data('status', 'hide');
            }
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
                    if (response.afterAmount === '0.00') {
                        $('#course-pay').text('去学习');
                    }

                    $('.coupon-error').html('');
                    $('[name=coupon]').attr("readonly",true);
                    $('.btn-use-coupon').addClass('disabled');
                } else {
                    var message = '<span class="text-danger">'+response.message+'</span>';
                    $('.coupon-error').html(message).show();
                    $('[name=coupon]').val('');
                }
            });
        });

    };

});