define(function(require, exports, module) {

	exports.run = function() {
		$("#article-table").on('click', '[data-role=trash-item]', function(){
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});

		$("#article-table").on('click', '[data-role=publish-item]', function(){
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});	

		$("#article-table").on('click', '[data-role=delete-item]', function(){
			if (!confirm('真的要永久删除该内容吗？')) {
				return ;
			}

			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});
		$(".featured-label").on('click', function(){
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});		

		$(".promoted-label").on('click', function(){
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});		

		$(".sticky-label").on('click', function(){
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});
		var $container = $('#aticle-table-container');
		var $table = $('#article-table');
		 require('../../util/batch-select')($container);
		 require('../../util/batch-delete')($container);
		 require('../../util/item-delete')($container);
	};

});