define(function(require, exports, module) {

	require("jquery.bootstrap-datetimepicker");
	require("$");
	var AutoComplete = require('autocomplete');

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

		$("#switch-operation").on('click', function() {
			var $hasSystemOperation = $('#hasSystemOperation');
			var hasSystemOperationValue = $hasSystemOperation.val();
			var hasSystemOperationNewValue = hasSystemOperationValue == 1 ? 0 : 1;
			$hasSystemOperation.val(hasSystemOperationNewValue);
			var $form = $('#search-form');
			$form.submit();
		});

		$("#log-module").change(function() {
			var url = $('#log-action').data('url');
			$.get(url, {
				module: this.value
			}, function(html) {
				$('#log-action').html(html);
			});
		});

		new AutoComplete({
			trigger: '#nickname',
			dataSource: $("#nickname").data('url'),
			filter: {
				name: 'stringMatch',
				options: {
					key: 'nickname'
				}
			},
			selectFirst: true
		}).render();

		$('[data-toggle="switch"]').on('click', function() {
			window.location.href = $(this).data('url');
		});

		$("#old-logs").on('click', function() {
			window.location.href = $(this).data('url');
		});
	};

});
