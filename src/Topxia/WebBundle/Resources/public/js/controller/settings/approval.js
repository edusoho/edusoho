define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var validator = new Validator({
            element: '#approval-form'
        });

        validator.addItem({
            element: '[name="idcard"]',
            required: true,
            rule : 'idcard'
        });

        validator.addItem({
            element: '[name="truename"]',
            required: true,
            rule: 'chinese byte_minlength{min:4} byte_maxlength{max:50}'
        });

        validator.addItem({
            element: '[name="faceImg"]',
            required: true
        });

        validator.addItem({
            element: '[name="backImg"]',
            required: true
        });

    };

});