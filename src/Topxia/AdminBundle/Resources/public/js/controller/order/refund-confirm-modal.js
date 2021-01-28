define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $form = $('#order-refund-confirm-form');
        var $modal = $form.parents('.modal');

        var validator = new Validator({
            element: $form,
            autoFocus: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $('#refund-confirm-btn').button('submiting').addClass('disabled');

                // $.post($form.attr('action'), $form.serialize(), function(response){
                //     $modal.modal('hide');
                //     Notify.success(Translator.trans('退款申请处理结果已提交'));
                //     window.location.reload();
                // });
            }
        });

        validator.addItem({
            element: 'input[name=result]',
            required: true,
            errormessageRequired: Translator.trans('admin.order.validate_old.result_input.message')
        });

        validator.addItem({
            element: '#refund-note-field',
            rule: 'maxlength{max:200}'
        });

        $form.find('input[name=result]').on('change', function() {
            if ($(this).val() == 'pass') {
                coinToRmb();
                $form.find('.amount-form-group').show();
                validator.addItem({
                    element: '#refund-coin-amount-field',
                    required: true,
                    rule: 'positive_currency min{min: 0} max{max: '+ $('#refund-coin-amount-field').data('maxCoinAmount') +'}'
                });

                validator.addItem({
                    element: '#refund-cash-amount-field',
                    required: true,
                    rule: 'positive_currency min{min: 0} max{max: '+ $('#refund-cash-amount-field').data('maxCashAmount') +'}'
                });
                $form.find('input[name=refund_coin_amount]').on('change', function() {
                    calculateAmount();
                    coinToRmb();
                });

                $form.find('input[name=refund_cash_amount]').on('change', function() {
                    calculateAmount();
                });

            } else {
                $form.find('.amount-form-group').hide();
                validator.removeItem('#refund-coin-amount-field');
                validator.removeItem('#refund-cash-amount-field');
            }
        });

        function calculateAmount() {
            var coinAmount = $('#refund-coin-amount-field').val();
            var cashAmount = $('#refund-cash-amount-field').val();
            var amount = (coinAmount*100 + cashAmount*100)/100;
            $('#amount-display').text(amount.toFixed(2));
        }

        function coinToRmb() {
            var coinDisplay = $('#coin-to-rmb');
            var coinAmount = $('#refund-coin-amount-field');
            var coinToRmb = (coinAmount.val()*100 / coinAmount.data('rate'))/100;
            coinDisplay.text(coinToRmb.toFixed(2));
        }

    };

});