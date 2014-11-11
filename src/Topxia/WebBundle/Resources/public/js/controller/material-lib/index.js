define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');
	    
	require('jquery.select2-css');
	require('jquery.select2');
	
	exports.run = function() {

		var $panel = $('#material-lib-items-panel');
		require('../../util/batch-select')($panel);
		require('../../util/batch-delete')($panel);

//		$("#panel-upload-file .btn-link").tooltip();
		$("#material-lib-items-panel .btn-link").tooltip();

		var $list = $("#material-item-list");

		$list.on('click', '.delete-material-btn', function(e) {
			if (!confirm('您真的要删除该文件吗？')) {
				return;
			}
			var $btn = $(e.currentTarget);
			$.post($(this).data('url'), function(response) {
				$btn.parents('.item-material').remove();
				Notify.success('文件已删除！');
			}, 'json');
		});

		$('.tip').tooltip();

		$("#modal").modal({
			backdrop : 'static',
			keyboard : false,
			show : false
		});

		$("#modal").on("hidden.bs.modal", function() {
			window.location.reload();
		})

		$("a", "#panel-upload-file").on(
				'click',
				function() {
					var url = "";
					if ($(this).data("storage") != 'cloud'
							|| typeof (FileReader) == "undefined"
							|| typeof (XMLHttpRequest) == "undefined") {
						url = $(this).data("normalUrl");
					} else {
						url = $(this).data("html5Url");
					}
					$("#modal").html('');
					$("#modal").modal('show');

					$.get(url, function(responseHtml) {
						$("#modal").html(responseHtml);
					});
				});

	}

});