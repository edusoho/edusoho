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
			if (!confirm('真的要删除该课程吗？')) {
				$(this).attr('href',"javascript:;");
				$(this).removeAttr('data-toggle');
				return;
			}
			$(this).attr('href',"#modal");
			$(this).attr('data-toggle','modal');
			var $tr = $(this).parents('tr');
			$.post($(this).data('url'),function(data){
				if(data == 'Have sub courses'){
					Notify.danger('请先删除班级课程');
				}else if(data == 'not remove classroom course'){
					Notify.danger('当前课程未移除,请先移除班级课程');
				}else if(data == 'delete draft course'){
					$tr.remove();	
				}else{}
			});
		});

		$table.find('.copy-course[data-type="live"]').tooltip();

		$table.on('click', '.copy-course[data-type="live"]', function(e) {
			e.stopPropagation();
		});



	};

});
