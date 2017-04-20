define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $modal = $('#student-create-form').parents('.modal');
        var $table = $('#course-student-list');

        var validator = new Validator({
            element: '#student-create-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                var $btn = $("#student-create-form-submit");
                $btn.button('submiting').addClass('disabled');
                
                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $table.find('tr.empty').remove();
                    $(html).prependTo($table.find('tbody'));
                    $modal.modal('hide');
                    var user_name = $('#student-create-form-submit').data('user') ;
                    Notify.success(Translator.trans('添加%username%操作成功!',{username:user_name}));
                    window.location.reload();
                }).error(function(){
                    var user_name = $('#student-create-form-submit').data('user') ;
                    Notify.danger(Translator.trans('添加%username%操作失败!',{username:user_name}));
                    $btn.button('reset').removeClass('disabled');
                });

            }
        });

        validator.addItem({
            element: '#student-nickname',
            required: true,
            rule: 'remote'
        });

        validator.addItem({
            element: '#student-remark',
            rule: 'maxlength{max:80}'
        });

        validator.addItem({
            element: '#buy-price',
            rule: 'currency'
        });

    };

});
