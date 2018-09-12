define(function(require, exports, module) {

	exports.run = function() {
		$("#batchnotification-table").on('click', '[data-role=publish-item]', function(){
			if (!confirm(Translator.trans('admin.batch_notification.send_hint'))) {
				return ;
			}
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});	

		$("#batchnotification-table").on('click', '[data-role=delete-item]', function(){
			if (!confirm(Translator.trans('admin.batch_notification.delete_hint'))) {
				return ;
			}
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});
	};
});