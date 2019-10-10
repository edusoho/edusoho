define(function(require, exports, module) {

var Validator = require('bootstrap.validator');
require('jquery.bootstrap-datetimepicker');
require('common/validator-rules').inject(Validator);
require('jquery.form');

exports.run = function () {
  var $form = $('#notification-setting-form');
  $('.submit-btn').click(function () {
    $('.submit-btn').button('submiting').addClass('disabled');
  });
  var validator = new Validator({
    element: $form,
    autoSubmit: false,
    onFormValidated: function(error, results, $form) {
      if (error) {
        return false;
      }

      $.post($form.attr('action'), $form.serialize())
        .success(function(response) {
          window.location.reload();
      })
    }
  });

  $('.js-days-item').click(function () {
    var $this = $(this);
    var $input = $this.find('input');
    if ($input.is(':checked')) {
      $input.prop('checked', false);
      $this.removeClass('btn-primary').addClass('btn-default');
    } else {
      $input.prop('checked', true);
      $this.removeClass('btn-default').addClass('btn-primary');
      $('.submit-btn').button('reset').removeClass('disabled');
    }
  });

  validator.addItem({
    element: '[name="sendTime"]',
    required: true,
  });

  validator.addItem({
    element: '[name="sendDays[]"]',
    required: true,
    errormessageRequired: Translator.trans('site.choose_hint')+Translator.trans('admin.wechat_notification.send_days'),
  });

  $("#send-time").datetimepicker({
    language: 'zh-CN',
    autoclose: true,
    format: 'hh:ii',
    minView: 0,
    formatViewType: 'time',
    startView: 1,
  }).on('hide', function(){
    validator.query('#send-time').execute();
  });

    $("#send-time").datetimepicker('setValue', $("#send-time").val().substring(0, 5));
  };
})