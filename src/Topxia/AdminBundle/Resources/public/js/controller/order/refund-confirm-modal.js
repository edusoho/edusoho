define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $form = $('#order-refund-confirm-form');
        var $modal = $form.parents('.modal');

        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            autoFocus: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }

                $('#refund-confirm-btn').button('submiting').addClass('disabled');
                $.post($form.attr('action'), $form.serialize(), function(response){
                    $modal.modal('hide');
                    Notify.success('退款申请处理结果已提交');
                    window.location.reload();
                });
            }

        });

        validator.addItem({
            element: 'input[name=result]',
            required: true,
            errormessageRequired: '请选择审核结果'
        });

        validator.addItem({
            element: '#refund-note-field',
            rule: 'maxlength{max:200}'
        });

        $form.find('input[name=result]').on('change', function() {
            if ($(this).val() == 'pass') {
                $form.find('.amount-form-group').show();
                validator.addItem({
                    element: '#refund-amount-field',
                    required: true,
                    rule: 'number'
                });
            } else {
                $form.find('.amount-form-group').hide();
                validator.removeItem('#refund-amount-field');
            }
        });

    };

});