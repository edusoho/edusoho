<template>
  <div class="prelogin">
    <img class='prelogin-img' src="/static/images/noLoginEmpty.png"></image>
    <span class='prelogin-text'>登录后查看更多信息</span>
    <van-button type="default" 
      class="prelogin-btn" @click.native="goLogin">立即登录</van-button>
  </div>
</template>
<script>
export default {
  methods: {
    goLogin() {
      this.$router.push({
        name: 'login',
        query: {
          redirect: this.$route.query.redirect || ''
        }
      })
    }
  }
}
</script>
