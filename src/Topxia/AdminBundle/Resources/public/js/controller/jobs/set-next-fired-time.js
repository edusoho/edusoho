define(function(require, exports, module){

  require('jquery.bootstrap-datetimepicker');
  var Notify = require('common/bootstrap-notify');

  exports.run = function(){

    $('[name=nextExecTime]').datetimepicker({
      language: 'zh-CN',
      autoclose: true,
      format: 'yyyy-mm-dd hh:ii',
      minView: 0,
      formatViewType: 'time',
      // startView: 1,
    });

    $('[name=nextExecTime]').datetimepicker('setStartDate', $('[name=nextExecTime]').data('now'));

    var $form = $('#set-next-fire-time-form');

    $form.submit(function() {
      $('#set-exec-time-btn').button('submiting').addClass('disabled');
      $.post($form.attr('action'), $form.serialize(), function(response) {
        if (response == true) {
          Notify.success(Translator.trans('site.modify.success'),1);
          window.location.reload();
        }else {
          Notify.warning(Translator.trans('site.save.fail'),1);
          $('#set-exec-time-btn').button('reset').removeClass('disabled');
        }
      }, 'json');
      return false;
    });
  };
});
