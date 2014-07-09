define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $form = $('#register-form');
        var validator = new Validator({
            element: $form,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#register-btn').button('submiting').addClass('disabled');
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

        validator.addItem({
            element: '[name="confirmPassword"]',
            required: true,
            rule: 'confirmation{target:#register_password}'
        });

        validator.addItem({
            element: '[name="nickname"]',
            required: true,
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:14} remote'
        });

        validator.addItem({
            element: '#user_terms',
            required: true,
            errormessageRequired: '勾选同意此服务协议，才能继续注册'
        });

        validator.addItem({
            element: '[name="truename"]',
            required: $form.find('[name="truename"]').attr('required') == 'required',
            rule: 'chinese minlength{min:2} maxlength{max:12}'
        });

        validator.addItem({
            element: '[name="mobile"]',
            required: $form.find('[name="mobile"]').attr('required') == 'required',
            rule: 'mobile'
        });

        validator.addItem({
            element: '[name="idcard"]',
            required: $form.find('[name="idcard"]').attr('required') == 'required',
            rule: 'idcard'
        });

    };

});