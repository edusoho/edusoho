define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');

    exports.run = function() {
        var validator = new Validator({
            element: '#password-reset-form',
            onFormValidated: function(err, results, form) {
                if (err == false) {
            $('#password-reset-form').find("[type=submit]").button('loading');
                }else{
                    $('#alertxx').hide();                    
                };

            }
        });

        validator.addItem({
            element: '[name="form[email]"]',
            required: true,
            rule: 'email'
        });
    };

});