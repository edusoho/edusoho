define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var validator = new Validator({
            element: '#setup-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                var $btn = this.$('[type=submit]').button('loading');
                
                $.post($form.attr('action'), $form.serialize(), function() {
                    Notify.success('设置帐号成功，正在跳转');
                    window.location.href = $btn.data('goto');
                }).error(function(){
                    $btn.button('reset');
                    Notify.danger('设置帐号失败，请重试');
                });
            }

        });

        validator.addItem({
            element: '#setup-email-field',
            required: true,
            rule: 'email email_remote'
        });

        validator.addItem({
            element: '#setup-nickname-field',
            required: true,
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:14} remote'
        });

    };

});