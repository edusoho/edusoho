import notify from 'common/notify';



$("#orders-table").on('click', '.js-cancel', function() {
  let $that =  $(this);
  $.confirm({
    title: Translator.trans('user.account.cancel_order_title'),
    text: Translator.trans('user.account.cancel_order_hint'),
    confirm() {
      $.post($that.data('url'), function (data) {
        if (data != true) {
          notify('danger', Translator.trans('user.account.cancel_order_fail_hint'));
        }
        notify('success', Translator.trans('user.account.cancel_order_success_hint'));
        window.location.reload();
      });
    }
  })
});
