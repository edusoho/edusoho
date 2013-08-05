define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
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
                
                $.post($form.attr('action'), $form.serialize(), function(response){
                    if (response.status == 'ok') {
                        var $html = $(response.html);
                            $('#' + $html.attr('id')).replaceWith($html);
                            toastr.success('更新成功!');
                        $modal.modal('hide');
                    }
                }, 'json');
            }

        });

       validator.addItem({
            element: '[name="form[name]"]',
            required: true
        });

        validator.addItem({
            element: '[name="form[url]"]',
            required: true
        });

    };

});