<template>
  <div class="goods-certificate" @click="toCertificate(selectedPlanId)">
    <span><span class="certificate-icon">证</span>证书</span>
    <i class="van-icon van-icon-arrow pull-right" />
  </div>
</template>

<script>
export default {
  props: ['selectedPlanId'],
  methods: {
    toCertificate(id) {
      this.$router.push({ path: `/certificate/list/${id}` });
    },
  },
};
</script>
