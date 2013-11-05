define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');

    exports.run = function() {
        var validator = new Validator({
            element: '#course-create-form',
            triggerType: 'change'
        });

        validator.addItem({
            element: '[name="activity[title]"]',
            required: true
        });

         validator.addItem({
            element: '[name="activity[actType]"]',
            required: true
        });

    };

});