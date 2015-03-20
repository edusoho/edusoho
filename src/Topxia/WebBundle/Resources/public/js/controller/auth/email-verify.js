define(function(require, exports, module) {

    exports.run = function() {

        $.post($('[name=verifyUrl]').val(), function(response) {
        	if (true == response){
	            setTimeout(function() {
	                window.location.href= $("#jump-btn").attr('href');
	            }, 2000);
	        }
        });

    }

});