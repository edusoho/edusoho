define(function(require, exports, module) {

	exports.run = function() {
		$('body').on('click', 'a.comment-delete', function() {
			var btn = $(this);
			if (!confirm('确定要删除评论吗?')) return ;
			$.post(btn.data('url'), function(response) {
				if (response.status == 'ok') {
					btn.parents('tr').remove();
					toastr.success('删除成功!');
				} else {
					toastr.error('删除失败:' + response.error.message);
				}
			}, 'json');
		});
	};

});