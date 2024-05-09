<template>
  <div id="app">
    <van-nav-bar
      class="nav-bar--white"
      :class="[
        { hide: isQrcode || hideNavbar },
      ]"
      :title="title"
      :left-arrow="showLeftArrow"
      style="z-index: 1001;"
      :style="{ backgroundColor: $route.meta.bgColor || '#fff'}"
      @click-left="backFn()"
    />
    <router-view v-if="isRouterAlive" />
  </div>
</template>
<script>
import * as types from '@/store/mutation-types';
import { mapMutations, mapState } from 'vuex';

export default {
  data() {
    return {
      showLeftArrow: false,
      isQrcode: false,
      hideNavbar: true,
      isShare: false,
      isRouterAlive: true,
    };
  },
  provide() {
    return {
      reload: this.reload,
      language: this.language
    };
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE,
    }),
    backFn() {
      const query = this.$route.query;
      if (query.backUrl) {
        this.$router.push({ path: query.backUrl });
        return;
      }
      // 从空白页进来，无回退页，直接回退到首页
      if (this.isShare && !this.isFromMall) {
        this.$router.push({ path: '/' });
        return;
      }

      this.$router.go(-1);
    },
    reload() {
      this.isRouterAlive = false;
      this.$nextTick(() => {
        this.isRouterAlive = true;
      });
    }, 
  },
  computed: {
    ...mapState({
      title: 'title',
      isFromMall: state => state.isFromMall,
      settingsName: state => state.settings.name,
      language: state => state.language
    }),
    routerKeepAlive() {
      return this.$route.meta.keepAlive;
    },
  },
  watch: {
    $route: {
      handler(to, from) {
        // 需要返回首页标记
        this.isShare = from.fullPath === '/';

        const redirect = to.query.redirect || '';

        this.isQrcode = !!to.query.loginToken;

        this.hideNavbar = to.meta.hideTitle ? to.meta.hideTitle : false;

        this.showLeftArrow = ![
          'my',
          'find',
          'learning',
          'prelogin',
          'preview',
          'coupon_receive',
          'share_redirect',
        ].includes(to.name);

        if (redirect === 'learning') {
          this.setNavbarTitle('我的学习');
          return;
        }

        const navbarTitle = to.meta.i18n ? this.$t(to.meta.title) : to.meta.title;

        this.setNavbarTitle(navbarTitle);

        document.title = this.settingsName;

      },
    },
  },
};
</script>
