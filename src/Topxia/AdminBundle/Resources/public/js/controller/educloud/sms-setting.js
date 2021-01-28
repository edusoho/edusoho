define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        if ($('#sms-form').length > 0) {
            $('[name="sms-close"]').click(function() {
                var registerMode = $('input[name="register-mode"]').val();
                if (registerMode == 'email_or_mobile' || registerMode == 'mobile') {
                    Notify.danger(Translator.trans('admin.edu_cloud.close_sms_fail_hint'));
                    return false
                }
            });
        }
    }

});