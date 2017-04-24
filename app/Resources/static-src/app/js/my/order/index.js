import notify from 'common/notify';

$("#orders-table").on('click', '.cancel-refund', function () {
  if (!confirm(Translator.trans('真的要取消退款吗？'))) {
    return false;
  }

  $.post($(this).data('url'), function () {
    notify('success', Translator.trans('退款申请已取消成功！'));
    window.location.reload();
  });
});

$("#orders-table").on('click', '.pay', function () {
  $.post($(this).data('url'), { orderId: $(this).data('orderId') }, function (html) {
    $("body").html(html);
  });
});

$("#orders-table").on('click', '.cancel', function () {
  if (!confirm(Translator.trans('真的要取消订单吗？'))) {
    return false;
  }

  $.post($(this).data('url'), function (data) {
    if (data != true) {
      notify('danger', Translator.trans('订单取消失败！'));
    }
    notify('success', Translator.trans('订单已取消成功！'));
    window.location.reload();
  });
});

