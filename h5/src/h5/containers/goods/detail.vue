<template>
  <div class="goods-detail">
    <!-- banner -->
    <div class="goods-detail__banner">
      <img :src="details.image">
    </div>
    <!-- 优惠 -->
    <detail-discount :details="details" />
    <!-- 商品名称、价格 -->
    <detail-info :details="details" :currentPlan="currentPlan" />
    <!-- 教学计划、有效期、服务 -->
    <detail-plan :details="details" :currentPlan="currentPlan" :plans="plans" />
  </div>
</template>

<script>
import DetailDiscount from './components/detail-discount';
import DetailInfo from './components/detail-info';
import DetailPlan from './components/detail-plan';
export default {
  data() {
    return {
      plans: [], // 所有学习计划
      currentPlan: {} // 当前学习计划
    }
  },
  components: {
    DetailDiscount,
    DetailInfo,
    DetailPlan
  },
  props: {
    details: {
      type: Object,
      default: () => {}
    }
  },
  watch: {
    details: {
      handler() {
        let specs = this.details.specs;
        let temp = [];
        for (const key in specs) {
          this.$set(specs[key], 'active', false);
          this.$set(specs[key], 'id', key);
          if (key == this.$route.params.id) {
            specs[key].active = true;
            this.currentPlan = specs[key];
          }
          temp.push(specs[key]);
        }
        this.plans = temp;
      }
    }
  }
}
</script>