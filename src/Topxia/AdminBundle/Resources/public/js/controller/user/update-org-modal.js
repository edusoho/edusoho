define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    exports.run = function() {
        var validator = new Validator({
            element: '#user-edit-form',
            autoSubmit: false,
            failSilently: true,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#edit-user-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function() {
                    Notify.success(Translator.trans('修改用户所属机构成功'));
                    window.location.reload();
                }).error(function() {
                    Notify.danger(Translator.trans('操作失败'));
                });
            }
        });
    }
});