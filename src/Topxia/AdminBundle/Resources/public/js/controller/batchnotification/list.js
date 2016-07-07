define(function(require, exports, module) {

	exports.run = function() {
		$("#batchnotification-table").on('click', '[data-role=publish-item]', function(){
			if (!confirm(Translator.trans('发送后不可修改，确认发送？'))) {
				return ;
			}
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});	

		$("#batchnotification-table").on('click', '[data-role=delete-item]', function(){
			if (!confirm(Translator.trans('真的要删除该内容吗？'))) {
				return ;
			}
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});
	};
});