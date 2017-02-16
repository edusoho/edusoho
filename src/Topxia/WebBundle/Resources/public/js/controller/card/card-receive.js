define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');

  exports.run = function (){
      if ($('a').hasClass('money-card-use')) {
          var url = $('.money-card-use').data('url');
          var target_url = $('.money-card-use').data('target-url');
          var coin = $('.card-coin-val').val();

          $.post(url, function(response){
            Notify.success(Translator.trans('学习卡已使用，充值'+ coin +'虚拟币成功，可前往【账户中心】-【我的账户】查看充值情况。'), 2);
            setTimeout("window.location.href = '" + target_url + "'",2000);
          }).error(function() {
          Notify.danger(Translator.trans('失败！'), 1);
          });
      }
  };
});

