define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require("jquery.bootstrap-datetimepicker");
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
                $('.captcha_div').removeClass('hidden');
            }else {
                $(".email_mobile_msg").addClass('hidden');
                if($('input[name="captcha_enabled"]').val() == 0) {
                    $('.captcha_div').addClass('hidden');
                }
            }
            if (isEmail || isMobile) {
                result = true;
            }
            return  result;  
        },
            "{{display}}格式错误"
    );

    exports.run = function() {
        $(".date").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });
        var $form = $('#register-form');
        var validator = new Validator({
            element: $form,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#register-btn').button('submiting').addClass('disabled');
            },
            failSilently: true
        });

        if ($("#getcode_num").length > 0){
            
            $("#getcode_num").click(function(){ 
                $(this).attr("src",$("#getcode_num").data("url")+ "?" + Math.random()); 
            }); 

            validator.addItem({
                element: '[name="captcha_num"]',
                required: true,
                rule: 'alphanumeric remote',
                onItemValidated: function(error, message, eleme) {
                    if (message == "验证码错误"){
                        $('.js-sms-send').addClass('disabled');
                        $("#getcode_num").attr("src",$("#getcode_num").data("url")+ "?" + Math.random()); 
                    } else {
                        $('.js-sms-send').removeClass('disabled');
                    }
                }                
            });
        };

        if ($('input[name="email"]').length > 0) {
            validator.addItem({
                element: '[name="email"]',
                required: true,
                rule: 'email_or_mobile_check email_remote'
            });
        }

        if ($('input[name="verifiedMobile"]').length > 0) {
            validator.addItem({
                element: '[name="verifiedMobile"]',
                required: true,
                rule: 'email_or_mobile_check email_or_mobile_remote'
            });
        }
        
        if ($('input[name="emailOrMobile"]').length > 0) {
            validator.addItem({
                element: '[name="emailOrMobile"]',
                required: true,
                rule: 'email_or_mobile_check email_or_mobile_remote'
            })
        }
        
        validator.addItem({
            element: '[name="nickname"]',
            required: true,
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:14} remote'
        });

        validator.addItem({
            element: '[name="password"]',
            required: true,
            rule: 'minlength{min:5} maxlength{max:20}',
            display: '密码'
        });


        validator.addItem({
            element: '#user_terms',
            required: true,
            errormessageRequired: '勾选同意此服务协议，才能继续注册'
        });

  
        if ($('input[name="em_sms_code"]').length > 0) {
            validator.addItem({
                element: '[name="em_sms_code"]',
                required: true,
                triggerType: 'submit',
                rule: 'integer fixedLength{len:6} remote',
                display: '短信验证码'           
            });
        }


        $("#register_emailOrMobile").blur(function(){
            var emailOrMobile  = $("#register_emailOrMobile").val();
            emSmsCodeValidate(emailOrMobile);
        });

        $("#register_mobile").blur(function(){
            var mobile  = $("#register_mobile").val();
            emSmsCodeValidate(mobile);
        });


        if ($('.js-sms-send').length > 0 ) {
            var smsSender = new SmsSender({
                element: '.js-sms-send',
                url: $('.js-sms-send').data('url'),
                smsType:'sms_registration',
                dataTo : $('[name="mobile"]').val() == null? 'emailOrMobile' : 'mobile',
                preSmsSend: function(){
                    var couldSender = true;
                    var $mobile_target =  validator.query('[name="mobile"]') == null?  validator.query('[name="emailOrMobile"]') : validator.query('[name="mobile"]');
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

        }

        function emSmsCodeValidate(mobile){
            var reg_mobile = /^1\d{10}$/;
            var isMobile = reg_mobile.test(mobile);
            if(isMobile){
                validator.addItem({
                    element: '[name="em_sms_code"]',
                    required: true,
                    triggerType: 'submit',
                    rule: 'integer fixedLength{len:6} remote',
                    display: '短信验证码'           
                });
            }else{
                validator.removeItem('[name="em_sms_code"]');
            }
        }


    };

});