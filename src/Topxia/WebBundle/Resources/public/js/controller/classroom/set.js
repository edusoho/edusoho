define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    
    exports.run = function() {

        var validator = new Validator({
            element: '#classroom-set-form',
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#classroom-save').button('submiting').addClass('disabled');
            }
        });

        validator.addItem({
            element: '[name="price"]',
            required: true,
            rule: 'currency'
        });
        
    };

});