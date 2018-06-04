<template>
  <div id="app">
    <van-nav-bar :title="title" 
      :class="navClass"
      :left-arrow="showLeftArrow" 
      @click-left="$router.go(-1)"/>
    <router-view></router-view>
  </div>
</template>
<script>
export default {
  data() {
    return  {
      title: '',
      showLeftArrow: false
    }
  },
  computed: {
    navClass() {
      return ['learning', 'find'].includes(this.$route.name) ? 'blue-nav' :  ''
    }
  },
  watch: {
    '$route': {
      handler(from) {
        ['my', 'find', 'learning'].includes(from.name)
          ? this.showLeftArrow = false
          : this.showLeftArrow = true;

        this.title = from.meta.title;
      }
    }
  }
}
</script>
