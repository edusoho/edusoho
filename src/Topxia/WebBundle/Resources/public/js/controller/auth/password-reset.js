define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    var SmsSender = require('../widget/sms-sender');
    require('common/validator-rules').inject(Validator);
    exports.run = function() {
        var validator = new Validator({
            element: '#password-reset-form',
            onFormValidated: function(err, results, form) {
                if (err == false) {
            $('#password-reset-form').find("[type=submit]").button('loading');
                }else{
                    $('#alertxx').hide();                    
                };

            }
        });
        
        
        var smsSender;
        
        var makeValidator = function(type) {
            if (("undefined" != typeof validator)&&("undefined" != typeof validator.destroy)){
                validator.destroy();
            }

            if ('email' == type) {
                validator = new Validator({
                    element: '#password-reset-form',
                    onFormValidated: function(err, results, form) {
                        if (err == false) {
                    $('#password-reset-form').find("[type=submit]").button('loading');
                        }else{
                            $('#alertxx').hide();                    
                        };

                    }
                });

                validator.addItem({
                    element: '[name="form[email]"]',
                    required: true,
                    rule: 'email'
                });
            }

            if ('mobile' == type) {
                validator = new Validator({
                    element: '#password-reset-by-mobile-form',
                    onFormValidated: function(err, results, form) {
                        if (err == false) {
                            $('#password-reset-by-monile-form').find("[type=submit]").button('loading');
                        }else{
                            $('#alertxx').hide();                    
                        };

                    }
                });

                validator.addItem({
                    element: '[name="nickname"]',
                    required: true
                });


                validator.addItem({
                    element: '[name="mobile"]',
                    required: true,
                    rule: 'phone'            
                });

                validator.addItem({
                    element: '[name="sms_code"]',
                    required: true,
                    rule: 'integer fixedLength{len:6} remote',
                    display: '短信验证码'           
                });

                if (('undefined' != typeof smsSender)&&("undefined" != typeof smsSender.destroy)){
                    smsSender.destroy();
                }
            }
        }

        makeValidator('email');
        $('.js-find-by-email').mouseover(function () {
            $('.js-find-by-email').addClass('active');
            $('.js-find-by-mobile').removeClass('active');

            makeValidator('email');
            $('#password-reset-by-mobile-form').hide();
            $('#password-reset-form').show();
        })

        $('.js-find-by-mobile').mouseover(function () {
            $('.js-find-by-email').removeClass('active');
            $('.js-find-by-mobile').addClass('active');

            makeValidator('mobile');
            smsSender = new SmsSender({
                element: '.js-sms-send',
                url: $('.js-sms-send').data('url'),
                smsType:'sms_forget_password',
                getPostData: function(data){
                    data.nickname = $('[name="nickname"]').val();
                    return data;
                },
                preSmsSend: function(){

                    var mobileValidateResult = true;
                    var nicknameValidateResult = true;
                    validator.query('[name="mobile"]').execute(function(error, results, element) {
                        if (error) {
                            mobileValidateResult = false;
                            return;
                        }
                    });
                    validator.query('[name="nickname"]').execute(function(error, results, element) {
                        if (error) {
                            nicknameValidateResult = false;
                            return ;
                        }
                    });

                    if(mobileValidateResult && nicknameValidateResult) {
                        return true;
                    }

                    return false;
                }
            });

            $('#password-reset-form').hide();
            $('#password-reset-by-mobile-form').show();

        })
    };

});