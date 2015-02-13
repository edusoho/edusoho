define(function(require, exports, module) {

    var SmsSender = require('edusoho.smsSender');

    exports.run = function() {
    	var mobile = $('[name="mobile"]').val();
    	if (mobile.length > 0) {
		    var smsSender = new SmsSender({
		        smsType:'sms_user_pay'  
		    });
		    smsSender.takeEffect();
		    
		    $('.js-confirm').unbind('click');
		    $('.js-confirm').click(function(){
		    	var smsCode = $('[name="sms_code_modal"]').val();	    	
		    	$('[name="sms_code"]').val(smsCode);
		    	$('#modal').modal('hide');
		    	$('#order-create-form').submit();
		    });
		}else{
			$('.js-confirm').unbind('click');
		    $('.js-confirm').click(function(){
		    	$('#modal').modal('hide');
		    });
		}
	}

});