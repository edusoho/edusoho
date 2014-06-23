define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');

	exports.run = function() {

		require('./header').run();

		$('#homework-student-list').on('click', '.homework-urge-btn', function(e) {
            if (!confirm('是否要发送催缴私信？')) {
                return ;
            }
            var $btn = $(e.currentTarget);
            $.post($(this).data('url'), function(response) {
                Notify.success('私信已发送');
            }, 'json');
        });
	};

});