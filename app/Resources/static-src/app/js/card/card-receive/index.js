import notify from 'common/notify';

if ($('a').hasClass('money-card-use')) {
  var url = $('.money-card-use').data('url');
  var target_url = $('.money-card-use').data('target-url');
  var coin = $('.card-coin-val').val();

  $.post(url, function (response) {
    notify('success',Translator.trans('card.card_receive_success_hint', {coin:coin}));
    setTimeout('window.location.href = \'' + target_url + '\'', 2000);
  }).error(function () {
    notify('danger',Translator.trans('card.card_receive_failed_hint'));
  });
}
