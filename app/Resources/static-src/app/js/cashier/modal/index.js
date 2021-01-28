import notify from 'common/notify';

$('.js-confirm-btn').on('click', event => {
  let $target = $(event.currentTarget);

  $.get($target.data('url'), resp => {
    if (resp.isPaid) {
      location.href = resp.redirectUrl;
    } else {
      notify('danger', Translator.trans('cashier.confirm.fail_message'));
      $('#modal').modal('hide');
    }
  });
});