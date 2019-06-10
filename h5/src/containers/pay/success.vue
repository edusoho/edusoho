<template>
  <div class="pay-success-container">
    <div class="pay-success-text">
      <i class="h5-icon h5-icon-check"></i>
      <span class="text-18 success-text">支付成功！</span>
      <div class="mt5 text-14 color-red">为享受更好的教学服务，建议您开启课程通知。</div>
    </div>
    <div class="qrcode-img-container" v-if="wechatSettings">
      <img :src="wechatSettings.official_qrcode" alt="">
    </div>
    <div class="press-img-container">
      <img src="static/images/wechat_press.png" alt="">
      <div class="press-text text-14">请长按二维码开启课程通知</div>
    </div>
    <van-button type="primary" class="btn-back-course" @click="backToCourse">返回课程</van-button>
  </div>
</template>

<script>
import Api from '@/api'
import { mapState } from 'vuex';

export default {
  data () {
    return {
      targetId: this.$route.query.targetId || '',
      targetType: this.$route.query.targetType || ''
    };
  },
  computed: {
    ...mapState(['wechatSwitch', 'wechatSettings']),
  },
  // created() {
  //   if (!this.wechatSwitch) {
  //     this.$router.replace({
  //       path: '/'
  //     })
  //   }
  // },
  methods: {
    backToCourse() {
      this.$router.replace({
        path: `/${this.targetType}/${this.targetId}`,
      })
    }
  }
}
</script>
