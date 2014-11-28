define(function(require, exports, module) {

  
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var validator = new Validator({
            element: '#group-settings-form',
            failSilently: true,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#push').button('submiting').addClass('disabled');
            }
        });

        validator.addItem({
            element: '[name="daySign"]',
            required: true,
            rule: 'positive_integer' 
        });

        validator.addItem({
            element: '.recharge',
            required: true,
            rule: 'positive_integer' 
        });

    }
    
});