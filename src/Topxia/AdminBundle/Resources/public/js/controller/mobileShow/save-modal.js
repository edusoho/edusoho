define(function(require, exports, module) {
exports.run = function() {
    $('.delete-btn').on('click', function() {
        if (!confirm('确定要删除该分类展示吗?')) {
            return ;
        }

        $.post($(this).data('url'), function() {
        	location.reload();
        });

    });

    $('.edit-btn').on('click', function() {
    	$.post($(this).data('url'), function() {

        });
    });
}
});