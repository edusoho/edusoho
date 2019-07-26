import notify from 'common/notify';

$('#orders-table').on('click', '.js-cancel-refund', function() {
  let $that =  $(this);
  cd.confirm({
    title: Translator.trans('user.account.refund_cancel_title'),
    content: Translator.trans('user.account.refund_cancel_hint'),
    okText: Translator.trans('site.confirm'),
    cancelText: Translator.trans('site.close'),
  }).on('ok', () => {
    $.post($that.data('url'), function() {
      notify('success', Translator.trans('user.account.refund_cancel_success_hint'));
  
      setTimeout(function() {
        window.location.reload();
      }, 3000);
    });
  });
});

$('#orders-table').on('click', '.js-cancel', function() {
  let $that =  $(this);

  cd.confirm({
    title: Translator.trans('user.account.cancel_order_title'),
    content: Translator.trans('user.account.cancel_order_hint'),
    okText: Translator.trans('site.confirm'),
    cancelText: Translator.trans('site.close'),
  }).on('ok', () => {
    $.post($that.data('url'), function(data) {
      if (data != true) {
        notify('danger', Translator.trans('user.account.cancel_order_fail_hint'));
      }
      notify('success', Translator.trans('user.account.cancel_order_success_hint'));
      
      setTimeout(function() {
        window.location.reload();
      }, 3000);
    });
  });
});

$('#orders-table').on('click', '.js-pay', function () {
  let $that = $(this);

  cd.confirm({
    title: Translator.trans('user.account.order_pay_'+ $that.data('type') +'_close_title'),
    content: Translator.trans('user.account.order_pay_'+ $that.data('type') +'_close_hint'),
    okText: Translator.trans('user.account.order_not_pay'),
    cancelText: Translator.trans('user.account.order_pay'),
  }).on('cancel', () => {
    window.location.href = $that.data('url');
  });
});
