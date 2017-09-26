import notify from 'common/notify';

$("#orders-table").on('click', '.js-cancel-refund', function() {
  let $that =  $(this);
  $.confirm({
    title: Translator.trans('user.account.refund_cancel_title'),
    text: Translator.trans('user.account.refund_cancel_hint'),
    confirm() {
      $.post($that.data('url'), function () {
        notify('success', Translator.trans('user.account.refund_cancel_success_hint'));
        window.location.reload();
      });
    }
  })
});