define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

    	var $form = $('#field-save-form');
        var validator = new Validator({
            element: $form,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#save-btn').button('submiting').addClass('disabled');
            }
		});
        

        validator.addItem({
            element: '[name="title"]',
            required: true,
            rule:'minlength{min:2} maxlength{max:20}'
        });

        validator.addItem({
            element: '[name="seq"]',
            required: true,
            rule:'positive_integer'
        });

    };

});