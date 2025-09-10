<template>
  <div class="prelogin">
    <img class="prelogin-img" src="static/images/noLoginEmpty.png" />
    <span class="prelogin-text">{{ $t('btn.viewAfterLogin') }}</span>
    <van-button type="default" class="prelogin-btn" @click.native="goLogin">{{ $t('btn.loginNow') }}</van-button>
  </div>
</template>
<script>

export default {
  methods: {
    goLogin() {
      this.$router.push({
        name: 'login',
        query: {
          redirect: this.$route.query.redirect || '/'
        }
      })
    }
  }
}
</script>
