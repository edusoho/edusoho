define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        if ($('#sms-form').length > 0) {
            $('[name="sms-close"]').click(function() {
                var registerMode = $('input[name="register-mode"]').val();
                if (registerMode == 'email_or_mobile' || registerMode == 'mobile') {
                    Notify.danger(Translator.trans('您启用了手机注册模式，不可关闭短信功能！'));
                    return false
                }
            });
        }
    }

});