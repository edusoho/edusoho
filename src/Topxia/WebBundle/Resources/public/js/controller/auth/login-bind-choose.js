define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $form = $('#set-bind-new-form');

        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {

                if (error) {
                    return false;
                }
                if(!$('#user_terms').find('input[type=checkbox]').attr('checked')) {
                 Notify.danger('勾选同意此服务协议，才能继续注册！');
                 return ;
                }
                $form.find('[type=submit]').button('loading');
                $("#bind-new-form-error").hide();

                $.post($form.attr('action'), $form.serialize(), function(response) {
                    if (!response.success) {
                        $('#bind-new-form-error').html(response.message).show();
                        return ;
                    }
                    Notify.success('登录成功，正在跳转至首页！');
                    window.location.href = response._target_path;

                }, 'json').fail(function() {
                    Notify.danger('登录失败，请重新登录后再试！');
                }).always(function() {
                   $form.find('button[type=submit]').button('reset');
                });
            }
        });

         $('#user_terms input[type=checkbox]').on('click', function() {
             if($(this).attr('checked')) {
                 $(this).attr('checked',false);
             } else {
                 $(this).attr('checked',true);
             };
         });
         if($("#set_bind_email").val() == ''){
            validator.addItem({
                element: '#set_bind_email',
                required: true,
                rule: 'email email_remote'
            });
        }

        validator.addItem({
            element: '#set-bind-nickname-field',
            required: true,
            rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:18} remote'
        });

    };

});