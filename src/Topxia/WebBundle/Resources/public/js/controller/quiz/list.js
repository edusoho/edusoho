define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
   	require("jquery.bootstrap-datetimepicker");
	require("$");
    exports.run = function() {
        var $container = $('#quiz-table-container');
        // var $table = $("#quiz-table");
        // require('../../util/short-long-text')($table);
        require('../../util/batch-select')($container);
        require('../../util/batch-delete')($container);
        // require('../../util/item-delete')($container);
		// $("#startDate, #endDate").datetimepicker();		

    };

});