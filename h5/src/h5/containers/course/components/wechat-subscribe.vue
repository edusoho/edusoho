<template>
  <div v-if="isWechatSubscribe" class="wechat-subscribe">
    <div v-if="firstGuide" class="wechat-subscribe-first-guide">
      {{ $t('courseLearning.pleaseClickSubscribe') }}
    </div>

    <!-- <div v-if="secondGuide" class="wechat-subscribe-second-guide">
      <img src="static/images/course_guide.png" alt="" />
    </div> -->

    <i
      :class="['iconfont', isSubscribe ? 'icon-subscribed' : 'icon-subscribe']"
    />
    <wx-open-subscribe
      :template="templateId"
      @success="success"
      @error="subError"
      id="subscribe-btn"
    >
      <script type="text/wxtag-template" slot="style">
        <style>
          .subscribe-btn {
            display: inline-block;
            min-width: 30px;
            color: #fff;
            font-size: 14px;
          }
        </style>
      </script>
      <script type="text/wxtag-template">
        <span class="subscribe-btn">
          {{ $t('courseLearning.subscribe') }}
        </span>
      </script>
    </wx-open-subscribe>
  </div>
</template>

<script>
import wx from 'weixin-js-sdk';
import Api from '@/api';

const reg = /accept/;
const WECHAT_SUBSCRIBE_FIRST_GUIDE = 'WECHAT_SUBSCRIBE_FIRST_GUIDE';
// const WECHAT_SUBSCRIBE_SECOND_GUIDE = 'WECHAT_SUBSCRIBE_SECOND_GUIDE';

export default {
  name: 'WechatSubscribe',

  data() {
    return {
      templateId: '',
      isWechatSubscribe: false,
      firstGuide: false,
      secondGuide: false,
      isSubscribe: false,
    };
  },

  async mounted() {
    if (!this.isWeixin()) return;

    if (!this.isKeyLocalStorage(WECHAT_SUBSCRIBE_FIRST_GUIDE)) {
      this.firstGuide = true;
    }

    const { enable } = await Api.wechatSubscribe();
    if (!enable) return;

    this.templateId = await Api.wechatTemplate();
    if (!this.templateId) return;

    // if (!this.isKeyLocalStorage(WECHAT_SUBSCRIBE_SECOND_GUIDE)) {
    //   this.secondGuide = true;
    // }

    this.$nextTick(res => {
      this.initSubscribe();
    });
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
          this.isWechatSubscribe = true;
        });
      });
    },

    success(e) {
      this.firstGuide = false;
      const subscribeDetails = e.detail.subscribeDetails;
      if (reg.test(subscribeDetails)) {
        this.isSubscribe = true;
        this.$toast(this.$t('courseLearning.subscriptionSucceeded'));
      }
    },

    subError(e) {
      console.log('fail', e.detail);
    },

    isWeixin() {
      const ua = navigator.userAgent.toLowerCase();
      return ua.match(/MicroMessenger/i) == 'micromessenger';
    },

    isKeyLocalStorage(key) {
      const value = localStorage.getItem(key);
      if (value) return true;
      localStorage.setItem(key, true);
      return false;
    },
  },
};
</script>
