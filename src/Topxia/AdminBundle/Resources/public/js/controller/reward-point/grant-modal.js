define(function (require, exports, module) {
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    exports.run = function () {
        var $modal = $('#reward-point-grant-form').parents('.modal');
        var validator = new Validator({
            element: '#reward-point-grant-form',
            autoSubmit: false,
            onFormValidated: function (error, results, $form) {
                if (error) {
                    return false;
                }

                $('#reward-point-grant-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function (html) {
                    $modal.modal('hide');
                    Notify.success(Translator.trans('发放成功'));
                    window.location.reload();
                }).error(function () {
                    Notify.danger(Translator.trans('发放失败'));
                });

            }
        });

        validator.addItem({
            element: '[name="note"]',
            rule: 'chinese_alphanumeric  byte_minlength{min:0} byte_maxlength{max:30}'
        });

        validator.addItem({
            element: '[name="amount"]',
            required: true,
            rule: 'integer min{min: 0} max{max: 100000}',
        });

    };

});