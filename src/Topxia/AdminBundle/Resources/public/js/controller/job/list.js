define(function(require, exports, module){

	require("jquery.bootstrap-datetimepicker");

	exports.run = function(){
		$("#nextExcutedStartTime,#nextExcutedEndTime").datetimepicker({
			autoclose: true
		});	

		$("#tips").popover({
		    html: true,
		    trigger: 'hover',//'hover','click'
		    content: $("#tips-html").html()
		});
	};

});