define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');

    exports.run = function() {
        var validator = new Validator({
            element: '#course-create-form',
            triggerType: 'change'
        });

        validator.addItem({
            element: '[name="course[title]"]',
            required: true
        });

    };

});