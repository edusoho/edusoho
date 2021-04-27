import wx from 'weixin-js-sdk';
import notify from 'common/notify';

let $jweixin = $('.js-wechat-data');

wx.config($jweixin.data('config'));

notify('success', '订阅成功-hongbusi');

wx.ready(function() {
  var btn = document.getElementById('subscribe-btn');
  btn.addEventListener('success', function (e) {
    console.log('Hongbusi-success', e.detail);
    notify('success', '订阅成功');
  });   
  btn.addEventListener('error',function (e) {
    console.log('fail', e.detail);
  });
});