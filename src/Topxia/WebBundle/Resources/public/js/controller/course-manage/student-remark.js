define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $modal = $('#student-remark-form').parents('.modal');

        var validator = new Validator({
            element: '#student-remark-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                
                $.post($form.attr('action'), $form.serialize(), function(html) {
                    var $html = $(html);
                    $('#'+$html.attr('id')).replaceWith($html);
                    $modal.modal('hide');
                    var user_name = $('#student-remark-form').data('user') ;
                    Notify.success(Translator.trans('备注%name%成功',{name:user_name}));
                }).error(function(){
                    var user_name = $('#student-remark-form').data('user') ;
                    Notify.danger(Translator.trans('备注%name%失败，请重试！',{name:user_name}));
                });
            }

        });

        validator.addItem({
            element: '#student-remark',
            required: false,
            rule: 'maxlength{max:80}',
            display: Translator.trans('备注')
        });

    };

});