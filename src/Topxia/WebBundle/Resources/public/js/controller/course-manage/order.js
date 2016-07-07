define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
   	require("jquery.bootstrap-datetimepicker");
	require("$");

    exports.run = function() {

        $("#startDate").datetimepicker({
            language: 'zh-CN',
            autoclose: true
        }).on('hide', function(ev){
            validator.query('#startDate').execute();
        });

		$("#startDate").datetimepicker().on('changeDate',function(){

            $("#endDate").datetimepicker('setStartDate',$("#startDate").val().substring(0,16));
        });

        $("#startDate").datetimepicker('setEndDate',$("#endDate").val().substring(0,16));


        $("#endDate").datetimepicker({
            language: 'zh-CN',
            autoclose: true
        }).on('hide', function(ev){
            validator.query('#endDate').execute();
        });

        $("#endDate").datetimepicker().on('changeDate',function(){

            $("#startDate").datetimepicker('setEndDate',$("#endDate").val().substring(0,16));
        });

        $("#endDate").datetimepicker('setStartDate',$("#startDate").val().substring(0,16));

    };

});
