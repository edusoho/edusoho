<template>
  <div class="goods-vip" @click="clickGoToVip">
    <div class="goods-vip__left">
      <img class="vip-icon" :src="currentSku.vipLevelInfo.icon" alt="" />
      {{ vipEntryContent }}
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

  computed: {
    vipEntryContent() {
      const { vipLevelInfo, vipUser } = this.currentSku;

      // 用户是会员，但会员等级不满足课程会员等级要求
      if (vipUser && vipUser.level.seq < vipLevelInfo.seq) {
        return `升级为${vipLevelInfo.name}，免费学习此门课程`;
      }

      return `加入${vipLevelInfo.name}，免费学习此门课程`;
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
