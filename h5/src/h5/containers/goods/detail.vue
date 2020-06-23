<template>
  <div class="goods-detail">
    <!-- banner -->
    <div class="goods-detail__banner">
      <img src="http://devtest.edusoho.cn/files/system/2019/08-07/1747506b1910625434.JPG">
    </div>
    <!-- 优惠 -->
    <detail-discount />
    <!-- 商品名称、价格 -->
    <detail-info />
  </div>
</template>

<script>
import DetailDiscount from './components/detail-discount';
import DetailInfo from './components/detail-info';
export default {
  components: {
    DetailDiscount,
    DetailInfo
  }
}
</script>