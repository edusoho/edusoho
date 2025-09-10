<template>
  <div class="back-to-top" @click="backToTop">
    <i class="iconfont icon-top"></i>
    <span>{{ $t('goods.top') }}</span>
  </div>
</template>

<script>
export default {
  methods: {
    backToTop() {
      document.documentElement.scrollTop = document.body.scrollTop = 0;
    },
  },
};
</script>
