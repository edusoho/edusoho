<template>
  <div>
    <img class="find-head-img" :src="headImg" alt="" />
    <div class="find-navbar" :class="`find-navbar-${portal}`">
      <i v-if="portal === 'apps'" class="iconfont icon-search"></i>
      <i class="h5-icon h5-icon-houtui"></i>{{ headTitle }}
    </div>
  </div>
</template>

<script>
import { mapActions, mapState } from "vuex";
export default {
  data(){
    return{
    }
  },
  props: {
    // 当前终端 apps、miniprogram、h5
    portal: {
      type: String,
      default: ''
    }
  },
  computed:{
    ...mapState([
      "settings"
    ]),
    headImg() {
      switch (this.portal) {
        case "miniprogram":
          return "static/images/miniprogram_head.jpg";
        case "apps":
          return "static/images/app_head.jpg";
        default:
          return "static/images/find_head_url.jpg";
      }
    },
    headTitle() {
      switch (this.portal) {
        case "miniprogram":
          return "小程序";
        case "apps":
          return this.settings.name;
        default:
          return "微网校";
      }
    }
  }
}
</script>
