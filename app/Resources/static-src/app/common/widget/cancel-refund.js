import notify from 'common/notify';

$(".cancel-refund").on('click', function () {
  if (!confirm(Translator.trans('真的要取消退款吗？'))) {
    return false;
  }

  $.post($(this).data('url'), function () {
    notify('success', Translator.trans('退款申请已取消成功！'));
    window.location.reload();
  });
});