define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $form = $('#navigation-form');
        var $modal = $form.parents('.modal');
        var $table = $('#navigation-table');

        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }
                $('#navigation-save-btn').button('submiting').addClass('disabled');
                $.post($form.attr('action'), $form.serialize(), function(html){
                    $modal.modal('hide');
                    Notify.success(Translator.trans('admin.navigation.save_success_hint'));
                    window.location.reload();
                });

            }

        });

        validator.addItem({
            element: '[name="name"]',
            required: true
        });

    };

});