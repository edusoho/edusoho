<template>
  <div class="coupon-item">
    <div class="coupon-item__top">header</div>
    <div class="coupon-item__middle" />
    <div class="coupon-item__bottom">header</div>
  </div>
</template>

<script>
export default {
  name: 'CouponItem'
}
</script>

<style lang="less" scoped>
.coupon-item {
  position: relative;
  width: 100%;
  height: 100%;
  color: #fff;

  &::before,
  &::after {
    content: "";
    position: absolute;
    top: 70px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #fff;
  }

  &::before {
    left: -6px;
  }

  &::after {
    right: -6px;
  }

  &__top {
    height: 70%;
    background-color: #ff6969;
    border-top-right-radius: 2px;
    border-top-left-radius: 2px;
  }

  &__middle {
    margin-top: -6px;
    margin-bottom: -1px;
    height: 6px;
    background-image: url('/static-dist/app/img/vue/a.png');
    background-size: 16px 6px;
    background-position-x: 6px;
  }

  &__bottom {
    height: 30%;
    font-size: 10px;
    background-color: #ff5353;
    border-bottom-right-radius: 2px;
    border-bottom-left-radius: 2px;
  }
}
</style>
