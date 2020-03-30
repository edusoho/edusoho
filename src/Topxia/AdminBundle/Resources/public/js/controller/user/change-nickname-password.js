define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var $form = $("#change-password-form");

        var validator = new Validator({
            element: '#change-password-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }
                $('#change-password-btn').button('submiting').addClass('disabled');
                $.post($form.attr('action'), $form.serialize(), function(html){
        
                    var $modal = $('#modal');

                    $.post($form.attr('action'), $form.serialize(), function(html) {
                        $modal.modal('hide');
                        Notify.success(Translator.trans('admin_v2.user.nickname_password_modify_success_hint'));
                        window.location.reload();
                    }).error(function(){
                        Notify.danger(Translator.trans('admin_v2.user.nickname_password_modify_error_hint'));
                    });
                });
            }
        });

        Validator.addRule("spaceNoSupport", function(options) {
            var value = $(options.element).val();
            return value.indexOf(' ') < 0;
        }, Translator.trans('validate.have_spaces'));

        validator.addItem({
            element: '[name="newPassword"]',
            required: true,
            rule: 'minlength{min:5} maxlength{max:20} spaceNoSupport'
        });

        validator.addItem({
            element: '[name="confirmPassword"]',
            required: true,
            rule: 'confirmation{target:#newPassword}'
        });

      validator.addItem({
        element: '[name="nickname"]',
        required: true,
        rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:18} remote'
      });

    };

});