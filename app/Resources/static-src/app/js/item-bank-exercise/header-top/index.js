import 'app/common/widget/qrcode';

ancelRefund();

function ancelRefund() {
  $('.cancel-refund').on('click', function () {
    if (!confirm(Translator.trans('course_set.refund_cancel_hint'))) {
      return false;
    }
    $.post($(this).data('url'), function (data) {
      window.location.reload();
    });
  });
}
