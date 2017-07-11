import notify from 'common/notify';

$("#orders-table").on('click', '.cancel-refund', function () {
  if (!confirm(Translator.trans('user.account.refund_cancel_hint'))) {
    return false;
  }

  $.post($(this).data('url'), function () {
    notify('success', Translator.trans('user.account.refund_cancel_success_hint'));
    window.location.reload();
  });
});

$("#orders-table").on('click', '.pay', function () {
  $.post($(this).data('url'), { orderId: $(this).data('orderId') }, function (html) {
    $("body").html(html);
  });
});

$("#orders-table").on('click', '.cancel', function () {
  if (!confirm(Translator.trans('user.account.cancel_order_hint'))) {
    return false;
  }

  $.post($(this).data('url'), function (data) {
    if (data != true) {
      notify('danger', Translator.trans('user.account.cancel_order_fail_hint'));
    }
    notify('success', Translator.trans('user.account.cancel_order_success_hint'));
    window.location.reload();
  });
});

