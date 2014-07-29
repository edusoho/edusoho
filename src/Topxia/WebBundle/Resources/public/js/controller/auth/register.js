define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require("jquery.bootstrap-datetimepicker");

    exports.run = function() {
        $(".date").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });
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
            element: '[name="truename"]',
            required: true,
            rule: 'chinese minlength{min:2} maxlength{max:12}'
        });

        validator.addItem({
            element: '[name="company"]',
            required: true,
        });

        validator.addItem({
            element: '[name="job"]',
            required: true,
        });

        validator.addItem({
            element: '#user_terms',
            required: true,
            errormessageRequired: '勾选同意此服务协议，才能继续注册'
        });

        validator.addItem({
            element: '[name="mobile"]',
            required: true,
            rule: 'mobile'
        });

        validator.addItem({
            element: '[name="idcard"]',
            required: true,
            rule: 'idcard'
        });

         validator.addItem({
            element: '[name="intField1"]',
            required: true,
            rule: 'int'
        });

        validator.addItem({
            element: '[name="intField2"]',
            required: true,
            rule: 'int'
        });

        validator.addItem({
            element: '[name="intField3"]',
            required: true,
            rule: 'int'
        });

        validator.addItem({
            element: '[name="intField4"]',
            required: true,
            rule: 'int'
        });

        validator.addItem({
            element: '[name="intField5"]',
            required: true,
            rule: 'int'
        });

        validator.addItem({
            element: '[name="floatField1',
            required: true,
            rule: 'float'
        });

        validator.addItem({
            element: '[name="floatField2',
            required: true,
            rule: 'float'
        });

        validator.addItem({
            element: '[name="floatField3',
            required: true,
            rule: 'float'
        });

        validator.addItem({
            element: '[name="floatField4',
            required: true,
            rule: 'float'
        });

        validator.addItem({
            element: '[name="floatField5',
            required: true,
            rule: 'float'
        });

        validator.addItem({
            element: '[name="dateField1"]',
            required: true,
            rule: 'date'
        });

        validator.addItem({
            element: '[name="dateField2"]',
            required: true,
            rule: 'date'
        });

        validator.addItem({
            element: '[name="dateField3"]',
            required: true,
            rule: 'date'
        });

        validator.addItem({
            element: '[name="dateField4"]',
            required: true,
            rule: 'date'
        });

        validator.addItem({
            element: '[name="dateField5"]',
            required: true,
            rule: 'date'
        });

        validator.addItem({
            element: '[name="varcharField1"]',
            required: true
        });

        validator.addItem({
            element: '[name="varcharField2"]',
            required: true
        });

        validator.addItem({
            element: '[name="varcharField3"]',
            required: true
        });

        validator.addItem({
            element: '[name="varcharField4"]',
            required: true
        });

        validator.addItem({
            element: '[name="varcharField5"]',
            required: true
        });

        validator.addItem({
            element: '[name="varcharField6"]',
            required: true
        });

        validator.addItem({
            element: '[name="varcharField7"]',
            required: true
        });

        validator.addItem({
            element: '[name="varcharField8"]',
            required: true
        });

        validator.addItem({
            element: '[name="varcharField9"]',
            required: true
        });

        validator.addItem({
            element: '[name="varcharField10"]',
            required: true
        });    

        validator.addItem({
            element: '[name=textField1]',
            required: true
        });

        validator.addItem({
            element: '[name="textField2"]',
            required: true
        });

        validator.addItem({
            element: '[name="textField3"]',
            required: true
        });

        validator.addItem({
            element: '[name="textField4"]',
            required: true
        });

        validator.addItem({
            element: '[name="textField5"]',
            required: true
        });

        validator.addItem({
            element: '[name="textField6"]',
            required: true
        });

        validator.addItem({
            element: '[name="textField7"]',
            required: true
        });

        validator.addItem({
            element: '[name="textField8"]',
            required: true
        });

        validator.addItem({
            element: '[name="textField9"]',
            required: true
        });

        validator.addItem({
            element: '[name="textField10"]',
            required: true
        });     
    };

});