define(function(require, exports, module) {
    exports.run = function() {
    	$('.schedule-list').on('click','li',function(){
    		$.get($(this).data('url'),function(html){
    			$('.schedule-list').html('');
    			$('.schedule-list').append(html);
    		});
    	});
    }
});
