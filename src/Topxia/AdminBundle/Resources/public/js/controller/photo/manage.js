define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');

	exports.run = function(options) {
		var $table = $('#activity-table');


		$table.on('click', '.delete-course', function() {
			if (!confirm('删除专辑，将删除专辑的图信息。真的要删除该专辑吗？')) {
				return ;
			}

			var $tr = $(this).parents('tr');
			$.post($(this).data('url'), function(){
				$tr.remove();
			});

		});

	};

});
