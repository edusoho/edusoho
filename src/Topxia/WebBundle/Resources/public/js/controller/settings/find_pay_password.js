define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

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
            validator.addItem({
                element: '[name="answer"]',
                required: true,
                rule: 'maxlength{max:20}'            
            });
        
    };

});