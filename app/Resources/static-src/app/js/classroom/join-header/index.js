$(".cancel-refund").on('click', function () {
  if (!confirm(Translator.trans('classroom.join.cancel_refund_hint'))) {
    return false;
  }

  $.post($(this).data('url'), function () {
    window.location.reload();
  });
});