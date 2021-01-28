define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var validator = new Validator({
            element: '#request-form'
        });

        validator.addItem({
            element: '[name="mpName"]',
            required: true,
            rule: 'byte_minlength{min:6} byte_maxlength{max:20}',
            errormessageRequired: Translator.trans('admin.wechat_app.mp_name_input.message')
        });
        validator.addItem({
            element: '[name="mpDescription"]',
            required: true,
            errormessageRequired: Translator.trans('admin.wechat_app.mp_description_input.message')
        });
        validator.addItem({
            element: '[name="contactName"]',
            required: true,
            errormessageRequired: Translator.trans('admin.wechat_app.contact_name_input.message')
        });
        validator.addItem({
            element: '[name=contactPhone]',
            required: true,
            rule: 'phone',
            errormessageRequired: Translator.trans('admin.wechat_app.contact_phone_input.message')
        });
        validator.addItem({
            element: '[name=contactQq]',
            required: true,
            rule: 'qq',
            errormessageRequired: Translator.trans('admin.wechat_app.contact_qq_input.message')
        });


    }
});