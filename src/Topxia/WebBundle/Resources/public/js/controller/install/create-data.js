define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');

    exports.run = function() {
        var validator = new Validator({
            element: '#create-data-form'
        });

         validator.addItem({
            element: '[name="dbhost"]',
            required: true,
            rule: 'text'
        });

        validator.addItem({
            element: '[name="dbuser"]',
            required: true,
            rule: 'text'
        });

        validator.addItem({
            element: '[name="dbname"]',
            required: true,
            rule: 'text'
        });

        validator.addItem({
            element: '[name="super_manager"]',
            required: true,
            rule: 'text minlength{min:5} maxlength{max:20}'
        });

        validator.addItem({
            element: '[name="super_manager_pd"]',
            required: true,
            rule: 'password minlength{min:5} maxlength{max:20}'
        });

        validator.addItem({
            element: '#super_manager_ckpd',
            required: true,
            rule: 'confirmation{target:#super_manager_pd}'
        });

         validator.addItem({
            element: '[name="super_manager_email"]',
            required: true,
            rule: 'email'
        });

    };

});