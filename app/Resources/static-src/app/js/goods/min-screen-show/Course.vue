<template>
  <div class="goods goods-course">

    <!-- banner -->
    <div class="goods-banner">
      <img :src="details.image">
    </div>

    <!-- 优惠活动 -->
    <goods-discount />

    <!-- 名称、价格 -->
    <goods-information :details="details" :currentPlan="currentPlan" />

    <!-- 教学计划、有效期、承诺服务 -->
    <goods-specs :details="details" :currentPlan="currentPlan" @changePlan="changePlan" />

  </div>
</template>

<script>
import axios from 'axios';
import GoodsDiscount from './components/goods-discount'; // 优惠活动
import GoodsInformation from './components/goods-information'; // 名称、价格、在学人数
import GoodsSpecs from './components/goods-specs'; // 学习计划、有效期、承诺服务

export default {
  data() {
    return {
      details: {},
      currentPlan: {}
    }
  },
  components: {
    GoodsDiscount,
    GoodsInformation,
    GoodsSpecs
  },
  methods: {
    // 获取商品信息
    getGoodsDetails() {
      let routerNum = 2; // goods id
      axios.get('/api/goods/1', {
        headers: { 'Accept': 'application/vnd.edusoho.v2+json'}
      }).then((res) => {
        let data = res.data;
        for (const key in data.specs) {
          this.$set(data.specs[key], 'active', false);
          this.$set(data.specs[key], 'id', key);
          if (key == routerNum) {
            this.$set(data.specs[key], 'active', true);
            this.currentPlan = data.specs[key];
          }
        }
        this.details = data;
      });
    },
    changePlan(id) {
      let data = this.details;
      for (const key in data.specs) {
        this.$set(data.specs[key], 'active', false);
        if (key == id) {
          this.$set(data.specs[key], 'active', true);
          this.currentPlan = data.specs[key];
        }
      }
      this.details = data;
    }
  },
  created() {
    this.getGoodsDetails();
  }
}
</script>