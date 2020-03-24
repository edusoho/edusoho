define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);

  exports.run = function () {
    var $modal = $('#audit-modal').parents('.modal');
    var $form = $('#audit-modal');
    var validator = new Validator({
      element: '#audit-modal',
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return false;
        }
        $('#audit-submit-btn').button('submiting').addClass('disabled');
        $.post($form.attr('action'), $form.serialize(), function (result) {
          $modal.modal('hide');
          if (result.success === true) {
            Notify.success(Translator.trans('处理成功'));
          } else {
            Notify.danger(Translator.trans(result.message));
          }

          window.location.reload();
        }).error(function () {
          Notify.danger(Translator.trans('处理失败'));
        });
      }
    });

    $("input[name='status']").on('click', function (e) {
      if ($(this).val() == 'pass') {
        $('.js-reject').addClass('hidden');
        validator.removeItem('[name="reject_reason"]');
      } else {
        $('.js-reject').removeClass('hidden');
        validator.addItem({
          element: '[name="reject_reason"]',
          required: true
        });
      }
    });

    if ($("input[name='status']:checked").val() == 'reject') {
      validator.addItem({
        element: '[name="reject_reason"]',
        required: true
      });
    }
  }
})