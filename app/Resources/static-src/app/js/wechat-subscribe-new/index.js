import wx from 'weixin-js-sdk';

let $jweixin = $('.js-wechat-data');

wx.config($jweixin.data('config'));

wx.ready(function() {
  var btn = document.getElementById('subscribe-btn');
  btn.addEventListener('success', function (e) {            
    console.log('success', e.detail);
  });   
  btn.addEventListener('error',function (e) {             
    console.log('fail', e.detail);
  });
});