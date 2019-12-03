<template>
  <div class="switch-code-container">
    <div
      v-if="isLoading"
      class="es-loading es-switch-loading warning default"
      style="width: 80px; height: 80px;">
      <div @click="show=true">
        <span class="spot"/>
        <span class="spot"/>
        <span class="spot"/>
        <img class="code-img" src="static/images/small_white_code.png">
      </div>
      <i class="h5-icon h5-icon-cuowu1 loading-close-icon" @click="closeLoading"/>
    </div>
    <van-popup v-model="show" class="code-popup-body">
      <i class="icon-close h5-icon h5-icon-guanbi" @click="show=false"/>
      <div class="title text-14">扫描下方二维码，授权开启课程通知</div>
      <img v-if="wechatSettings" :src="wechatSettings.official_qrcode" class="code-img">
    </van-popup>
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  props: {
    closeDate: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      isLoading: true,
      show: false
    }
  },
  computed: {
    ...mapState(['wechatSettings'])
  },
  methods: {
    closeLoading() {
      const now = new Date()
      const today = `${now.getFullYear()}-${now.getMonth() + 1}-${now.getDate()}`
      this.isLoading = false
      this.show = false
      localStorage.setItem(this.closeDate, today)
    }
  }
}
</script>
