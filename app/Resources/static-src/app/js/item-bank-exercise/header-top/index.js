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

$(document).on('click', '.js-handleExerciseClosed', function (event) {
  event.preventDefault();
  window.location.href = '/course/closed?type=exercise'
});