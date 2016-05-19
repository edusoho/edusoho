define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var SelectTree = require('edusoho.selecttree');
    require("jquery.bootstrap-datetimepicker");

    exports.run = function() {
        var selectTree = new SelectTree({
            element: "#orgSelectTree",
            name: 'orgCode'
        });
        var $container = $('#message-table-container');
        var $table = $("#message-table");
        require('../../util/short-long-text')($table);
        require('../../util/batch-select')($container);
        require('../../util/batch-delete')($container);
        require('../../util/item-delete')($container);
        $("#startDate, #endDate").datetimepicker({
            autoclose:true
        });

    };

});