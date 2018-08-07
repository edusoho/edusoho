define(function(require, exports, module) {

	require("jquery.bootstrap-datetimepicker");
	require("$");

	exports.run = function() {
		$("#startDateTime, #endDateTime").datetimepicker({
			autoclose: true
		});

		$("#log-table").on('click', '.show-data', function() {
			$(this).hide().parent().find('.hide-data').show().end().find('.data').show();
		});

		$("#log-table").on('click', '.hide-data', function() {
			$(this).hide().parent().find('.show-data').show().end().find('.data').hide();
		});


		$("#log-module").change(function() {
			var url = $('#log-action').data('url');
			$.get(url, {
				module: this.value
			}, function(html) {
				$('#log-action').html(html);
			});
		});

		$('[data-toggle="switch"]').on('click', function() {
			window.location.href = $(this).data('url');
		})
	};

});
