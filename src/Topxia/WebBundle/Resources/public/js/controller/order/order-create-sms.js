define(function(require, exports, module) {
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);

    var SmsSender = require('../widget/sms-sender');

    exports.run = function() {
    	var mobile = $('[name="mobile"]').val();

    	if (mobile.length > 0) {
    		var validator = new Validator({
	            element: '#js-sms-modal-form',
	            autoSubmit: true,
	            onFormValidated: function(error){
	                if (error) {
	                    return false;
	                }
	            }
	        });

		    var smsSender = new SmsSender({
		    	validator:validator,
		    	element: '.js-sms-send',
		    	url: $('.js-sms-send').data('url'),
		        smsType:'sms_user_pay'  
		    });

	    	var smsModalCodeValidator = new Validator({
	            element: '#js-sms-modal-form',
	            autoSubmit: false
	        });

		    var refresh = function () {
		    	smsModalCodeValidator = new Validator({
		            element: '#js-sms-modal-form',
		            autoSubmit: false
		        });

		        smsModalCodeValidator.addItem({
					element: '[name="sms_code_modal"]',
					required: true,
					display: '短信验证码',
					rule: 'integer fixedLength{len:6}'  
				});
		    }		    

		    $('.js-confirm').unbind('click');
		    $('.js-confirm').click(function(){
			    refresh();
		    	smsModalCodeValidator.execute(function(error, results, element) {
	                if (error) {
	                    return false;
	                }
	                var smsCode = $('[name="sms_code_modal"]').val();	    	
			    	$('[name="sms_code"]').val(smsCode);
			    	$('#modal').modal('hide');
			    	$('#order-create-form').submit();
	            });	            
		    	
		    });

	        $('#modal').on('shown.bs.modal', function () {
				setTimeout(refresh,500); 
		    });
		    refresh();
		}else{
			$('.js-confirm').unbind('click');
		    $('.js-confirm').click(function(){
		    	$('#modal').modal('hide');
		    });
		}
	}

});