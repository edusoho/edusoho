<template>
  <div class="goods-vip" @click="clickGoToVip">
    <div class="goods-vip__left">
      <img class="vip-icon" :src="currentSku.vipLevelInfo.icon" alt="" />
      {{ currentSku.vipLevelInfo.name }}免费学
    </div>
    <i class="van-icon van-icon-arrow pull-right" />
  </div>
</template>

<script>
import { mapState } from 'vuex';

export default {
  props: {
    currentSku: {
      type: Object,
      default: () => {},
    },
  },

  computed: {
    ...mapState(['vipSwitch']),
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
