define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');

    exports.run = function() {
        var validator = new Validator({
            element: '#course-create-form',
            triggerType: 'change',
            onFormValidated: function(error){
                if (error) {
                    $('#course-create-btn').button('reset').removeClass('disabled');
                }
            }
        });

        validator.addItem({
            element: '[name="course[title]"]',
            required: true
        });

        $('#course-create-btn').on('click', function(){
            $(this).button('submiting').addClass('disabled');
        });
        
    };

});