define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var validator = new Validator({
            element: '#invite-form',
            failSilently: true,
            triggerType: 'change'
        });

        validator.addItem({
            element: '[name=promoted_user_value]',
            required: false,
            rule:'positive_integer'
        });

        validator.addItem({
            element: '[name=promote_user_value]',
            required: false,
            rule:'positive_integer'
        });

        validator.addItem({
            element: '[name=deadline]',
            required: true,
            rule:'positive_integer'
        });

        validator.addItem({
          element: '[name=remain_number]',
          required: false,
          rule: 'positive_integer min{min:1} max{max:1000}',
          errormessage: '请输入1-1000的数字',
        });

        validator.addItem({
          element: '[name=mobile]',
          required: false,
          rule: 'mobile',
          errormessageMobile:'请输入有效的手机号码'
        });
    };

});