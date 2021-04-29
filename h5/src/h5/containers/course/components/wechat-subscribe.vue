<template>
  <div v-if="supportWechatSubscribe" class="wechat-subscribe">
    <div class="wechat-subscribe-guide" @click="clickSubscribe">
      <img src="static/images/course_guide.png" alt="" />
    </div>
    <i class="iconfont icon-subscribe" />
    <div v-if="firstSubscribe" class="wechat-subscribe-popover">
      请点此订阅微信通知
    </div>
    <wx-open-subscribe :template="templateId" id="subscribe-btn">
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
const reg = /accept/;

export default {
  name: 'WechatSubscribe',

  data() {
    return {
      supportWechatSubscribe: false,
      firstSubscribe: false,
      templateId: '',
    };
  },

  created() {
    const browser = navigator.userAgent.toLowerCase();
    console.log(browser.match(/MicroMessenger/i) != 'micromessenger');
    if (browser.match(/MicroMessenger/i) != 'micromessenger') return;
    console.log('debugger');
    this.initSubscribe();
    this.firstWechatSubscribe();
  },

  methods: {
    async initSubscribe() {
      const { enable } = await Api.wechatSubscribe();
      if (!enable) return;

      this.templateId = await Api.wechatTemplate();
      if (!this.templateId) {
        console.log('template 为空');
        return;
      }

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

        this.supportWechatSubscribe = true;

        wx.ready(() => {
          const btn = document.getElementById('subscribe-btn');
          const that = this;
          btn.addEventListener('success', function(e) {
            console.log('success', e.detail);
            const subscribeDetails = e.detail.subscribeDetails;
            if (reg.test(subscribeDetails)) {
              that.$toast('订阅成功');
            }
          });
          btn.addEventListener('error', function(e) {
            console.log('fail', e.detail);
          });
        });
      });
    },

    clickSubscribe() {
      if (this.firstSubscribe) this.firstSubscribe = false;
      console.log('点击了');
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
