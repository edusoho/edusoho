define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	exports.run = function() {
		$('body').on('click', 'a.comment-delete', function() {
			var btn = $(this);
			if (!confirm(Translator.trans('admin.comment.delete_hint'))) return ;
			$.post(btn.data('url'), function(response) {
				if (response.status == 'ok') {
					btn.parents('tr').remove();
					Notify.success(Translator.trans('admin.comment.delete_success_hint'));
				} else {
					Notify.danger(Translator.trans('admin.comment.delete_fail_hint',{response:response.error.message}));
				}
			}, 'json');
		});
	};

});