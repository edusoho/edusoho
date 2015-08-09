define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    var SmsSender = require('../widget/sms-sender');

    Validator.addRule(
        'email_or_mobile_check',
        function(options, commit) {
            var emailOrMobile = options.element.val();
            var reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            var reg_mobile = /^1\d{10}$/;
            var result =false;
            var isEmail = reg_email.test(emailOrMobile);
            var isMobile = reg_mobile.test(emailOrMobile);
            if(isMobile){
                $(".email_mobile_msg").removeClass('hidden');
            }else {
                $(".email_mobile_msg").addClass('hidden');
            }
            if (isEmail || isMobile) {
                result = true;
            }
            return  result;  
        },
            "{{display}}格式错误"
    );

    exports.run = function() {
        $('#bind-new-btn').on('click', function() {
            var $btn = $(this);

            if ($('#user_terms').length != 0) {
                if(!$('#user_terms').find('input[type=checkbox]').attr('checked')) {
                    Notify.danger('勾选同意此服务协议，才能继续注册！');
                    return;
                };
            };

            $('#bind-new-form-error').hide();
            $btn.button('loading');

            $.post($btn.data('url'), function(response) {
                if (!response.success) {
                    $('#bind-new-form-error').html(response.message).show();
                    return ;
                }
                Notify.success('登录成功，正在跳转至首页！');
                window.location.href = response._target_path;

            }, 'json').fail(function() {
                Notify.danger('登录失败，请重新登录后再试！');
            }).always(function() {
                $btn.button('reset');
            });

        });

        $('#user_terms').on('click', 'input[type=checkbox]', function() {
            if($(this).attr('checked')) {
                $(this).attr('checked',false);
            } else {
                $(this).attr('checked',true);
            };
        });

        var $form = $('#bind-exist-form');

        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $form.find('[type=submit]').button('loading');
                $("#bind-exist-form-error").hide();

                $.post($form.attr('action'), $form.serialize(), function(response) {
                    if (!response.success) {
                        $("#bind-exist-form-error").html(response.message).show();
                        return ;
                    }

                    Notify.success('绑定帐号成功，正在跳转至首页！');
                    window.location.href = response._target_path;;

                }, 'json').fail(function() {
                    Notify.danger('绑定失败，请重新登录后再试。');
                }).always(function(){
                    $form.find('[type=submit]').button('reset');
                });

            }
        });

        validator.addItem({
            element: '#bind-email-field',
            required: true,
            rule: 'email_or_mobile'
        });

        validator.addItem({
            element: '#bind-password-field',
            required: true
        });


        if ($('#set-bind-exist-form').length > 0) {
            var $formSet = $('#set-bind-exist-form');

            var validatorSet = new Validator({

                element: $formSet,
                autoSubmit: false,
                onFormValidated: function(error, results, $form) {
                    if (error) {
                        return false;
                    }

                    /*var nickname = '';
                    if ($('#set-bind-nickname-field').length >0 ) {
                        nickname = $('#set-bind-nickname-field').val();
                    }

                    var emailOrMobile = ''
                    if ($('#email_or_mobile_remote').length >0 ) {
                        emailOrMobile = $('#set-bind-email-field').val();
                    }*/

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
                       $('#set-bind-new-btn').button('reset');
                    });
                }
            });
            
            if ($('#set_bind_email').length > 0) {
                validatorSet.addItem({
                    element: '#set_bind_email',
                    required: true,
                    rule: 'email email_remote'
                });
            }

            if ($('#set_bind_mobile').length > 0) {
                validatorSet.addItem({
                    element: '#set_bind_mobile',
                    required: true,
                    rule: 'phone email_or_mobile_remote',
                    onItemValidated: function(error, message, eleme) {
                        if (error) {
                            $('.js-sms-send').addClass('disabled');
                            return;
                        } else {
                            $('.js-sms-send').removeClass('disabled');
                        }
                    }
                });
            }

            if ($('#set_bind_emailOrMobile').length > 0) {
                validatorSet.addItem({
                    element: '#set_bind_emailOrMobile',
                    required: true,
                    rule: 'email_or_mobile_check email_or_mobile_remote',
                    onItemValidated: function(error, message, eleme) {
                        if (error) {
                            $('.js-sms-send').addClass('disabled');
                            return;
                        } else {
                            $('.js-sms-send').removeClass('disabled');
                        }
                    }
                });
            }
            

            validatorSet.addItem({
                element: '#set-bind-nickname-field',
                required: true,
                rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:18} remote'
            });

            if ($('input[name="sms_code"]').length > 0) {
                validatorSet.addItem({
                    element: '[name="sms_code"]',
                    required: true,
                    triggerType: 'submit',
                    rule: 'integer fixedLength{len:6} remote',
                    display: '短信验证码'           
                });

                $formSet.on('click','.js-sms-send',function(){
                    var $mobile_target =  validatorSet.query('#set_bind_mobile') == null?  validatorSet.query('#set_bind_emailOrMobile') : validatorSet.query('#set_bind_mobile');
                    $mobile_target.execute(function(error, results, element) {
                        if (error) {
                            return;
                        }
                    });
                })
            }


            /*if ($('.js-sms-send').length > 0 ) {
                var smsSender = new SmsSender({
                    element: '.js-sms-send',
                    url: $('.js-sms-send').data('url'),
                    smsType:'sms_registration',
                    dataTo : 'set_bind_emailOrMobile',
                    preSmsSend: function(){
                        var couldSender = true;
                        var $mobile_target =  validatorSet.query('#set_bind_mobile') == null?  validatorSet.query('#set_bind_emailOrMobile') : validatorSet.query('#set_bind_mobile');
                        
                        $mobile_target.execute(function(error, results, element) {
                            if (error) {
                                couldSender = false;
                                return;
                            }
                            couldSender = true;
                            return;
                        });

                        return couldSender;
                    }      
                });
            }*/
        }

    };

});