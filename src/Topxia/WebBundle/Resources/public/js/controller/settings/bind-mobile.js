define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var SmsSender = require('edusoho.smsSender');

    exports.run = function() {
		var validator = new Validator({
            element: '#bind-mobile-form',
            autoSubmit: true,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#submit-btn').button('submiting').addClass('disabled');
            }
        });
		var smsSender = new SmsSender({
            validator: validator,
            smsType:'sms_bind',
            hasMobile:true            
        });
        smsSender.takeEffect();

	};
});