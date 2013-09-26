define(function(require, exports, module){

	require("jquery.bootstrap-datetimepicker");
	require("$");

	exports.run = function(){
		$("#startDateTime, #endDateTime").datetimepicker();		
	};

});