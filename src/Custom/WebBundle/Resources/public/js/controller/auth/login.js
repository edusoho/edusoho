define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require("placeholder")
    require("jquery.bootstrap-datetimepicker");
    exports.run = function() {

       
        var validator = new Validator({
            element: '#login-form'
        });

        validator.addItem({
            element: '[name="_username"]',
            required: true,
            display: '账号'
        });

        validator.addItem({
            element: '[name="_password"]',
            required: true,
            display: '密码'
        });
        $('.receive-modal').click();

    };

});