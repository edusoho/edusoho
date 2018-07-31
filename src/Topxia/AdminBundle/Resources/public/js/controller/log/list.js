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


		$("#log-module").change(function() {
			var url = $('#log-action').data('url');
			$.get(url, {
				module: this.value
			}, function(html) {
				$('#log-action').html(html);
			});
		});
		
		var autocomplete = new AutoComplete({
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
	};

});