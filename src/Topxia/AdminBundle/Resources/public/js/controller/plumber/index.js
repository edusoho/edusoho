define(function (require, exports, module) {
  let Notify = require('common/bootstrap-notify');

  exports.run = function () {
    if ($(".js-operator-plumber").length) {
      $("body").on('click', '.js-operator-plumber', function () {
        $(this).attr('disabled', true);
        Notify.warning(Translator.trans('admin_v2.developer.plumber_operate_hint'));

        $.post($('#plumber-inf').data('url'), {
          action: $(this).data('action'),
          _csrf_token: $('meta[name=csrf-token]').attr('content')
        }, function (response) {
          if (response.result) {
            Notify.success(Translator.trans('admin_v2.developer.plumber_operate_success_hint'));

            $('#plumber-info').html(response.template);
          } else {
            Notify.danger(Translator.trans('admin_v2.developer.plumber_operate_error_hint'));
          }
          $(this).removeAttr('disabled');

        });
      });
    }
  };
});

