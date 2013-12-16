define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var validator = new Validator({
            element: '#nickname-form'
        });

        validator.addItem({
            element: '[name=nickname]',
            required: true,
            rule : 'chinese_alphanumeric byte_minlength{min:4} byte_maxlength{max:14} remote',
        });


    };

});