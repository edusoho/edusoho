define(function(require, exports, module) {

    exports.run = function() {
    	$('#notificationModal').click(function(){
			 $.post($('#notificationModal').data('url'), function(){
                });
		})
    }
});