define(function(require, exports, module) {
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);

    var SmsSender = require('../widget/sms-sender');

    exports.run = function() {

    	var $form = $('#js-sms-modal-form');

		var smsValidator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }

                $.post($form.attr('action'),$form.serialize(),function(response){
	                $("#alert-btn").addClass('hidden');
	                $("#alerted-btn").removeClass('hidden');
                    $('.member-num').text(parseInt(response.number));
	            })
            }
        });

        var mobileRule = 'phone remote';
        if ($('[name="mobile"]').attr('readonly') == 'readonly') {
            var smsSender = new SmsSender({
                element: '.js-sms-send',
                url: $('.js-sms-send').data('url'),
                smsType:'system_remind' 
            });
            mobileRule = 'phone';
        }

        smsValidator.addItem({
            element: '[name="mobile"]',
            required: true,
            rule: mobileRule,
            display: '手机号码',
            onItemValidated: function(error, message, eleme) {
                if (error) {
                    $('.js-sms-send').addClass('disabled');
                    return;
                } else {
                    canSmsSend();
                }
            }              
        });

        smsValidator.addItem({
            element: '[name="captcha_code"]',
            required: true,
            rule: 'alphanumeric remote',
            onItemValidated: function(error, message, eleme) {
                if (message == "验证码错误"){
                    //$("#getcode_num").attr("src",$("#getcode_num").data("url")+ "?" + Math.random());
                    $('.js-sms-send').addClass('disabled');
                } else {
                    canSmsSend();
                }
            }                
        });
		
        smsValidator.addItem({
            element: '[name="sms_code_modal"]',
            required: true,
            triggerType: 'submit',
            rule: 'integer fixedLength{len:6} remote',
            display: '短信验证码'           
        });

	    $('.js-confirm').click(function(e){
	    	smsValidator.execute(function(error, results, element) {
                if (error) {
                    return false;
                }
                var smsCode = $('[name="sms_code_modal"]').val();	    	
		    	$('[name="sms_code"]').val(smsCode);
		    	$('#modal').modal('hide');
		    	$('#order-create-form').submit();
            });	            
	    	return false;
	    });

        $("#getcode_num").click(function(){ 
            $(this).attr("src",$("#getcode_num").data("url")+ "?" + Math.random()); 
        }); 

        function canSmsSend()
        {
            var mobileError = $.trim($('[name="mobile"]').closest('.controls').find('.help-block').html());
            var mobileVal = $('[name="mobile"]').val();
            if (mobileVal == '' || (mobileVal != '' && mobileError != '')){
                return false;
            }

            var captchaError = $.trim($('#captcha_code').closest('.controls').find('.help-block').html());
            var captchaVal = $('#captcha_code').val();
            if (captchaVal == '' || (captchaVal != '' && captchaError != '')) {
                return false;
            }

            $('.js-sms-send').removeClass('disabled');
            var smsSender = new SmsSender({
                element: '.js-sms-send',
                url: $('.js-sms-send').data('url'),
                smsType:'system_remind'
            });
        }
	}

});