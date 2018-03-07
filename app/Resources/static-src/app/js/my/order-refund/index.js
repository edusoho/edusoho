import notify from 'common/notify';

$('#orders-table').on('click', '.js-cancel-refund', function() {
  let $that =  $(this);

  cd.confirm({
    title: Translator.trans('user.account.refund_cancel_title'),
    content: Translator.trans('user.account.refund_cancel_hint'),
    okText: Translator.trans('site.confirm'),
    cancelText: Translator.trans('site.close'),
  }).on('ok', () => {
    $.post($that.data('url'), function () {
      notify('success', Translator.trans('user.account.refund_cancel_success_hint'));
      window.location.reload();
    });
  });
});