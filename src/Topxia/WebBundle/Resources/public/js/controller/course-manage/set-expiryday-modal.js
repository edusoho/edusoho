define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $modal = $('#expiryday-set-form').parents('.modal');
        var $table = $('#course-student-list');

        var validator = new Validator({
            element: '#expiryday-set-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $.post($form.attr('action'), $form.serialize(), function() {
                    Notify.success('增加学员有效期操作成功!');
                    $modal.modal('hide');
                    window.location.reload();
                }).error(function(){
                    Notify.danger('增加学员有效期操作失败!');
                });

            }
        });

        validator.addItem({
            element: '#set-more-expiryday',
            rule: 'integer'
        });

    };

});