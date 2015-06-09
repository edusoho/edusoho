define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var validator = new Validator({
            element: '#article-form',
            failSilently: true,
            triggerType: 'change'
        });

        validator.addItem({
            element: '[name=pageNums]',
            required: true,
            rule: 'positive_integer'
        });

    };

});