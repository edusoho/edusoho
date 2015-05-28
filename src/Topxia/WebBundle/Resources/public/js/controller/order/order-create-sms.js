define(function(require, exports, module) {
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);

    var SmsSender = require('../widget/sms-sender');

    exports.run = function() {

    	var mobile = $('[name="mobile"]').val();

    	if (mobile.length > 0) {
    		var smsValidator = new Validator({
	            element: '#js-sms-modal-form',
	            autoSubmit: true,
	            onFormValidated: function(error){
	                if (error) {
	                    return false;
	                }
	            }
	        });

    		
	        if($('input[name="sms_code_modal"]').length>0){
	            smsValidator.addItem({
	                element: '[name="sms_code_modal"]',
	                required: true,
	                triggerType: 'submit',
	                rule: 'integer fixedLength{len:6} remote',
	                display: '短信验证码'           
	            });
	        }

		    var smsSender = new SmsSender({
		    	element: '.js-sms-send',
		    	url: $('.js-sms-send').data('url'),
		        smsType:'sms_user_pay' 
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

		}
	}

});