<template>
  <div v-if="supportWechatSubscribe" class="wechat-subscribe">
    <!-- <div class="wechat-subscribe-guide" v-if="firstGuide">
      <img src="static/images/course_guide.png" alt="" />
    </div> -->
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
      firstGuide: false,
      templateId: '',
    };
  },

  created() {
    if (!this.isWeixin()) return;
    this.initSubscribe();
    this.firstWechatSubscribe();
  },

  methods: {
    async initSubscribe() {
      const { enable } = await Api.wechatSubscribe();
      if (!enable) return;

      this.templateId = await Api.wechatTemplate();
      if (!this.templateId) return;

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

    isWeixin() {
      const ua = navigator.userAgent.toLowerCase();
      return ua.match(/MicroMessenger/i) == 'micromessenger';
    },

    // clickSubscribe() {
    //   if (this.firstSubscribe) this.firstSubscribe = false;
    //   this.firstWechatGuide();
    // },

    firstWechatSubscribe() {
      const status = localStorage.getItem('first-wechat-subscribe');
      if (status) return;
      localStorage.setItem('first-wechat-subscribe', true);
      this.firstSubscribe = true;
    },

    // firstWechatGuide() {
    //   const status = localStorage.getItem('first-wechat-guide');
    //   if (status) return;
    //   localStorage.setItem('first-wechat-guide', true);
    //   this.firstGuide = true;
    // },
  },
};
</script>
