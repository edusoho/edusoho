<template>
  <div class="cd-container">
    <div class="product-breadcrumb">首页 / 艺术学概论</div>
    <product-detail></product-detail>
    <product-info></product-info>
  </div>
</template>

<script>
  import productDetail from './product-detail';
  import productInfo from './product-info';
  export default {
    data() {
      return {
    
      }  
    },
    components: {
      productDetail,
      productInfo
    },
    methods: {
      
    }
  }
</script>
