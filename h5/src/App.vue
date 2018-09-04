<template>
  <div id="app">
    <van-nav-bar :title="title"
      class="nav-bar"
      :left-arrow="showLeftArrow"
      @click-left="$router.go(-1)"/>
    <router-view></router-view>
  </div>
</template>
<script>
import Api from '@/api'
import * as types from '@/store/mutation-types';
import { mapMutations, mapState } from 'vuex';

export default {
  data() {
    return  {
      showLeftArrow: false
    }
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE
    }),
  },
  computed: {
    ...mapState(['title'])
  },
  watch: {
    '$route': {
      handler(to) {
        const redirect = to.query.redirect || '';

        this.showLeftArrow = !['my', 'find', 'learning', 'prelogin', 'preview'].includes(to.name);

        if(redirect === 'learning') {
          this.setNavbarTitle('我的学习')
          return;
        }

        this.setNavbarTitle(to.meta.title)
      }
    }
  }
}
</script>
