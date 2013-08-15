define(function(require, exports, module){

	require("jquery.bootstrap-datetimepicker");
	require("$");

	exports.run = function(){
		$("#form_startDateTime, #form_endDateTime").datetimepicker();		
	};

});