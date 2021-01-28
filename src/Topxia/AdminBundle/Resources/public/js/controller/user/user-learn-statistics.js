define(function(require, exports, module){
  require('jquery.bootstrap-datetimepicker');
  var Notify = require('common/bootstrap-notify');

  exports.run = function(){
    var $form = $('#user-search');

    $(document).keyup(function(event){
      if(event.keyCode ==13){
        $('#search').trigger('click');
      }
    });

    $form.on('click', '#default-search', function () {
      $('[name=\'nickname\']').val('');
      $('[name=\'isDefault\']').val('true');
      $form.submit();
    });

    $form.on('click', '#search', function() {
      $('[name=\'isDefault\']').val('false');
      $form.submit();
    });

    $('#startDate').datetimepicker({
      autoclose: true,
      format: 'yyyy-mm-dd',
      minView: 2
    }).on('changeDate', function() {
      var startDate = $('#startDate').val().substring(0, 16);
      $('#endDate').datetimepicker('setStartDate', startDate);
    });

    $('#startDate').datetimepicker('setStartDate', $('#startDate').data('minTime'));
    $('#startDate').datetimepicker('setEndDate', $('#endDate').val().substring(0, 16));

    $('#endDate').datetimepicker({
      autoclose: true,
      format: 'yyyy-mm-dd',
      minView: 2
    }).on('changeDate', function() {
      $('#startDate').datetimepicker('setEndDate', $('#endDate').val().substring(0, 16));
    });

    $('#endDate').datetimepicker('setStartDate', $('#startDate').val().substring(0, 16));
  };
});
