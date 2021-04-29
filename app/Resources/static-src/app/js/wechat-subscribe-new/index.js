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
    if (res.enable) {
      initWechatSubscribe();
    }
  });
};

// 判断是不是微信环境
function isWechatBrowser() {
  const browser = navigator.userAgent.toLowerCase();
  return browser.match(/MicroMessenger/i) == 'micromessenger';
}

function initWechatSubscribe() {
  $.ajax({
    url: '/api/template',
    type: 'GET',
    headers:{
      'Accept':'application/vnd.edusoho.v2+json'
    },
    beforeSend(request) {
      request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
    },
  }).success(function (res) {
    if (!res) return;

    const template = 
    `
    <i class="es-icon es-icon-share"></i>
    <br>
    <wx-open-subscribe template="${res}" id="subscribe-btn">
      <template slot="style">
        <style>
          .subscribe-btn {
            color: #919191;
            font-size: 12px;
          }
        </style>
      </template>
      <template>
        <span class="subscribe-btn">
          订阅
        </span>
      </template>
    </wx-open-subscribe>
    `;
    $('.js-wechat-subscribe').html(template);
    initWechatConfig();
  });
}


function initWechatConfig() {
  wx.config($jweixin.data('config'));

  const reg = /accept/;
  
  wx.ready(function() {
    var btn = $('.js-wechat-subscribe').find('#subscribe-btn');
    console.log('hongbusi', btn);
    console.log($('#subscribe-btn'));
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
}