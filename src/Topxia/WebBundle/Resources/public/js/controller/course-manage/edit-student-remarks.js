define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $modal = $('#edit-student-remarks').parents('.modal');

        var validator = new Validator({
            element: '#edit-student-remarks',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                
                $.post($form.attr('action'), $form.serialize(), function(response) {
                    $modal.modal('hide');
                    Notify.success('编辑学员备注信息成功!');
                },'json').error(function(){
                    Notify.danger('编辑学员备注信息失败!');
                });
            }

        });

        validator.addItem({
            element: '[name="remarks"]',
            required: false,
            rule: 'maxlength{max:250}'
        });

    };

});