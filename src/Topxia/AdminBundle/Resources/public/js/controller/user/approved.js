define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');

	exports.run = function() {

		var $table = $('#user-table');

		$table.on('click', '.cancel-approval', function(){
            if (!confirm('确定要撤销这条认证成功的实名认证吗？')) {  return ; }

            $.post($(this).data('url'),function(response){
               window.location.reload();
            }).error(function(){
                window.location.reload();
            });

		});

	};

});