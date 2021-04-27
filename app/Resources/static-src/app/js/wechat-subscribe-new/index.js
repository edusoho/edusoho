import wx from 'weixin-js-sdk';
import notify from 'common/notify';

let $jweixin = $('.js-wechat-data');

wx.config($jweixin.data('config'));

wx.ready(function() {
  var btn = document.getElementById('subscribe-btn');
  btn.addEventListener('success', function (e) {
    console.log('success', e.detail);
    notify('danger', '订阅成功');
  });   
  btn.addEventListener('error',function (e) {
    console.log('fail', e.detail);
  });
});