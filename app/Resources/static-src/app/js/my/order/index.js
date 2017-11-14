import notify from 'common/notify';

$("#orders-table").on('click', '.js-cancel-refund', function() {
  let $that =  $(this);

  cd.confirm({
    title: Translator.trans('user.account.refund_cancel_title'),
    content: Translator.trans('user.account.refund_cancel_hint'),
    confirmText: Translator.trans('site.confirm'),
    cancelText: Translator.trans('site.close'),
    confirm() {
      $.post($that.data('url'), function() {
        notify('success', Translator.trans('user.account.refund_cancel_success_hint'));

        setTimeout(function() {
          window.location.reload();
        }, 3000);
      });
    }
  });
});

$("#orders-table").on('click', '.js-cancel', function() {
  let $that =  $(this);

  cd.confirm({
    title: Translator.trans('user.account.cancel_order_title'),
    content: Translator.trans('user.account.cancel_order_hint'),
    confirmText: Translator.trans('site.confirm'),
    cancelText: Translator.trans('site.close'),
    confirm() {
      $.post($that.data('url'), function(data) {
        if (data != true) {
          notify('danger', Translator.trans('user.account.cancel_order_fail_hint'));
        }
        notify('success', Translator.trans('user.account.cancel_order_success_hint'));
        
        setTimeout(function() {
          window.location.reload();
        }, 3000);
      });
    }
  })
});
