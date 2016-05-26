define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    var validator = require('bootstrap.validator');

    exports.run = function() {

        $("#startDate").datetimepicker({
            autoclose: true
        }).on('changeDate', function() {
            $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));
        });

        $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));

        $("#endDate").datetimepicker({
            autoclose: true
        }).on('changeDate', function() {
            $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));
        });

        $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));


        $(".btn-export-csv").on('click', function(event) {
            var $btn = $(this);
            var exportCount = $btn.data('exportCount');
            var exportCountFormat = $btn.data('exportCountFormat');
            var exportAllowCount = $btn.data('exportAllowCount');
            var exportAllowCountFormat = $btn.data('exportAllowCountFormat');

            if (exportCount > exportAllowCount) {
                Notify.danger("您的导出结果数量（" + exportCountFormat + "条）已超出最大值（" + exportAllowCountFormat + "条），请调整筛选范围后分批导出");
                event.preventDefault();
                return false;
            }
            if (exportCount == 0) {
                Notify.danger("没有可导出的数据");
                event.preventDefault();
                return false;
            }
            Notify.success("正在导出数据，请稍候...");
        })

    };

});