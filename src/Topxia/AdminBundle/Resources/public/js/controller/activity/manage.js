define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');

	exports.run = function(options) {
		var $table = $('#activity-table');

		$table.on('click', '.close-course', function(){
			$.post($(this).data('url'), function(html){
				var $tr = $(html);
				$table.find('#' + $tr.attr('id')).replaceWith(html);
				Notify.success('活动关闭成功！');
			});
		});

		$table.on('click', '.publish-course', function(){
			$.post($(this).data('url'), function(html){
				var $tr = $(html);
				$table.find('#' + $tr.attr('id')).replaceWith(html);
				Notify.success('活动发布成功！');
			});
		});


		$table.on('click', '.recommend-activity', function(){
			$.post($(this).data('url'), function(html){
				var $tr = $(html);
				$table.find('#' + $tr.attr('id')).replaceWith(html);
				Notify.success('活动置顶成功！');
			});
		});

		$table.on('click', '.cancel-recommend-activity', function(){
			$.post($(this).data('url'), function(html){
				var $tr = $(html);
				$table.find('#' + $tr.attr('id')).replaceWith(html);
				Notify.success('活动取消置顶成功！');
			});
		});


		$table.on('click', '.open-registration', function(){
			$.post($(this).data('url'), function(html){
				var $tr = $(html);
				$table.find('#' + $tr.attr('id')).replaceWith(html);
				Notify.success('活动开放报名成功！');
			});
		});


		$table.on('click', '.close-registration', function(){
			$.post($(this).data('url'), function(html){
				var $tr = $(html);
				$table.find('#' + $tr.attr('id')).replaceWith(html);
				Notify.success('活动关闭报名成功！');
			});
		});
	

		$table.on('click', '.delete-course', function() {
			if (!confirm('删除活动，将删除活动的章节、课时、学员信息。真的要删除该活动吗？')) {
				return ;
			}

			var $tr = $(this).parents('tr');
			$.post($(this).data('url'), function(){
				$tr.remove();
			});

		});

	};

});
