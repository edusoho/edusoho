define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var SelectTree = require('edusoho.selecttree');
    require("jquery.bootstrap-datetimepicker");

    exports.run = function() {
         var selectTree = new SelectTree({
            element: "#orgSelectTree",
            name: 'orgCode'
        });

        $("#startDate").datetimepicker().on('changeDate', function() {
            $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));
        });

        $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));

        $("#endDate").datetimepicker().on('changeDate', function() {
            $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));
        });

        $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));

    };

});