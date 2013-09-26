define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var $modal = $("#message-create-form").parents('.modal');

        var validator = new Validator({
            element: '#message-create-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                
                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success('私信发送成功');
                }).error(function(){
                    $modal.modal('hide');
                    Notify.danger('私信发送失败，请重试！');
                });
            }

        });

        validator.addItem({
            element: '[name="message[receiver]"]',
            required: true,
            rule: 'chinese_alphanumeric remote'
        });

        validator.addItem({
            element: '[name="message[content]"]',
            required: true,
            rule: 'maxlength{max:500}'
        });

        $('#modal').modal('show');
    }

});