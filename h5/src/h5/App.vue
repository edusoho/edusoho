<template>
  <div id="app">
    <van-nav-bar
      :class="[
        { hide: (isQrcode || hideNavbar) },
        color === 'white' ? 'nav-bar--white' : 'nav-bar--default'
      ]"
      :title="title"
      :left-arrow="showLeftArrow"
      style="z-index: 1001;"
      @click-left="backFn()"
    />
    <router-view/>
  </div>
</template>
<script>
import Api from '@/api'
import * as types from '@/store/mutation-types'
import { mapMutations, mapState } from 'vuex'

export default {
  data() {
    return {
      showLeftArrow: false,
      isQrcode: false,
      hideNavbar: false,
      isShare: false,
      color: ''
    }
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE
    }),
    backFn() {
      const query = this.$route.query
      if (query.backUrl) {
        this.$router.push({ path: query.backUrl })
        return
      }
      // 从空白页进来，无回退页，直接回退到首页
      if (this.isShare) {
        this.$router.push({ path: '/' })
        return
      }
      this.$router.go(-1)
    }
  },
  computed: {
    ...mapState({
      title: 'title',
      settingsName: state => state.settings.name
    }),
    routerKeepAlive() {
      return this.$route.meta.keepAlive
    }
  },
  watch: {
    $route: {
      handler(to, from) {
        // 需要返回首页标记
        this.isShare = from.fullPath === '/'

        const redirect = to.query.redirect || ''

        this.isQrcode = !!to.query.loginToken

        this.hideNavbar=to.meta.hideTitle ? to.meta.hideTitle : false

        this.color=to.meta.color === 'white'?'white':''

        this.showLeftArrow = ![
          'my',
          'find',
          'learning',
          'prelogin',
          'preview',
          'coupon_receive',
          'share_redirect'
        ].includes(to.name)

        if (redirect === 'learning') {
          this.setNavbarTitle('我的学习')
          return
        }

        this.setNavbarTitle(to.meta.title)
        document.title = this.settingsName
      }
    }
  }
}
</script>
