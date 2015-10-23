define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');

	exports.run = function(options) {
		var $table = $('#course-recommend-table');

		$table.on('click', '.cancel-recommend-course', function() {
			if (!confirm('真的要取消该课程推荐吗？')) {
				return ;
			}

			var $tr = $(this).parents('tr');
			$.post($(this).data('url'), function(){
				Notify.success('课程推荐已取消！');
				$tr.remove();
			});

		});

	};

});
