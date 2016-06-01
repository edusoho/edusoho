define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    var validator = require('bootstrap.validator');
    exports.run = function() {
        var now = new Date();
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
        if ($("#status").val() == 'end') {
            $("#endDate").datetimepicker('setEndDate', now);
            $("#startDate").datetimepicker('setEndDate', now);
        }
        if ($("#status").val() == 'coming') {
            $("#startDate").datetimepicker('setStartDate', now);
            $("#endDate").datetimepicker('setStartDate', now); //只用开始时间搜,不考虑结束时间
        }
        if ($("#status").val() == 'underway') {
            $("#startDate").datetimepicker('setEndDate', now);
            $("#endDate").datetimepicker('setStartDate', now);
        }

        // .datetimepicker('setEndDate'  可视为<
        // .datetimepicker('setStartDate' 可视为>

        $('#course-table').on('click', 'tbody tr span ', function() {
            $.get($(this).parent().data('url'), function(data) {
                $('#course-table tbody tr td')[4].innerHTML = data.maxOnlineNum;
            });
        });

    };

});