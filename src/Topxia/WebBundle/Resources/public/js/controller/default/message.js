define(function(require, exports, module) {

    exports.run = function() {
    	var $message = $("#page-message-container");
    	var duration = $message.data('duration');
    	if (duration > 0) {
	        setTimeout(function() {
	        	var goto = $message.data('goto');
	            window.location.href= goto;
	        }, 2000);
    	}

    }

});