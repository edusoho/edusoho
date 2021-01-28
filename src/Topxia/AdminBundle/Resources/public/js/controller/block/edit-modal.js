define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);
    
    exports.run = function() {
        var $form = $('#block-form');
        var $modal = $form.parents('.modal');
        var $table = $('#block-table');

        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }
                
                $.post($form.attr('action'), $form.serialize(), function(response){
                    if (response.status == 'ok') {
                        var $html = $(response.html);
                            $('#' + $html.attr('id')).replaceWith($html);
                            Notify.success(Translator.trans('更新成功!'));
                        $modal.modal('hide');
                    }
                }, 'json');
            }

        });

        validator.addItem({
            element: '[name="title"]',
            required: true,
            rule: 'maxlength{max:25}'
        });

        validator.addItem({
            element: '[name="code"]',
            required: true,
            rule: 'maxlength{max:25} alphabet_underline remote'
        });

    };

});