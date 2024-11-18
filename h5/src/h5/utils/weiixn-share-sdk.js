import wx from 'weixin-js-sdk';
import Api from '@/api';

/**
 * 初始化微信分享业务。
 * 接口参数：
 *    title         (必填)   微信分享的标题
 *    des           (必填)   微信分享的描述
 *    imgUrl        (必填)   分享的小logo图片的地址
 *    link          (必填)   点击跳转的链接
 */
const initShare = ({ title, desc, imgUrl, link }) => {

  const shareLink =
    window.location.href.split('#')[0].replace(/index.html/, 'redirect.html') +
    '?shareRedirect=' +
    encodeURIComponent(link);
  const params = {
    url: window.location.href.split('#')[0],
  };
  Api.wechatJsSdkConfig({ params }).then(res => {
    wx.config({
      // debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
      appId: res.appId, // 必填，公众号的唯一标识
      timestamp: res.timestamp, // 必填，生成签名的时间戳
      nonceStr: res.nonceStr, // 必填，生成签名的随机串
      signature: res.signature, // 必填，签名
      jsApiList: res.jsApiList, // 必填，需要使用的JS接口列表
    });
    wx.ready(() => {
      // 分享好友设置
      wx.updateAppMessageShareData({
        title,
        link: shareLink,
        imgUrl,
        desc,
        success() {
          console.log('分享成功');
        },
        fail(err) {
          console.log(err);
        },
      });
      wx.updateTimelineShareData({
        title,
        link: shareLink,
        imgUrl,
        success: function() {
          console.log('分享成功');
        },
        fail(err) {
          console.log(err);
        },
      });
    });
  });
};

export default initShare;
