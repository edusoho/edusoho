define(function(require, exports, module) {

    exports.run = function() {
    	$("#notebook-list").on('click', '.media', function(){
    		window.location.href = $(this).find('.notebook-go').attr('href');
    	});


    };

});