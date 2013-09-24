define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $modal = $('#student-create-form').parents('.modal');

        var validator = new Validator({
            element: '#student-create-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                
                $.post($form.attr('action'), $form.serialize(), function(response) {
                    $modal.modal('hide');
                    if(response){
                        window.location.reload();
                    }
                    Notify.success('添加学员操作成功!');
                },'json').error(function(){
                    Notify.danger('添加学员操作失败!');
                });

            }
        });

        validator.addItem({
            element: '#student-nickname',
            required: true,
            rule: 'chinese_alphanumeric remote'
        });

        validator.addItem({
            element: '#student-remark',
            rule: 'maxlength{max:80}'
        });

    };

});