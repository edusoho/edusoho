import notify from 'common/notify';

$("#orders-table").on('click', '.js-cancel-refund', function () {
  $.confirm({
    text: Translator.trans('user.account.refund_cancel_hint'),
    confirm() {
      $.post($(this).data('url'), function () {
        notify('success', Translator.trans('user.account.refund_cancel_success_hint'));
        window.location.reload();
      });
    }
  })
});

$("#orders-table").on('click', '.js-cancel', function () {
  $.confirm({
    text: Translator.trans('user.account.cancel_order_hint'),
    confirm() {
      $.post($(this).data('url'), function (data) {
        if (data != true) {
          notify('danger', Translator.trans('user.account.cancel_order_fail_hint'));
        }
        notify('success', Translator.trans('user.account.cancel_order_success_hint'));
        window.location.reload();
      });
    }
  })
});

