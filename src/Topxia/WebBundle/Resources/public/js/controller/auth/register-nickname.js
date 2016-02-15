define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require("jquery.bootstrap-datetimepicker");
    var SmsSender = require('../widget/sms-sender');

    exports.run = function() {

        var $form = $('#register-nickname-form');
        var validator = new Validator({
            element: $form,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#register-nickname-btn').button('submiting').addClass('disabled');
            },
            failSilently: true
        });

        $(".nickname_ingnore").click(function (){
            var $reg_nickname = $("#register_nickname");
            $reg_nickname.val($reg_nickname.data('randmo'));
            $("#register-nickname-form").submit();
        })

        validator.addItem({
            element: '[name="nickname"]',
            required: true,
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:18} nickname_remote'
        });
    };

});