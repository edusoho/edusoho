define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);
    exports.run = function() {
        var $form = $('#topLink-form');
        var $modal = $form.parents('.modal');

        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $('#topLink-edit-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(){
                    Notify.success('更新成功！');
                    $modal.modal('hide');
                    window.location.reload();
                });

            }
        });

        validator.addItem({
            element: '[name="name"]',
            required: true
        });

        validator.addItem({
            element: '[name="seq"]',
            required: true
        });

        validator.addItem({
            element: '[name="url"]',
            required: true
        });

        

    };




});