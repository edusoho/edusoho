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

export default {
  data() {
    return  {
      title: '',
      showLeftArrow: false
    }
  },
  watch: {
    '$route': {
      handler(to) {
        const redirect = to.query.redirect || '';

        this.showLeftArrow = !['my', 'find', 'learning', 'prelogin'].includes(to.name);

        if(redirect === 'learning') {
          this.title = '我的学习';
          return;
        }

        this.title = to.meta.title;
      }
    }
  }
}
</script>
