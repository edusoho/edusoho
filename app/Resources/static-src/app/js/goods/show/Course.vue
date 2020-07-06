<template>
  <div class="cd-container">
    <div class="product-breadcrumb">首页 / 艺术学概论</div>
    <product-detail :detailData="componentsData" :currentPlan="currentPlan" @changePlan="changePlan"></product-detail>
    <course-info :hasExtension="componentsData.hasExtension" :description="componentsData.description"></course-info>
  </div>
</template>

<script>
  import axios from 'axios';
  import ProductDetail from './product-detail';
  import CourseInfo from './course-info';
  export default {
    data() {
      return {
        componentsData: {},
        currentPlan: {}
      }
    },
    components: {
      ProductDetail,
      CourseInfo
    },
    methods: {
      changePlan(id) {
        let data = this.componentsData;
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
      let goodsId = window.location.pathname.replace(/[^0-9]/ig, ""); // goods id
      axios.get('/api/goods/' +　goodsId, {
        headers: { 'Accept': 'application/vnd.edusoho.v2+json'}
      }).then((res) => {
        let data = res.data;
        for (const key in data.specs) {
          this.$set(data.specs[key], 'active', false);
          this.$set(data.specs[key], 'id', key);
          if (key == goodsId) {
            this.$set(data.specs[key], 'active', true);
            this.currentPlan = data.specs[key];
          }
        }
        this.componentsData = data;
      });
    }
  }
</script>
