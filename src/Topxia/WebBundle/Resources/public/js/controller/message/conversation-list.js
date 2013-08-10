define(function(require, exports, module) {

    exports.run = function() {
        $("#site-navbar").find('.message-badge-container .badge').remove();

    	$('.conversation-list').on('click', 'a', function(e){
    		e.stopPropagation();
    	});

    	$('.conversation-list').on('click', '.media', function(e) {
    		window.location.href = $(this).data('url');
    	});

    	$('.conversation-list').on('click', '.delete-conversation-btn', function(e){
    		if (!confirm('真的要删除该私信吗？')) {
    			return false;
    		}

    		var $item = $(this).parents('.media');

    		$.post($(this).data('url'), function(){
    			$item.remove();
    		});

    	});

    };

});