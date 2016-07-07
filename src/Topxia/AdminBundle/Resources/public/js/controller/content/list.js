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
			if (!confirm(Translator.trans('真的要永久删除该内容吗？'))) {
				return ;
			}
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});

	};

});