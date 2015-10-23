define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');

	exports.run = function() {

		var $table = $('#teacher-table');

		$table.on('click', '.promote-user', function(){
            $.post($(this).data('url'),function(response) {
                window.location.reload();
            });
		});

        $table.on('click', '.cancel-promote-user', function(){
            $.post($(this).data('url'),function(response) {
                window.location.reload();
            });
        });

	};

});