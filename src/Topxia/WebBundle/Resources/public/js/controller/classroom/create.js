define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');

    exports.run = function() {
        var validator = new Validator({
            element: '#classroom-create-form',
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#classroom-create-btn').button('submiting').addClass('disabled');
            }
        });

        validator.addItem({
            element: '[name="title"]',
            required: true
        });
        
    };

});