define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    var CreateBase = require('./util/create-base');
    
    exports.run = function() {

        var validator = new Validator({
            element: '#test-update-form',
        });

        CreateBase.initValidator(validator);
    };

});