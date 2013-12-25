define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');

    exports.run = function() {
        var validator = new Validator({
            element: '#course-buy-form',
            autoSubmit: false
        });
        if ($('#course-buy-form').find('input[name="mobile"]')){
            validator.addItem({
                element: '[name="mobile"]',
                required: true
            });
        }

    };

});