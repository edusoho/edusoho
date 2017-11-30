define(function(require, exports, module){
  require("jquery.bootstrap-datetimepicker");
  var Notify = require('common/bootstrap-notify');

  exports.run = function(){
    $form = $("#user-search");
    $form.on('click', '#default-search', function () {
      $("[name='startDate']").val('');
      $("[name='endDate']").val('');
      $("[name='nickname']").val('');
      $("[name='isDefault']").val('true');
      $form.submit();
    });

    $form.on('click', '#search', function() {
      $("[name='isDefault']").val('false');
      $form.submit();
    });

    $("#startDate").datetimepicker({
      autoclose: true,
      format: 'yyyy-mm-dd',
      minView: 2
    }).on('changeDate', function() {
      var startDate = $("#endDate").val().substring(0, 16);
      $("#endDate").datetimepicker('setStartDate', startDate);
      var minDate = $("#startDate").data('minTime')

      if ($("#startDate").val().substring(0, 16) <= minDate) {
        $("#startDate").val(minDate);
        $("#startDate").datetimepicker('setStartDate', minDate);
      }
    });

    $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));

    $("#endDate").datetimepicker({
      autoclose: true,
      format: 'yyyy-mm-dd',
      minView: 2
    }).on('changeDate', function() {
      $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));
    });

    $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));
  };
});
