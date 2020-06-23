<template>
  <div class="goods-detail">
    <!-- banner -->
    <div class="goods-detail__banner">
      <img src="http://devtest.edusoho.cn/files/system/2019/08-07/1747506b1910625434.JPG">
    </div>
    <!-- 优惠 -->
    <detail-discount />
  </div>
</template>

<script>
import DetailDiscount from './components/detail-discount';
export default {
  components: {
    DetailDiscount
  }
}
</script>