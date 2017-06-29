define(function(require, exports, module) {
    
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var $modal = $('#edit-form').parents('.modal');
        
        var validator = new Validator({
            element: '#edit-form',
            failSilently: true,
            autoSubmit: false,
            onFormValidated: function(error, results, $form){
                if (error) {
                    return false;
                }

                $('#create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success(Translator.trans('操作成功'));
                    window.location.reload();
                }).error(function(){
                    Notify.danger(Translator.trans('操作失败'));
                });

            }
        });

        validator.addItem({
            element: '[name="amount"]',
            required: true,
            rule: 'positive_integer' 
        });

    };

    
});