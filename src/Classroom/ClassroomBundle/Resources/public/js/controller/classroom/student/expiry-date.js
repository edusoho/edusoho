define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    require('jquery.bootstrap-datetimepicker');

    exports.run = function() {

        var validator = new Validator({
            element: '#expiryday-set-form',
            autoSubmit: false,
            failSilently: true,
            triggerType: 'change',
            onFormValidated: function(error, results, $form){
                if (error) {
                    return false;
                }
                $('#student-save').button('loading').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(response) {
                    if (response == true) {
                        Notify.success(Translator.trans('修改学员成功'));
                    } else {
                        Notify.danger(Translator.trans('修改学员失败'));
                    }
                    window.location.reload();
                });
            }
        });

        validator.addItem({
            element: '[name=deadline]',
            required: true,
            display: '有效期'
        });

        $("#student_deadline").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });
        $("#student_deadline").datetimepicker('setStartDate', new Date);
    };
});
