define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var SmsSender = require('../widget/sms-sender');

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

        validator.addItem({
            element: '[name="password"]',
            required: true          
        });

        validator.addItem({
            element: '[name="mobile"]',
            required: true,
            rule: 'phone'            
        });

        if($('input[name="sms_code"]').length>0){
            validator.addItem({
                element: '[name="sms_code"]',
                required: true,
                triggerType: 'submit',
                rule: 'integer fixedLength{len:6} remote',
                display: '短信验证码'           
            });
        }

		var smsSender = new SmsSender({
            element: '.js-sms-send',
            url: $('.js-sms-send').data('url'),
            smsType: 'sms_bind',
            preSmsSend: function(){
                var couldSender = true;

                validator.query('[name="mobile"]').execute(function(error, results, element) {
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

	};
});