import wx from 'weixin-js-sdk';
import Api from '@/api';

const initSubscribe = () => {
  const params = {
    url: window.location.href.split('#')[0],
  };
  Api.wechatJsSdkConfig({ params }).then(res => {
    wx.config({
      debug: false,
      appId: res.appId,
      timestamp: res.timestamp,
      nonceStr: res.nonceStr,
      signature: res.signature,
      jsApiList: res.jsApiList,
      openTagList: ['wx-open-subscribe'],
    });
    wx.ready(() => {
      const btn = document.getElementById('subscribe-btn');
      btn.addEventListener('success', function(e) {
        console.log('success', e.detail);
      });
      btn.addEventListener('error', function(e) {
        console.log('fail', e.detail);
      });
      return true;
    });
  });
};

export default initSubscribe;
