define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    exports.run = function() {
    	var $form = $("#courseSync-form");
    	var $modal = $('#modal');
    	var validator = new Validator({
        element: $form,
        autoSubmit: false,
        autoFocus: false,
        onFormValidated: function(error, results, $form) {
	      	if (error) {
	            return ;
	       	}
	        $.post($form.attr('action'), $form.serialize(), function(html){
	            window.location.href = html;
                $modal.modal('hide');
	        });
        }
     });
    };

});