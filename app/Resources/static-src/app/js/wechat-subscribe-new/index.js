import wx from 'weixin-js-sdk';
import notify from 'common/notify';

let $jweixin = $('.js-wechat-data');

if (isWechatBrowser()) {
  $.ajax({
    url: '/api/settings/wechat_message_subscribe',
    type: 'GET',
    headers:{
      'Accept':'application/vnd.edusoho.v2+json'
    }
  }).success(function (res) {
    if (!res.enable) return;
    initWechatConfig();
  });
};

// 判断是不是微信环境
function isWechatBrowser() {
  const browser = navigator.userAgent.toLowerCase();
  return browser.match(/MicroMessenger/i) == 'micromessenger';
}

function initWechatConfig() {
  wx.config($jweixin.data('config'));

  const reg = /accept/;
  
  wx.ready(function() {
    var btn = document.getElementById('subscribe-btn');
    btn.addEventListener('success', function (e) {
      const subscribeDetails = e.detail.subscribeDetails;
      if (reg.test(subscribeDetails)) {
        notify('success', '订阅成功');
      }
    });   
    btn.addEventListener('error',function (e) {
      console.log('fail', e.detail);
    });
  });

  $('.js-wechat-subscribe').removeClass('hidden');
}