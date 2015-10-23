define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var $form = $('#customer-service-setting');

        var validator = new Validator({
            element: $form,
            autoSubmit: true
        });

        validator.addItem({
            element: '[name="customer_of_qq"]',
            required: true,
            rule: 'qq'
        });

        validator.addItem({
            element: '[name="customer_of_mail"]',
            required: true,
            rule: 'email'
        });

         validator.addItem({
            element: '[name="customer_of_phone"]',
            required: true,
            rule: 'phone'
        });

    };

});