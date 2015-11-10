define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    Validator.addRule(
        'email_or_mobile_check',
        function(options, commit) {
            var emailOrMobile = options.element.val();
            var reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            var reg_mobile = /^1\d{10}$/;
            var result =false;
            var isEmail = reg_email.test(emailOrMobile);
            var isMobile = reg_mobile.test(emailOrMobile);
            if (isEmail || isMobile) {
                result = true;
            }
            return  result;  
        },
            "{{display}}格式错误"
    );

    exports.run = function() {
        var $formSet = $('#set-bind-new-form');

        var validatorSet = new Validator({

            element: $formSet,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                if(!$('#user_terms').find('input[type=checkbox]').attr('checked')) {
                    Notify.danger('勾选同意此服务协议，才能继续注册！');
                    return ;
                }
                $form.find('[type=submit]').button('loading');
                $("#bind-new-form-error").hide();

                $.post($formSet.attr('action'), $formSet.serialize(), function(response) {
                    if (!response.success) {
                        $('#bind-new-form-error').html(response.message).show();
                        return ;
                    }
                    Notify.success('登录成功，正在跳转至首页！');
                    window.location.href = response._target_path;

                }, 'json').fail(function() {
                    Notify.danger('登录失败，请重新登录后再试！');
                }).always(function() {
                   $formSet.find('[type=submit]').button('reset');
                });
            }
        });

        $('#user_terms').on('click', 'input[type=checkbox]', function() {
            if($(this).attr('checked')) {
                $(this).attr('checked',false);
            } else {
                $(this).attr('checked',true);
            };
        });

        validatorSet.addItem({
            element: '#set_bind_email',
            required: true,
            rule: 'email email_remote'
        });

        validatorSet.addItem({
            element: '#set-bind-nickname-field',
            required: true,
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:18} remote'
        });

    };

});