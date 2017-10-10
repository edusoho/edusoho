define(function(require, exports, module) {
  require("jquery.bootstrap-datetimepicker");
  exports.run = function() {
    $startTime = $("#startTime");
    $endTime = $("#endTime");

    $startTime.datetimepicker({
        autoclose: true,
    }).on('changeDate', function() {
        $endTime.datetimepicker('setStartDate', $startTime.val().substring(0, 16));
    });
    $startTime.datetimepicker('setEndDate', $endTime.val().substring(0, 16));
    
    $endTime.datetimepicker({
        autoclose: true,
    }).on('changeDate', function() {
        $startTime.datetimepicker('setEndDate', $endTime.val().substring(0, 16));
    });
    $endTime.datetimepicker('setStartDate', $startTime.val().substring(0, 16));

  }
});
