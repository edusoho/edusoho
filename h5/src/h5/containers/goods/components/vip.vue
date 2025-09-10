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
    type: {
      type: String,
      default: 'course',
    },
  },

  computed: {
    vipEntryContent() {
      const { vipLevelInfo, vipUser } = this.currentSku;
      const type = this.type == 'course' ? this.$t('goods.course') : this.$t('goods.classroom');

      // 用户是会员，但会员等级不满足课程会员等级要求
      if (
        vipUser &&
        vipUser.level &&
        Number(vipUser.level.seq) < Number(vipLevelInfo.seq)
      ) {
        return `${this.$t('goods.upgrade')}${vipLevelInfo.name}，${this.$t('goods.freeLearningVip')}${type}`;
      }

      return `${this.$t('goods.joinVip')}${vipLevelInfo.name}，${this.$t('goods.freeLearningVip')}${type}`;
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
