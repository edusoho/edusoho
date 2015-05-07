define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require("common/jquery.placeholder.js")
    require("jquery.bootstrap-datetimepicker");
    exports.run = function() {
        $('input, textarea').placeholder(); 
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