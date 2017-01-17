define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');

    exports.run = function() {

        var validator = new Validator({
            element: '#password-reset-update-form'
        });

        validator.addItem({
            element: '[name="form[password]"]',
            required: true,
            rule: 'minlength{min:5} maxlength{max:20}'
        });

        validator.addItem({
            element: '[name="form[confirmPassword]"]',
            required: true,
            rule: 'confirmation{target:"#form_password"}',
            errormessageConfirmation: Translator.trans('两次密码输入不一致，请重新输入')
        });

    };

});