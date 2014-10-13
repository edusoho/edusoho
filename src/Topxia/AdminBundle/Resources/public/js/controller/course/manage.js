define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	var ClassChooser = require('../class/class-chooser');
	
	exports.run = function(options) {

		var $table = $('#course-table');
		var $form = $('#message-search-form');
		$table.on('click', '.cancel-recommend-course', function(){
			$.post($(this).data('url'), function(html){
				var $tr = $(html);
				$table.find('#' + $tr.attr('id')).replaceWith(html);
				Notify.success('课程推荐已取消！');
			});
		});

		$table.on('click', '.close-course', function(){
			if (!confirm('您确认要关闭此课程吗？课程关闭后，仍然还在有效期内的学生，将可以继续学习。')) return false;
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
			if (!confirm('删除课程，将删除课程的章节、课时、学生信息。真的要删除该课程吗？')) {
				return ;
			}

			var $tr = $(this).parents('tr');
			$.post($(this).data('url'), function(){
				$tr.remove();
			});

		});

		//调用
        var classChooser = new ClassChooser({
            element:'#class_name',
            modalTarget:$('#modal'),
            url:$form.find('#class_name').data().url
        });
        
        classChooser.on('choosed',function(id,name){
            $form.find('#class_id').val(id);
            $form.find('#class_name').val(name);
        });

	};

});
