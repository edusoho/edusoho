define(function(require, exports, module) {

	exports.run = function() {
		$("#content-table").on('click', '[data-role=trash-item]', function(){
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});

		$("#content-table").on('click', '[data-role=publish-item]', function(){
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});

		$("#content-table").on('click', '[data-role=delete-item]', function(){
			if (!confirm(Translator.trans('admin.content.delete_hint'))) {
				return ;
			}
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});

	};

});