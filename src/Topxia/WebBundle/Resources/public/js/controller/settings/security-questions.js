define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var validator = new Validator({
            element: '#settings-security-questions-form',
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#password-save-btn').button('submiting').addClass('disabled');
            }
        });

        validator.addItem({
            element: '[name="answer-1"]',
            required: true,
            rule: 'maxlength{max:20}'            
        });
        
        validator.addItem({
            element: '[name="answer-2"]',
            required: true,
            rule: 'maxlength{max:20}'
        });
        
        validator.addItem({
            element: '[name="answer-3"]',
            required: true,
            rule: 'maxlength{max:20}'
        });
    };

});