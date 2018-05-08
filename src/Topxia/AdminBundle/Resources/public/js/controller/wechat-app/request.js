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
            errormessageRequired: Translator.trans('请填写10个字以内的小程序标题')
        });
        validator.addItem({
            element: '[name="mpDescription"]',
            required: true,
            errormessageRequired: Translator.trans('请填写小程序介绍')
        });
        validator.addItem({
            element: '[name="contactName"]',
            required: true,
            errormessageRequired: Translator.trans('请填写联系人姓名')
        });
        validator.addItem({
            element: '[name=contactPhone]',
            required: true,
            rule: 'phone',
            errormessageRequired: Translator.trans('请填写正确的电话号码')
        });
        validator.addItem({
            element: '[name=contactQq]',
            required: true,
            rule: 'qq',
            errormessageRequired: Translator.trans('请填写正确的 QQ 号')
        });


    }
});