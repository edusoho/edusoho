define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var validator = new Validator({
                element: '#payment-form'
            });
        
        $('[name=alipay_enabled]').change(function(e) {
            var radio = e.target.value;

            if (radio == '1') {
                validator.addItem({
                    element: '[name="alipay_secret"]',
                    required: true,
                    errormessageRequired: '请输入KEY'
                });
                validator.addItem({
                    element: '[name=alipay_key]',
                    required: true,
                    errormessageRequired: '请输入PID'
                })
            } else {
                validator.removeItem('[name="alipay_secret"]');
                validator.removeItem('[name="alipay_key"]');
            }
        });

        $('input[name="alipay_enabled"]:checked').change();

    };

});