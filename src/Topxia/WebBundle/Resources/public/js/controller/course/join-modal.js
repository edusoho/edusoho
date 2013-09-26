define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {

        var validator = new Validator({
            element: '#course-join-form'
        });

        validator.addItem({
            element: '[name="form[truename]"]',
            required: true,
            rule: 'chinese minlength{min:2} maxlength{max:5}'
        });

        validator.addItem({
            element: '[name="form[email]"]',
            required: true,
            rule: 'email'
        });

        validator.addItem({
            element: '[name="form[mobile]"]',
            required: true,
            rule: 'mobile'
        });

        validator.addItem({
            element: '[name="form[company]"]',
            required: true
        });


        validator.addItem({
            element: '[name="form[job]"]',
            required: true
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return ;
            }

	    	$.post($form.attr('action'), $form.serialize(), function(json) {
	    		window.location.reload();
	    	}, 'json');

        });

    };

});