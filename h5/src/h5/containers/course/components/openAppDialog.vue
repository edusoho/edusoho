<template>
  <div class="open-app-container">
    <div class="open-app-overlay"></div>
    <div class="open-app-dialog">
      <div class="message">将为您跳转至APP进行学习</div>
      <div class="footer">
        <button @click="cancel()">取消</button>

        <a v-if="isWeixinBrowser || isDingTalkBrowser" :href="appMiddlePageUrl">继续</a>
        <a v-else :href="openAppUrl" @click="openMiddlePage">继续</a>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: ['openAppUrl', 'courseId', 'goodsId'],
  data() {
    return {
      appMiddlePageUrl: `/mobile/downloadMiddlePage?courseId=${this.courseId}&goodsId=${this.goodsId}`,
      isWeixinBrowser: /micromessenger/.test(navigator.userAgent.toLowerCase()),
      isDingTalkBrowser: /ding\s?talk/i.test(navigator.userAgent.toLowerCase()),
      timeout: null
    }
  },mounted() {
    document.addEventListener('visibilitychange', function () {
      // 用户离开了当前页面
      if (document.visibilityState === 'hidden') {
        this.timeout && clearTimeout(this.timeout)
      }
    });
  },
  methods: {
    cancel() {
      this.$emit("cancel")
    },
    openMiddlePage() {
      this.timeout = setTimeout(() => {
        window.location.href = this.appMiddlePageUrl
        this.timeout = null
      }, 1500)
    }
  }
}
</script>
<style lang="scss" scoped>
.open-app-container {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 999999;

  .open-app-overlay {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background-color: rgba(0, 0, 0, 0.3);
    z-index: 1;
  }

  .open-app-dialog {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 10;

    width: 320px;
    height: 120px;
    background-color: #fff;
    font-size: 16px;
    display: flex;
    flex-direction: column;
    border-radius: 16px;
  }

  .message {
    flex: 1;
    text-align: center;
    line-height: 70px;
    color: #37393D;
  }

  .footer {
    display: flex;
    height: 48px;
  }

  button {
    width: 160px;
    height: 48px;
    color: #5E6166;
    border-top: 1px solid #ebedf0;
    font-size: 16px;
    border-radius: 0 0 0 16px;
  }

  a {
    display: block;
    width: 160px;
    height: 48px;
    color: #00BE63;
    text-align: center;
    line-height: 46px;
    border-top: 1px solid #ebedf0;
    border-left: 1px solid #ebedf0;
    text-decoration: none;
    font-size: 16px;
    border-radius: 0 0 16px 0;
  }
}
</style>
