define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
   	require("jquery.bootstrap-datetimepicker");
	require("$");

    exports.run = function() {
		$("#startDate, #endDate").datetimepicker();		

    };

});