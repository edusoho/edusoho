define(function(require, exports, module) {

    exports.run = function() {
        $('#notebook').on('click', '.notebook-note-collapsed', function(){
            $(this).removeClass('notebook-note-collapsed');
        });

        $('#notebook').on('click', '.notebook-note-collapse-bar', function(){
            $(this).parents('.notebook-note').addClass('notebook-note-collapsed');
        });

        $('#notebook').on('click', '.notebook-note-delete', function(){
            var $btn = $(this);
        	if (!confirm('真的要删除该笔记吗？')) {
        		return false;
        	}

        	$.post($btn.data('url'), function(){
	        	$btn.parents('.notebook-note').remove();
        	});

        });
    };

});