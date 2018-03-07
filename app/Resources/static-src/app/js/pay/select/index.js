import notify from 'common/notify';

let $modal = $('#modal');

$('.form-paytype').on('click', '.check', function () {
  let $this = $(this);
  if (!$this.hasClass('active') && !$this.hasClass('disabled')) {
    $this.addClass('active').siblings().removeClass('active');
    $('input[name=\'payment\']').val($this.attr('id'));
  }
  if ($this.attr('id') == 'quickpay') {
    $('.js-pay-agreement').show();
  } else {
    $('.js-pay-agreement').hide();
  }

}).on('click', '.js-order-cancel', function () {
  let $this = $(this);
  $.post($this.data('url'), function (data) {
    if (data != true) {
      notify('danger',Translator.trans('notify.order_cancel_failed.message'));
    }
    notify('success',Translator.trans('notify.order_cancel_succeed.message'));
    window.location.href = $this.data('goto');
  });

}).on('click', '.js-pay-bank', function (e) {
  e.stopPropagation();
  let $this = $(this);
  $this.addClass('checked').siblings('li').removeClass('checked');
  $this.find('input').prop('checked', true);

}).on('click', '.js-pay-bank .closed', function () {

  if (!confirm(Translator.trans('confirm.bind_pay_bank.message'))) {
    return;
  }

  let $this = $(this);
  let payAgreementId = $this.closest('.js-pay-bank').find('input').val();

  $.post($this.data('url'), { 'payAgreementId': payAgreementId }, function (response) {
    if (response.success == false) {
      notify('danger',response.message);
    } else {
      $modal.modal('show');
      $modal.html(response);
    }
  });
});

$('input[name=\'payment\']').val($('div .active').attr('id'));

$('#copy').on('click', function (event) {
  let textarea = document.createElement('textarea');
  textarea.style.position = 'fixed';
  textarea.style.top = 0;
  textarea.style.left = 0;
  textarea.style.border = 'none';
  textarea.style.outline = 'none';
  textarea.style.resize = 'none';
  textarea.style.background = 'transparent';
  textarea.style.color = 'transparent';

  textarea.value = document.location.href;
  let ele = $(textarea);
  $(this).append(ele);

  textarea.select();
  document.execCommand('copy');

  ele.remove();
  notify('success',Translator.trans('notify.copy_succeed.message'));
});
