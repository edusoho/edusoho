define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.raty');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

      
        var validator = new Validator({
            element: '#qustion-form',
            autoSubmit: false
        });

      
        validator.addItem({
            element: '[name="qustion[title]"]',
            required: true
        });

        validator.addItem({
            element: '[name="qustion[content]"]',
            required: true
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }

            $.post($form.attr('action'), $form.serialize(), function(json) {
                window.location.reload();
            }, 'json');

        });


    };

});