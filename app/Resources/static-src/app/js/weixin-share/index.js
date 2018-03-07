import wx from 'weixin-js-sdk';

let $jweixin = $('.jweixin-data');

wx.config($jweixin.data('config'));

wx.ready(function() {
  //分享到朋友圈
  wx.onMenuShareTimeline({
    title: $jweixin.data('title'),
    link: $jweixin.data('link'),
    imgUrl: $jweixin.data('imgUrl')
  });

  //分享给朋友
  wx.onMenuShareAppMessage({
    title: $jweixin.data('title'), // 分享标题
    desc: $jweixin.data('desc'), // 分享描述
    link: $jweixin.data('link'), // 分享链接
    imgUrl: $jweixin.data('imgUrl'), // 分享图标
    type: '', // 分享类型,music、video或link，不填默认为link
    dataUrl: '' // 如果type是music或video，则要提供数据链接，默认为空
  });

  //分享到QQ
  wx.onMenuShareQQ({
    title: $jweixin.data('title'), 
    desc: $jweixin.data('desc'), 
    link: $jweixin.data('link'), 
    imgUrl: $jweixin.data('imgUrl') 
  });

  //分享到QQ空间
  wx.onMenuShareQZone({
    title: $jweixin.data('title'), 
    desc: $jweixin.data('desc'),
    link: $jweixin.data('link'),
    imgUrl: $jweixin.data('imgUrl')
  });
});