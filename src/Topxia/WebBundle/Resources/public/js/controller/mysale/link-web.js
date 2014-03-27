define(function(require, exports, module) {

    require('jquery');

  

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {


		    var validator = new Validator({
		        element: '#weblink-form',
		        autoSubmit: false
		    });

		  
		    validator.addItem({
		        element: '[name="oUrl"]',
		        required: true
		    });

		    validator.addItem({
		        element: '[name="linkName"]',
		        required: true
		    });

		    validator.on('formValidated', function(error, msg, $form) {
		        if (error) {
		            return;
		        }

		        $.post($form.attr('action'), $form.serialize(), function(json) {
		            window.location.reload();
		        }, 'json');

		    });
      

    };
});