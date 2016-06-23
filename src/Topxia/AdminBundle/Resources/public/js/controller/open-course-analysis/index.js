define(function(require, exports, module) {
	require('jquery.bootstrap-datetimepicker');
	exports.run = function() {

		$("#startTime").datetimepicker({
			language: 'zh-CN',
			autoclose: true,
			format: 'yyyy-mm-dd',
			minView: 2
		}).on('changeDate', function() {
			$("#endTime").datetimepicker('setstartTime', $("#startTime").val());
		});

		$("#startTime").datetimepicker('setendTime', $("#endTime").val());

		$("#endTime").datetimepicker({
			language: 'zh-CN',
			autoclose: true,
			format: 'yyyy-mm-dd',
			minView: 2
		}).on('changeDate', function() {
			$("#startTime").datetimepicker('setendTime', $("#endTime").val());
		});

		$("#endTime").datetimepicker('setstartTime', $("#startTime").val());

		$('#user-search-form').on('click', '.btn-data-range', function() {
			$("#startTime").val($(this).data('start'));
			$("#endTime").val($(this).data('end'));
			$("#endTime").datetimepicker('setstartTime', $("#startTime").val());

		})

	}
});