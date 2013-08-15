define(function(require, exports, module){

	require("jquery.bootstrap-datetimepicker");
	require("$");

	exports.run = function(){
		$("#form_start, #form_end").datetimepicker();		
	};

});