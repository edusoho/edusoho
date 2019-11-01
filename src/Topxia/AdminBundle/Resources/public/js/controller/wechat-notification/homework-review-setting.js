define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  require('jquery.bootstrap-datetimepicker');
  require('common/validator-rules').inject(Validator);
  require('jquery.form');

  exports.run = function () {
    var $form = $('#notification-setting-form');
    $('.save-btn').click(function () {
      $('.save-btn').button('submiting').addClass('disabled');
    });
    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          $('.save-btn').button('reset');
          return false;
        }

        $.post($form.attr('action'), $form.serialize())
          .success(function(response) {
            window.location.reload();
          });
      }
    });

    validator.addItem({
      element: '[name="sendTime"]',
      required: true,
    });

    $('#send-time').datetimepicker({
      language: 'zh-CN',
      autoclose: true,
      format: 'hh:ii',
      minView: 0,
      formatViewType: 'time',
      startView: 1,
    }).on('hide', function(){
      validator.query('#send-time').execute();
    });

    $('#send-time').datetimepicker('setValue', $('#send-time').val().substring(0, 5));
  };
})