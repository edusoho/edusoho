define(function(require, exports, module) {
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);

    var SmsSender = require('../widget/sms-sender');

    exports.run = function() {

        Validator.addRule('smsCodeCheck', function(options, commit) {
            var mobile = $('input[name="mobile"]').val();
            var element = options.element,
                url = options.url ? options.url : (element.data('url') ? element.data('url') : null);
            $.get(url, {value:element.val(),mobile:mobile}, function(response) {
                commit(response.success, response.message);
            }, 'json');
        })

    	var $form = $('#js-sms-modal-form');

		var smsValidator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }

                $.post($form.attr('action'),$form.serialize(),function(response){
                    $('#modal').modal('hide');
	                $("#alert-btn").addClass('hidden');
	                $("#alerted-btn").removeClass('hidden');
                    $('.member-num').text(parseInt(response.number));
	            })
            }
        });

        if ($('input[name="mobile"]').attr('readonly') != 'readonly') {
            validatorItems(smsValidator);
        }

	    $('.js-confirm').click(function(e){
            $form.submit();
	    });

        $("#getcode_num").click(function(){ 
            $(this).attr("src",$("#getcode_num").data("url")+ "?" + Math.random()); 
        }); 

        function canSmsSend()
        {
            var mobileError = $.trim($('[name="mobile"]').closest('.controls').find('.help-block').html());
            var mobileVal = $('[name="mobile"]').val();
            if (mobileVal == '' || (mobileVal != '' && mobileError != '')){
                $('.js-sms-send').attr('disabled',true);
                return false;
            }

            var captchaError = $.trim($('#captcha_code').closest('.controls').find('.help-block').html());
            var captchaVal = $('#captcha_code').val();
            if (captchaVal == '' || (captchaVal != '' && captchaError != '')) {
                $('.js-sms-send').attr('disabled',true);
                return false;
            }

            $('.js-sms-send').attr('disabled',false);
            var smsSender = new SmsSender({
                element: '.js-sms-send',
                url: $('.js-sms-send').data('url'),
                smsType:'system_remind'
            });
        }

        $('.modify_mobile').click(function(){
            $(this).hide();
            $('input[name="mobile"]').attr('readonly',false);
            $('.form-group').show();

            validatorItems(smsValidator);

        })

        function validatorItems(validator)
        {
            validator.addItem({
                element: '[name="mobile"]',
                required: true,
                rule: 'phone smsCodeCheck',
                display: '手机号码',
                onItemValidated: function(error, message, eleme) {
                    if (error) {
                        $('.js-sms-send').attr('disabled',true);
                        return;
                    } else {
                        canSmsSend();
                    }
                }              
            });

            validator.addItem({
                element: '[name="captcha_code"]',
                required: true,
                rule: 'alphanumeric remote',
                onItemValidated: function(error, message, eleme) {
                    if (message == "验证码错误"){
                        $("#getcode_num").attr("src",$("#getcode_num").data("url")+ "?" + Math.random());
                        $('.js-sms-send').attr('disabled',true);
                    } else {
                        canSmsSend();
                    }
                }                
            });
            
            validator.addItem({
                element: '[name="sms_code_modal"]',
                required: true,
                triggerType: 'submit',
                rule: 'integer fixedLength{len:6} smsCodeCheck',
                display: '短信验证码'           
            });
        }
	}

});