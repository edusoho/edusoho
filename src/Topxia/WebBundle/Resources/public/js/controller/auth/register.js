define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require("placeholder");
    require("jquery.bootstrap-datetimepicker");
    var SmsSender = require('../widget/sms-sender');
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
                        $("#getcode_num").attr("src",$("#getcode_num").data("url")+ "?" + Math.random()); 
                    }
                }                
            });
        };

        validator.addItem({
            element: '[name="email"]',
            required: true,
            rule: 'email email_remote'
        });

        validator.addItem({
            element: '[name="password"]',
            required: true,
            rule: 'minlength{min:5} maxlength{max:20}',
            display: '密码'
        });

        validator.addItem({
            element: '[name="confirmPassword"]',
            required: true,
            rule: 'confirmation{target:#register_password}'
        });

        validator.addItem({
            element: '[name="nickname"]',
            required: true,
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:14} remote'
        });

        validator.addItem({
            element: '[name="truename"]',
            required: true,
            rule: 'chinese minlength{min:2} maxlength{max:12}'
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
            element: '#user_terms',
            required: true,
            errormessageRequired: '勾选同意此服务协议，才能继续注册'
        });

        validator.addItem({
            element: '[name="mobile"]',
            required: true,
            rule: 'mobile'
        });

        validator.addItem({
            element: '[name="idcard"]',
            required: true,
            rule: 'idcard'
        });

        validator.addItem({
            element: '[name="emailOrMobile"]',
            required: true,
            rule: 'email_or_mobile email_or_mobile_remote'
        })

  
        if($('input[name="sms_code"]').length>0){
            validator.addItem({
                element: '[name="sms_code"]',
                required: true,
                triggerType: 'submit',
                rule: 'integer fixedLength{len:6} remote',
                display: '短信验证码'           
            });
        }


        $("#register_emailOrMobile").blur(function(){
            var emailOrMobile  = $("#register_emailOrMobile").val();
            var reg_mobile = /^1\d{10}$/;
            var isMobile = reg_mobile.test(emailOrMobile);
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


    };

});