define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	exports.run = function() {

		$('body').on('click', 'button.delete-btn', function() {
			if (!confirm('确认要删除此编辑区吗？')) return false;
			var $btn = $(this);
			$.post($btn.data('url'), function(response) {
				if (response.status == 'ok') {
					$('#' + $btn.data('target')).remove();
					Notify.success('删除成功!');
				} else {
					alert('服务器错误!');
				}
			}, 'json');
		});
		
	};

});