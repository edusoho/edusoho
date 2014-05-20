define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $modal = $('#member-create-form').parents('.modal');
        var $table = $('#course-student-list');

        var validator = new Validator({
            element: '#member-create-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }                
                $.post($form.attr('action'), $form.serialize(), function(html) {                   
                    $modal.modal('hide');
                    Notify.success('添加学员操作成功!');


                    window.location.reload();
                }).error(function(){
                    Notify.danger('添加学员操作失败!');
                });

            }
        });

        validator.addItem({
            element: '#member-num',
            required: true,
            rule: 'integer'
        });

        validator.addItem({
            element: '#buy-price',
            rule: 'currency'
        });

    };

});