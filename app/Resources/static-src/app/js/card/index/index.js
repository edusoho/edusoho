import notify from 'common/notify';
$('a[role=filter-change]').click(function (event) {
  window.location.href = $(this).data('url');
});

$('.receive-modal').click();
$('body').on('click', '.money-card-use', function () {
  $('body').off('click', '.money-card-use');
  let url = $(this).data('url');
  let target_url = $(this).data('target-url');
  let coinName = $(this).data('coinName');
  let coinAmount = $('.card-coin-val').val();

  $.post(url, function (response) {
    notify('success', Translator.trans('card.card_receive_success_hint', {coinAmount: coinAmount, coinName: coinName}));
    setTimeout('window.location.href = \'' + target_url + '\'', 2000);
  }).error(function () {
    notify('danger', Translator.trans('card.card_receive_failed_hint'));
  });
});



$('body').on('click', '[data-role=coupon-pack]', function () {
  let url = $(this).data('url');
  $.post($(this).data('url'), function (html) {
    $('#modal').html(html);
  });
});
