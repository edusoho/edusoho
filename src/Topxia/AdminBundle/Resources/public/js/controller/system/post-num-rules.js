define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');


    exports.run = function(){
    	var $form = $('#post-num-rules-form');
        var validator = new Validator({
            element: $form,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
            }
		});

		validator.addItem({
            element: '[name="setting[rules][thread][fiveMuniteRule][postNum]"]',
            required: true,
            rule:'min{min:0} integer'
        });

        validator.addItem({
            element: '[name="setting[rules][threadLoginedUser][fiveMuniteRule][postNum]"]',
            required: true,
            rule:'min{min:0} integer'
        });



    }
});