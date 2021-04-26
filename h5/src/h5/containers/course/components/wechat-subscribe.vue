<template>
  <div
    v-if="supportWechatSubscribe"
    class="wechat-subscribe"
    @click="clickSubscribe"
  >
    <i class="iconfont icon-subscribe" />
    <div v-if="firstSubscribe" class="wechat-subscribe-popover">
      请点此订阅微信通知
    </div>
    <wx-open-subscribe
      template="gd7YkJSa2zh5k0z7O3PBPMosQmGS6zex8bumXbzHg5U"
      id="subscribe-btn"
    >
      <script type="text/wxtag-template" slot="style">
        <style>
          .subscribe-btn {
            color: #fff;
            font-size: 14px;
          }
        </style>
      </script>
      <script type="text/wxtag-template">
        <span class="subscribe-btn">
          订阅
        </span>
      </script>
    </wx-open-subscribe>
  </div>
</template>

<script>
import wx from 'weixin-js-sdk';
import Api from '@/api';

export default {
  name: 'WechatSubscribe',

  data() {
    return {
      supportWechatSubscribe: true,
      firstSubscribe: false,
    };
  },

  created() {
    this.initSubscribe();
    this.firstWechatSubscribe();
  },

  methods: {
    initSubscribe() {
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
        });
      });
    },

    clickSubscribe() {
      if (this.firstSubscribe) this.firstSubscribe = false;
    },

    firstWechatSubscribe() {
      const status = localStorage.getItem('first-wechat-subscribe');
      if (status) return;
      localStorage.setItem('first-wechat-subscribe', true);
      this.firstSubscribe = true;
    },
  },
};
</script>
