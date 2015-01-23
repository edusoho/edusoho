define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');

	exports.run = function(options) {
		var $table = $('#course-table');

		$table.on('click', '.cancel-recommend-course', function(){
			$.post($(this).data('url'), function(html){
				var $tr = $(html);
				$table.find('#' + $tr.attr('id')).replaceWith(html);
				Notify.success('课程推荐已取消！');
			});
		});

		$table.on('click', '.close-course', function(){
			var user_name = $(this).data('user') ;
			if (!confirm('您确认要关闭此课程吗？课程关闭后，仍然还在有效期内的'+user_name+'，将可以继续学习。')) return false;
			$.post($(this).data('url'), function(html){
				var $tr = $(html);
				$table.find('#' + $tr.attr('id')).replaceWith(html);
				Notify.success('课程关闭成功！');
			});
		});

		$table.on('click', '.publish-course', function(){
			if (!confirm('您确认要发布此课程吗？')) return false;
			$.post($(this).data('url'), function(html){
				var $tr = $(html);
				$table.find('#' + $tr.attr('id')).replaceWith(html);
				Notify.success('课程发布成功！');
			});
		});

		$table.on('click', '.delete-course', function() {
			var chapter_name = $(this).data('chapter') ;
			var part_name = $(this).data('part') ;
			var user_name = $(this).data('user') ;
			if (!confirm('删除课程，将删除课程的'+chapter_name+''+part_name+'、课时、'+user_name+'信息。真的要删除该课程吗？')) {
				return ;
			}

			var $tr = $(this).parents('tr');
			$.post($(this).data('url'), function(){
				$tr.remove();
			});

		});

		$table.find('.copy-course[data-type="live"]').tooltip();

		$table.on('click', '.copy-course[data-type="live"]', function(e) {
			e.stopPropagation();
		});



	};

});
