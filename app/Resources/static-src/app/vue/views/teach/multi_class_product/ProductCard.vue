<template>
  <div class="product-card">
    <div class="clearfix">
      <div class="pull-left">
        <div class="product-card__title">默认产品</div>
        <div class="product-card__remark">系统默认产品包</div>
      </div>
      <div class="pull-right">
        编辑、删除
      </div>
    </div>
    <a-row class="cd-mt24">
      <a-col :span="6">
        <div class="gray-darker text-24">240000</div>
        <div class="gray-darker text-14 cd-mt4">预估收入</div>
      </a-col>
      <a-col :span="6">
        <div class="gray-darker text-24">240000</div>
        <div class="gray-darker text-14 cd-mt4">学习人数</div>
      </a-col>
      <a-col :span="6">
        <div class="gray-darker text-24">240000</div>
        <div class="gray-darker text-14 cd-mt4">总课时</div>
      </a-col>
      <a-col :span="6">
        <div class="gray-darker text-24">240000</div>
        <div class="gray-darker text-14 cd-mt4">班课</div>
      </a-col>
    </a-row>
  </div>
</template>

<script>
  export default {
    name: '',
    props: {},
    data () {
      return {
      };
    },
    methods: {
    }
  }
</script>

<style lang="less">
.product-card {
  background-color: red;
}
  // .product-card {
  //   // padding: @spacing-6x;
  //   box-shadow: 0 0 16px 0 rgba(0,0,0,0.10);
  //   background-color: #fff;
  //   border-radius: 12px;
  //   &__title {
  //     font-size: 18px;
  //     // color: @gray-darker;
  //     font-weight: 500;
  //   }
  //   &__remark {
  //     // margin-top: @spacing-2x;
  //     // color: @gray;
  //     font-size: 14px;
  //   }
  // }
</style>