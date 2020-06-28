<template>
  <div class="back-to-top">
    <i class="iconfont icon-top" @click="backToTop"></i>
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