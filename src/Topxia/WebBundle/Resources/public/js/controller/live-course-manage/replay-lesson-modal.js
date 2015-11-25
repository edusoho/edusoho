define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $modal = $('#replay-manage-form').parents('.modal');
        

        var validator = new Validator({
            element: '#replay-manage-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                // $('#replay-manage-form').button('submiting').addClass('disabled');
                console.log($form.serialize());
                $.post($form.attr('action'), $form.serialize(), function(html) {
                    // $modal.modal('hide');
                    Notify.success('保存成功');
                    // window.location.reload();
                }).error(function(){
                    Notify.danger('保存失败');
                });
                
            }
        });
    };

});