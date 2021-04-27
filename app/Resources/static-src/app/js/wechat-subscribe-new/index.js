import wx from 'weixin-js-sdk';
import notify from 'common/notify';

let $jweixin = $('.js-wechat-data');

wx.config($jweixin.data('config'));

wx.ready(function() {
  var btn = document.getElementById('subscribe-btn');
  btn.addEventListener('success', function (e) {
    const subscribeDetails = e.detail.subscribeDetails;
    if (subscribeDetails.indexof('accept') !== -1) {
      notify('success', '订阅成功indexof');
    }
    console.log('Hongbusi-success', subscribeDetails);
    console.log('Hongbusi-success', subscribeDetails.indexof('accept'));
  });   
  btn.addEventListener('error',function (e) {
    console.log('fail', e.detail);
  });
});