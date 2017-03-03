define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $form = $('#category-form');
        var $modal = $form.parents('.modal');

        var validator = new Validator({
                element: $form,
                autoSubmit: false,
                onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $.post($form.attr('action'), $form.serialize(), function(html){
                    $modal.modal('hide');
                    location.reload();
                    if($form.data('mode') == 'add') {
                        Notify.success(Translator.trans('添加栏目成功！'));
                    } else {
                        Notify.success(Translator.trans('更新栏目成功！'));
                    }
                }).fail(function() {
                    if($form.data('mode') == 'add') {
                        Notify.danger(Translator.trans('新增栏目成功失败，请重试！'));
                    }else{
                        Notify.danger(Translator.trans('更新栏目成功失败，请重试！'));
                    }
                });

            }
        });

        validator.addItem({
            element: '#category-name-field',
            required: true,
            rule: 'minlength{min:1} maxlength{max:10} remote'
        });
        $('.radios').on('click', "input[name=type]", function() {
            var selectedValue = $(this).attr('value');
            var url = $(this).data(url);

            $.get(url['url'], function(html){
                $('.category-ajax').html(html);
            });
            if (selectedValue == 'classroom' || selectedValue == 'course') {
                $('.order-form').removeClass('hide');
            }
            if (selectedValue == 'live') {
                $('.order-form').addClass('hide');
            }
        });
    };

});