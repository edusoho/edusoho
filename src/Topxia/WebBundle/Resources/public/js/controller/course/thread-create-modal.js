define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');

    exports.run = function() {
        // 表单校验
        var validator = new Validator({
            element: '#course-thread-create-form'
        });

        validator.addItem({
            element: '[name="form[title]"]',
            required: true
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return ;
            }

            $.post($form.attr('action'), $form.serialize(), function(json) {
                window.location.reload();
            }, 'json');

        });

    };

});