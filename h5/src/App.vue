<template>
  <div id="app">
    <van-nav-bar :title="title" 
      class="nav"
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
