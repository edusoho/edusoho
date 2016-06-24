define(function(require, exports, module) {
	require('jquery.bootstrap-datetimepicker');
	exports.run = function() {

		$("#startTime").datetimepicker({
			language: 'zh-CN',
			autoclose: true,
			format: 'yyyy-mm-dd',
			minView: 2
		}).on('changeDate', function() {
			$("#endTime").datetimepicker('setStartDate', $("#startTime").val());
		});

		$("#startTime").datetimepicker('setEndDate', $("#endTime").val());

		$("#endTime").datetimepicker({
			language: 'zh-CN',
			autoclose: true,
			format: 'yyyy-mm-dd',
			minView: 2
		}).on('changeDate', function() {
			$("#startTime").datetimepicker('setEndDate', $("#endTime").val());
		});

		$("#endTime").datetimepicker('setStartDate', $("#startTime").val());

		$('#user-search-form').on('click', '.btn-data-range', function() {
			$('.btn-data-range').removeClass('active');
			$(this).addClass('active');
			$("#startTime").val($(this).data('start'));
			$("#endTime").val($(this).data('end'));
			$("#endTime").datetimepicker('setStartDate', $("#startTime").val());

		})

	}
});