define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require("jquery.bootstrap-datetimepicker");
    
    exports.run = function() {
        var validator = new Validator({
            element: '#login-form'
        });

        validator.addItem({
            element: '[name="_username"]',
            required: true
        });

        validator.addItem({
            element: '[name="_password"]',
            required: true
        });

    };

});