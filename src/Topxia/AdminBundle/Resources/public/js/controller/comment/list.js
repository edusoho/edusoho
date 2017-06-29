define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	exports.run = function() {
		$('body').on('click', 'a.comment-delete', function() {
			var btn = $(this);
			if (!confirm(Translator.trans('确定要删除评论吗?'))) return ;
			$.post(btn.data('url'), function(response) {
				if (response.status == 'ok') {
					btn.parents('tr').remove();
					Notify.success(Translator.trans('删除成功!'));
				} else {
					Notify.danger(Translator.trans('删除失败:%response%',{response:response.error.message}));
				}
			}, 'json');
		});
	};

});