define(function (require, exports, module) {
    "use strict";

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function () {
        var $form = $('#reset-email-form');
        var validator = new Validator({
            element: $form,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#next-btn').button('submiting').addClass('disabled');
            },
            failSilently: true
        });

        validator.addItem({
            element: '[name="email"]',
            required: true,
            rule: 'email email_remote'
        });

        validator.addItem({
            element: '[name="password"]',
            required: true,
            rule: 'minlength{min:5} maxlength{max:20}'
        });
    }
});
