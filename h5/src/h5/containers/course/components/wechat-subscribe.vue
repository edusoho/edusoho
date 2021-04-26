<template>
  <div v-if="supportWechatSubscribe" class="wechat-subscribe">
    <i class="van-icon van-icon-arrow van-nav-bar__arrow" />
    订阅
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
import initSubscribe from '@/utils/wechat-subscribe.js';

export default {
  name: 'WechatSubscribe',

  data() {
    return {
      supportWechatSubscribe: true,
      firstSubscribe: false,
    };
  },

  created() {
    initSubscribe();
    this.firstWechatSubscribe();
  },

  methods: {
    firstWechatSubscribe() {
      const status = localStorage.getItem('first-wechat-subscribe');
      if (status) return;
      localStorage.setItem('first-wechat-subscribe', true);
      this.firstSubscribe = true;
    },
  },
};
</script>
