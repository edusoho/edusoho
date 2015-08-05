define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    var validator = require('bootstrap.validator');

    exports.run = function() {

        $("#startDate").datetimepicker({
            autoclose: true
        }).on('changeDate',function(){
            $("#endDate").datetimepicker('setStartDate',$("#startDate").val().substring(0,16));
        });

        $("#startDate").datetimepicker('setEndDate',$("#endDate").val().substring(0,16));

        $("#endDate").datetimepicker({
            autoclose: true
        }).on('changeDate',function(){

            $("#startDate").datetimepicker('setEndDate',$("#endDate").val().substring(0,16));
        });

        $("#endDate").datetimepicker('setStartDate',$("#startDate").val().substring(0,16));

    };

});