define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var CaptchaModal = require('./captcha-mobile-modal.js');
    

    exports.run = function() {

        var dataTo = '';
        var smsType = '';
        var captchaNum = 'captcha_num';
        if ($('input[name="set_bind_emailOrMobile"]').length > 0) {
            dataTo = 'set_bind_emailOrMobile';
            smsType = 'sms_registration';
        } else if ($('input[name="mobile"]').length > 0) {
            dataTo = 'mobile';
            if ($('#password-reset-by-mobile-form').length > 0) {
                smsType = 'sms_forget_password';
            } else if ($('#settings-find-pay-password-form').length > 0) {
                smsType = 'sms_forget_pay_password';
            }else{
                smsType = 'sms_bind';
            }
        } else {
            dataTo = $('[name="verifiedMobile"]').val() == null? 'emailOrMobile' : 'verifiedMobile';
            smsType = 'sms_registration';
        }

        $('#captcha-form').find('#getcode_num').attr("src",$("#getcode_num").data("url")+ "?" + Math.random()); 

        var captchaModal = new CaptchaModal({
            element: '#captcha-form',
            dataTo: dataTo,
            smsType: smsType,
            captchaNum: captchaNum,
        });

    };

});