define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");

    exports.run = function() {
        var $container = $('#message-table-container');
        var $table = $("#message-table");
        require('../../util/short-long-text')($table);
        require('../../util/batch-select')($container);
        require('../../util/batch-delete')($container);
        require('../../util/item-delete')($container);

        $("#startDate, #endDate").datetimepicker({
            autoclose: true
        });

    };

});