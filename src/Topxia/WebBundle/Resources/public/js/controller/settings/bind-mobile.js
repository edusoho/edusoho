define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var SmsSend = require('edusoho.smsSend');

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
		var smsSend = new SmsSend();
        smsSend.setValidator(validator);
        smsSend.setSmsType('sms_bind');
        smsSend.sethasMobile(true);
        smsSend.takeEffect();

	};
});