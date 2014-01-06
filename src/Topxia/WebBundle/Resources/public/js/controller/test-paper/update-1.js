define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    
    exports.run = function() {

        var validator = new Validator({
            element: '#test-update-form',
        });

        validator.addItem({
            element: '#test-name-field',
            required: true,
        });

        validator.addItem({
            element: '#test-description-field',
            required: true,
            rule: 'maxlength{max:500}',
        });

        validator.addItem({
            element: '#test-limitedTime-field',
            required: true,
            rule: 'integer'
        });
           
    };

});