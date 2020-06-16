<template>
  <div class="cd-container">
    <div class="product-breadcrumb">首页 / 艺术学概论</div>
    <product-detail :detailData="componentsData"></product-detail>
    <product-info :hasExtension="componentsData.hasExtension"></product-info>
  </div>
</template>

<script>
  import axios from 'axios';
  import productDetail from './product-detail';
  import productInfo from './product-info';
  export default {
    data() {
      return {
        componentsData: {}
      }
    },
    components: {
      productDetail,
      productInfo
    },
    methods: {
      
    },
    created() {
      axios.get('/api/goods/1', {
        headers: { 'Accept': 'application/vnd.edusoho.v2+json'}
      }).then((res) => {
        this.componentsData = res.data;
      });
    }
  }
</script>
