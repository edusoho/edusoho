<template>
  <div class="switch-code-container">
    <div v-if="isLoading"
      class="es-loading es-switch-loading warning default"
      style="width: 80px; height: 80px;">
      <div @click="show=true">
        <span class="spot"></span>
        <span class="spot"></span>
        <span class="spot"></span>
        <img class="code-img" src='static/images/small_white_code.png'>
      </div>
      <i class="h5-icon h5-icon-cuowu1 loading-close-icon" @click="closeLoading"></i>
    </div>
    <van-popup v-model="show" class="code-popup-body">
      <i class="icon-close h5-icon h5-icon-guanbi" @click="show=false"></i>
      <div class="title text-14">扫描下方二维码，授权开启课程通知</div>
      <img class="code-img" v-if="wechatSettings" :src='wechatSettings.official_qrcode'>
    </van-popup>
  </div>
</template>

<script>
  import { mapState } from 'vuex';

  export default {
    data() {
      return {
        isLoading: true,
        show: false
      }
    },
    computed: {
      ...mapState(['wechatSettings']),
    },
    methods: {
      closeLoading() {
        this.isLoading = false;
        this.show = false;
      }
    }
  }
</script>