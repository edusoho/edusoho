define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	exports.run = function() {
		$('body').on('click', 'button.delete-btn', function() {
			if (!confirm(Translator.trans('确认要删除标签吗？'))) return false;
			var $btn = $(this);
			$.post($btn.data('url'), function(response) {
				if (response.status == 'ok') {
					$('#' + $btn.data('target')).remove();
					Notify.success(Translator.trans('删除成功!'));
				} else {
					alert(Translator.trans('服务器错误!'));
				}
			}, 'json');
		});
	};

});