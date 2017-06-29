define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    var validator = require('bootstrap.validator');

    exports.run = function() {
        
        $("#start-date").datetimepicker({
            autoclose: true
        }).on('changeDate',function(){
            $("#end-date").datetimepicker('setStartDate',$("#start-date").val().substring(0,16));
        });

        $("#start-date").datetimepicker('setEndDate',$("#end-date").val().substring(0,16));

        $("#end-date").datetimepicker({
            autoclose: true
        }).on('changeDate',function(){

            $("#start-date").datetimepicker('setEndDate',$("#end-date").val().substring(0,16));
        });

        $("#end-date").datetimepicker('setStartDate',$("#start-date").val().substring(0,16));
       
    };

});