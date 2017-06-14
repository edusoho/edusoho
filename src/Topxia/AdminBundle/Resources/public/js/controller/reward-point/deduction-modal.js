define(function (require, exports, module) {
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    exports.run = function () {
        var $modal = $('#reward-point-deduction-form').parents('.modal');
        var validator = new Validator({
            element: '#reward-point-deduction-form',
            autoSubmit: false,
            onFormValidated: function (error, results, $form) {
                if (error) {
                    return false;
                }

                $('#reward-point-deduction-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function (html) {
                    $modal.modal('hide');
                    Notify.success(Translator.trans('扣减成功'));
                    window.location.reload();
                }).error(function () {
                    Notify.danger(Translator.trans('扣减失败'));
                });

            }
        });

        var rules = [['judge_account',function(options){
            var account = options.element.val();
            if((Number(account) === parseInt(account)) && account >= 0){
                var limit = Number($('.js-limit-account').html());
                return !!(account <= limit);
            }else{
                return false;
            }
            return false;
        },'输入小于或等于余额的非负整数']];
        $.each(rules ,function (i,rule) {
            Validator.addRule.apply(validator ,rule);
        });

        validator.addItem({
            element: '[name="note"]',
            rule: 'chinese_alphanumeric  byte_minlength{min:0} byte_maxlength{max:30}'
        });

        validator.addItem({
            element: '[name="amount"]',
            required: true,
            rule: 'judge_account'
        });

    };

});
