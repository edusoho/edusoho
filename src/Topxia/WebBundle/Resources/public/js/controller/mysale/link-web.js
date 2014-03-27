define(function(require, exports, module) {

    require('jquery');

  

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {


		    var validator = new Validator({
		        element: '#weblink-form',
		        autoSubmit: true
		    });

		  
		    validator.addItem({
		        element: '[name="oUrl"]',
		        required: true,
		        rule: 'url'
		    });

		    validator.addItem({
		        element: '[name="linkName"]',
		        required: true,
		        rule: 'byte_minlength{min:1} byte_maxlength{max:100}'
		    });

      

    };
});