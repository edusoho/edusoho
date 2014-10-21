define(function(require, exports, module) {
    require("jquery.bootstrap-datetimepicker");

     exports.run = function() {
        $("#timePicker").datetimepicker({
            language: 'zh-CN',
            // autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month',
        }); 
    }
});
