define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        for (var i = 17; i >= 1; i--) {
            var id = '#article-property-tips' + i;
            var htmlId = id + '-html';
            $(id).popover({
                html: true,
                trigger: 'hover', //'hover','click'
                placement: 'left', //'bottom',
                content: $(htmlId).html()
            });
        };

        validateSmsControllerForm = function() {
            var validator = new Validator({
                element: '#sms-controller-form'
            });
            validator.addItem({
                element: '[name="sms_school_name"]',
                required: true,
                rule: 'chinese_alphanumeric minlength{min:3} maxlength{max:8}',
                display: "签名",
                errormessageRequired: '签名3-8字，建议使用汉字'
            });
        }

        if ($('#sms-form').length > 0) {

            $('[name="sms-close"]').click(function() {
                var registerMode = $('input[name="register-mode"]').val();
                if (registerMode == 'email_or_mobile' || registerMode == 'mobile') {
                    $('[name="sms_enabled"][value=1]').prop('checked', true);
                    Notify.danger("您启用了手机注册模式，不可关闭短信功能！");
                    return false
                }
            });

        }
        $("[name='sign-update']").on('click', function() {
            $("[name='submit-sign']").show();
            $("[name='status']").hide();
            validateSmsControllerForm();

        });

        $("[name='sms-open']").on('click', function() {
            validateSmsControllerForm();
        });
    }

});