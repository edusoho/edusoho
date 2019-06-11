<template>
  <div id="app">
    <van-nav-bar
      style="z-index: 1001;"
      :class="[{hide: isQrcode}, 'nav-bar']"
      :title="title"
      :left-arrow="showLeftArrow"
      @click-left="backFn()"
    />
    <router-view></router-view>
  </div>
</template>
<script>
import Api from "@/api";
import * as types from "@/store/mutation-types";
import { mapMutations, mapState } from "vuex";

export default {
  data() {
    return {
      showLeftArrow: false,
      isQrcode: false,
      isShare:false
    };
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE
    }),
    backFn() {
      const query = this.$route.query;
      if (query.backUrl) {
        this.$router.push({ path: query.backUrl });
        return;
      }
      if(this.isShare){ //如果是从分享页进来
         this.$router.push({ path: '/'});
         return
      }
       this.$router.go(-1)
    }
  },
  computed: {
    ...mapState(["title"]),
    routerKeepAlive() {
      return this.$route.meta.keepAlive;
    }
  },
  watch: {
    $route: {
      handler(to,from) {
        this.isShare=from.fullPath==='/'?true:false //需要返回首页标记

        const redirect = to.query.redirect || "";

        this.isQrcode = !!to.query.loginToken;

        this.showLeftArrow = ![
          "my",
          "find",
          "learning",
          "prelogin",
          "preview",
          "coupon_receive",
          "share_redirect"
        ].includes(to.name);

        if (redirect === "learning") {
          this.setNavbarTitle("我的学习");
          return;
        }

        this.setNavbarTitle(to.meta.title);
      }
    }
  }
};
</script>
