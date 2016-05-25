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
            $btn.addClass('disabled');
            var url = $btn.data('url');
            var checkUrl = $btn.data('checkUrl');

            $.get(checkUrl, function(result) {
                if (result.status == 'error') {
                    Notify.warning("您的导出结果数量（" + result.count + "条）已超出最大值（" + result.maxAllowCount + "条），请调整筛选范围后分批导出");
                } else if (result.count == 0) {
                    Notify.warning("没有可导出的数据");
                } else {
                    Notify.warning("正在导出数据，请稍候...");
                    window.location.href = url;
                }
                setTimeout(function() {
                    $btn.removeClass('disabled');
                }, 3000);
            });
        })

    };

});