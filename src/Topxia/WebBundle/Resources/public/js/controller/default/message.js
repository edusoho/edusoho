define(function(require, exports, module) {

    exports.run = function() {
    	var $message = $("#page-message-container");
        var gotoUrl = $message.data('goto');
    	var duration = $message.data('duration');
    	if (duration > 0 && gotoUrl) {
	        setTimeout(function() {
	            window.location.href= gotoUrl;
	        }, 2000);
    	}

    }

});