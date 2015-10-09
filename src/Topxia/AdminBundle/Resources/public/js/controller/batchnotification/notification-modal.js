define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {
        var $modal = $('#batchnotification-create-form').parents('.modal');

        $form = $('#batchnotification-create-form');

        var validator = new Validator({
            element: '#batchnotification-create-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#batchnotification-create-btn').button('submiting').addClass('disabled');
                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    window.location.reload();

                }).error(function(){
                    Notify.danger('操作失败');
                });
            }
        });
        validator.addItem({
            element: '[name=content]',
            required: true,
            rule: 'minlength{min:2}'
        });

    };

});