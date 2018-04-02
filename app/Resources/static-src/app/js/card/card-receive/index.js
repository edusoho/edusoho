import notify from 'common/notify';

if ($('a').hasClass('money-card-use')) {
  let $moneyCard = $('.money-card-use');
  var url = $moneyCard.data('url');
  var coinName = $moneyCard.data('coinName');
  var targetUrl = $moneyCard.data('targetUrl');
  var coin = $('.card-coin-val').val();

  $.post(url, function (response) {
    notify('success',Translator.trans('card.card_receive_success_hint', {coin:coin, coinName: iconName}));
    setTimeout('window.location.href = \'' + targetUrl + '\'', 2000);
  }).error(function () {
    notify('danger',Translator.trans('card.card_receive_failed_hint'));
  });
}
