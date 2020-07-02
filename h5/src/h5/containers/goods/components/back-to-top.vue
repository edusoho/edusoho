<template>
  <div class="back-to-top" @click="backToTop">
    <i class="iconfont icon-top"></i>
    <span>顶部</span>
  </div>
</template>

<script>
export default {
  methods: {
    backToTop() {
      document.documentElement.scrollTop = 0;
    }
  }
}
</script>