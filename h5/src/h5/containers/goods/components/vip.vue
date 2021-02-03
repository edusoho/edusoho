<template>
  <div class="goods-vip" @click="clickGoToVip">
    <div class="goods-vip__left">
      <img class="vip-icon" :src="currentSku.vipLevelInfo.icon" alt="" />
      加入{{ currentSku.vipLevelInfo.name }}，免费学习此门课程
    </div>
    <i class="van-icon van-icon-arrow" />
  </div>
</template>

<script>
export default {
  props: {
    currentSku: {
      type: Object,
      default: () => {},
    },
  },

  methods: {
    clickGoToVip() {
      this.$router.push({
        path: '/vip',
        query: { id: this.currentSku.vipLevelInfo.id },
      });
    },
  },
};
</script>
