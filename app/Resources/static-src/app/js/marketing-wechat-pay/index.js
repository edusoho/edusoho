import wx from 'weixin-js-sdk'
import notify from 'common/notify'

const $payBtn = $('.js-weixin-pay')
let payConfig = {}

if (isWechatBrowser()) {
  $.ajax({
    url: '/api/settings/wechat_message_subscribe',
    type: 'GET',
    headers:{
      'Accept':'application/vnd.edusoho.v2+json'
    }
  }).success(function (res) {
    payConfig = res || {}
    initWechatConfig({});
  })
}

// 判断是不是微信环境
function isWechatBrowser() {
  const browser = navigator.userAgent.toLowerCase()

  return browser.match(/MicroMessenger/i) == 'micromessenger'
}

function initWechatConfig() {
  wx.config({
    ...payConfig.paymentResult,
    jsApiList: ['chooseWXPay']
  })

  wx.ready(function() {
    $payBtn.removeClass('disabled')

    $payBtn.on('click', () => {
      wx.chooseWXPay({
        success: () => {
          notify('success', '支付成功')
          window.location.href = payConfig.returnUrl
        },
        cancel: () => {
          notify('info', '支付取消')
        }
      })
    })
  })
}
