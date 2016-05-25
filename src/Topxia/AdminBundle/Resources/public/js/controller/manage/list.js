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


        $(".btn-export-csv").on('click', function() {
            var $btn = $(this);
            var url = $btn.data('url');
            var checkUrl = $btn.data('checkUrl');
            var exportCount = $btn.data('exportCount');
            var exportCountFormat = $btn.data('exportCountFormat');
            var exportAllowCount = $btn.data('exportAllowCount');
            var exportAllowCountFormat = $btn.data('exportAllowCountFormat');

            if (exportCount > exportAllowCount) {
                Notify.warning("您的导出结果数量（" + exportCountFormat + "条）已超出最大值（" + exportAllowCountFormat + "条），请调整筛选范围后分批导出");
                return false;
            }
            if (exportCount == 0) {
                Notify.warning("没有可导出的数据");
                return false;
            }
            $btn.addClass('disabled');
            Notify.warning("正在导出数据，请稍候...");
            window.location.href = url;
            $btn.removeClass('disabled');
        })

    };

});