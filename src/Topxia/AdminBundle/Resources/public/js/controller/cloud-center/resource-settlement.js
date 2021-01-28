define(function (require, exports, module) {

  require('jquery.bootstrap-datetimepicker');

  exports.run = function () {

    if ($('#startTime').length) {
      $('#startTime').datetimepicker({
        autoclose: true,
      }).on('changeDate', function () {
        $('#endTime').datetimepicker('setStartDate', $('#startTime').val().substring(0, 16));
      });

      $('#startTime').datetimepicker('setEndDate', $('#endTime').val().substring(0, 16));
    }

    if ($('#endTime').length) {
      $('#endTime').datetimepicker({
        autoclose: true,
      }).on('changeDate', function () {

        $('#startTime').datetimepicker('setEndDate', $('#endTime').val().substring(0, 16));
      });

      $('#endTime').datetimepicker('setStartDate', $('#startTime').val().substring(0, 16));
    }

    if ($('.form-switch').length) {
      $('[data-toggle="switch"]').on('click', function () {
        var $this = $(this);
        var $parent = $this.parent();
        var isEnable = $this.val();
        var reverseEnable = isEnable == 1 ? 0 : 1;

        $('input[name="showDebt"]').change();

        if ($parent.hasClass('checked')) {
          $parent.removeClass('checked');
        } else {
          $parent.addClass('checked');
        }
        $this.val(reverseEnable);
        $this.next().val(reverseEnable);
      });
    }
  };
})
;