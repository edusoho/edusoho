import notify from 'common/notify';

if ($('a').hasClass('money-card-use')) {
  var url = $('.money-card-use').data('url');
  var target_url = $('.money-card-use').data('target-url');
  var coin = $('.card-coin-val').val();

  $.post(url, function (response) {
    notify('success',Translator.trans('学习卡已使用，充值' + coin + '虚拟币成功，可前往【账户中心】-【我的账户】查看充值情况。'));
    setTimeout("window.location.href = '" + target_url + "'", 2000);
  }).error(function () {
    notify('danger',Translator.trans('失败！'));
  });
}

