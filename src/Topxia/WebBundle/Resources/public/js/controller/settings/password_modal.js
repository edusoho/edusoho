define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');

    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var payPasswordValidator = new Validator({
            element: '#settings-password-form',
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#password-save-btn').button('submiting').addClass('disabled');
            }
        });

        

        payPasswordValidator.addItem({
            element: '[name="form[newPassword]"]',
            required: true,
            rule: 'minlength{min:5} maxlength{max:20}'
        });

        payPasswordValidator.addItem({
            element: '[name="form[confirmPassword]"]',
            required: true,
            rule: 'confirmation{target:#form_newPassword}'
        });

        $('.js-submit-form').unbind('click');
        var modal = $('#modal');
        $('.js-submit-form').click(function(){
            payPasswordValidator.execute(function(error, results, element) {
                if (error) {
                    return;
                }
                var data = $("#settings-password-form").serialize();
                var targetUrl = $("#settings-password-form").attr("action");
                $.post(targetUrl, data, function(html) {
                    modal.html(html);
                });
            });
        });
    };

});