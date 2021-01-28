define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	require('../widget/category-select').run('course');
	
	exports.run = function(options) {
		var $table = $('#course-recommend-table');

		$table.on('click', '.cancel-recommend-course', function() {
			if (!confirm(Translator.trans('admin.course.cancel_recommend_hint'))) {
				return;
			}

			var $tr = $(this).parents('tr');
			$.post($(this).data('url'), function() {
				Notify.success(Translator.trans('admin.course.cancel_recommend_success_hint'));
				$tr.remove();
			});

		});

	};

});