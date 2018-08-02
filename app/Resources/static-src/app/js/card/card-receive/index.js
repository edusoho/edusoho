import notify from 'common/notify';

if ($('a').hasClass('money-card-use')) {
  let $moneyCard = $('.money-card-use');
  let url = $moneyCard.data('url');
  let coinName = $moneyCard.data('coinName');
  let targetUrl = $moneyCard.data('targetUrl');
  let coinAmount = $('.card-coin-val').val();
  $.post(url, function (response) {
    notify('success',Translator.trans('card.card_receive_success_hint', {coinAmount: coinAmount, coinName: coinName}));
    setTimeout('window.location.href = \'' + targetUrl + '\'', 2000);
  }).error(function () {
    notify('danger',Translator.trans('card.card_receive_failed_hint'));
  });
}
