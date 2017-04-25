import notify from 'common/notify';
$('a[role=filter-change]').click(function (event) {
  window.location.href = $(this).data('url');
});

$('.receive-modal').click();
$('body').on('click', '.money-card-use', function () {
  $('body').off('click', '.money-card-use');
  var url = $(this).data('url');
  var target_url = $(this).data('target-url');
  var coin = $(this).prev().text();

  $.post(url, function (response) {
    notify('success', Translator.trans('card.card_receive_success_hint', {coin:coin}), 2);
    setTimeout("window.location.href = '" + target_url + "'", 2000);
  }).error(function () {
    notify('danger', Translator.trans('card.card_receive_failed_hint'), 1);
  });
});

