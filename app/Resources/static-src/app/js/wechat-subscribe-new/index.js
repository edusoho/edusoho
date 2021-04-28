import wx from 'weixin-js-sdk';
import notify from 'common/notify';

let $jweixin = $('.js-wechat-data');

// 判断是不是微信环境
const wx = navigator.userAgent.toLowerCase();
if (wx.match(/MicroMessenger/i) == 'micromessenger') return;

wx.config($jweixin.data('config'));

const reg = /accept/;

wx.ready(function() {
  var btn = document.getElementById('subscribe-btn');
  $('.js-wechat-subscribe').removeClass('hidden');
  btn.addEventListener('success', function (e) {
    const subscribeDetails = e.detail.subscribeDetails;
    console.log(subscribeDetails);
    if (reg.test(subscribeDetails)) {
      notify('success', '订阅成功');
    }
  });   
  btn.addEventListener('error',function (e) {
    console.log('fail', e.detail);
  });
});