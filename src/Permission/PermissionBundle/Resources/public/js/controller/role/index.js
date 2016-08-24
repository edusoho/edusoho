define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
    	$('.role-delete').click(function(){
    		var url = $(this).data('url');
    		$.post(url, function(){
    			document.location.reload();
    		});
    	})

    }
})