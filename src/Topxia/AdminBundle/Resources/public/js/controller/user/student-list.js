define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');

	exports.run = function() {

		var $table = $('#user-table');
		$table.on('click', '.remove-student', function() {
			var $trigger = $(this);
			if (!confirm('真的要' + $trigger.attr('title') + '吗？')) {
				return ;
			}
			var $tr = $(this).parents('tr');
			$.post($(this).data('url'), function(){
				Notify.success($trigger.attr('title') + '成功！');
				$tr.remove();
			}).error(function(){
                Notify.danger($trigger.attr('title') + '失败');
            });

		});

	};

});