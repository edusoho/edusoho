define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $modal = $('#user-create-form').parents('.modal');

        var validator = new Validator({
            element: '#user-create-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $('#user-create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success(Translator.trans('admin.user.create_new_user_success_hint'));
                    window.location.reload();
                }).error(function(){
                    Notify.danger(Translator.trans('admin.user.create_new_user_fail_hint'));
                });

            }
        });
        validator.addItem({
            element: '[name="emailOrMobile"]',
            required: true,
            rule: 'email_or_mobile email_or_mobile_remote'
        });

        validator.addItem({
            element: '[name="nickname"]',
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:18} remote'
        });

        validator.addItem({
            element: '[name="password"]',
            required: true,
            rule: 'minlength{min:5} maxlength{max:20}'
        });

        validator.addItem({
            element: '[name="confirmPassword"]',
            required: true,
            rule: 'confirmation{target:#password}'
        });
    };

});