define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	    require('jquery.select2-css');
	    require('jquery.select2');
	    
	exports.run = function() {

		var $list = $("#share-history-table");

		$list.on('click', '.cancel-share-btn', function(e) {
			var $btn = $(e.currentTarget);
			$.post($(this).data('url'), {targetUserId: $(this).attr('targetUserId')}, function(response) {
				$btn.parents('.share-history-record').remove();
				Notify.success('已取消分享！');
				window.location.reload();
			}, 'json');
		});
		
		$("#modal").modal({
			backdrop : 'static',
			keyboard : false,
			show : false
		});

		$("#modal").on("hidden.bs.modal", function() {
			window.location.reload();
		})
	}

});