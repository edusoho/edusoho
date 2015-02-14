define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var SmsSender = require('../widget/sms-sender');
    
    exports.run = function() {
        var validator = new Validator({
            element: '#settings-find-pay-password-form',
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                    $('#password-save-btn').button('submiting').addClass('disabled');
                }
            });
            
            var smsSender = new SmsSender({
                validator: validator,
                smsType:'sms_forget_pay_password',
                hasMobile:true            
            });
            smsSender.takeEffect();
        
    };

});