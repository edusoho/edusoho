$(document).ready(function(){
	console.log('init : ', $, $.fn.datetimepicker);
	$("#startTime").datepicker({
	    language: 'zh-CN',
	    autoclose: true,
	    format: 'yyyy-mm-dd',
	    minView: 'month'
	});
	$("#startTime").datepicker('setStartDate', new Date());
});
