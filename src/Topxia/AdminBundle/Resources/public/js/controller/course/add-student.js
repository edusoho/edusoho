define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $modal = $('#add-student-form').parents('.modal');

        var validator = new Validator({
            element: '#add-student-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                
                $.post($form.attr('action'), $form.serialize(), function(response) {
                    $modal.modal('hide');
                    Notify.success(Translator.trans('添加学员操作成功!'));
                },'json').error(function(){
                    Notify.danger(Translator.trans('添加学员操作失败!'));
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