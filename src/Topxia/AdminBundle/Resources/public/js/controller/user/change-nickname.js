define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var $form = $("#change-nickname-form");

        var validator = new Validator({
            element: '#change-nickname-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }
                $('#change-nickname-btn').button('submiting').addClass('disabled');
                $.post($form.attr('action'), $form.serialize(), function(html){
        
                    var $modal = $('#modal');

                    $.post($form.attr('action'), $form.serialize(), function(html) {
                        $modal.modal('hide');
                        Notify.success(Translator.trans('admin_v2.user.nickname_modify_success_hint'));
                        window.location.reload();
                    }).error(function(){
                        Notify.danger(Translator.trans('admin_v2.user.nickname_modify_error_hint'));
                    });
                });
            }
        });

      validator.addItem({
        element: '[name="nickname"]',
        required: true,
        rule: 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:18} remote'
      });

    };

});