define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var validator = new Validator({
            element: '#bind-exist-form'
        });

        validator.addItem({
            element: '[name="user_bind_exist[email]"]',
            required: true,
            rule: 'email'
        });

        validator.addItem({
            element: '[name="user_bind_exist[password]"]',
            required: true
        });

    };

});