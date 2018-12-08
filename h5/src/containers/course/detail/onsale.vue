<template>
  <e-panel title="优惠" v-if="unreceivedCoupons.length">
    <van-cell class="course-detail__cell" is-link @click="couponListShow = true">
      <template slot="title">
        <span class="text-12">领券：</span>
        <mini-coupon :item="item" v-for="(item, index) in miniCoupons" :key="index" />
      </template>
    </van-cell>
    <van-popup class="coupon-popup white-background" v-model="couponListShow" position="bottom">
      <div class="coupon-popup__title">
        <span class="name">优惠券</span>
        <i class="icon h5-icon h5-icon-guanbi" @click="couponListShow = false"></i>
      </div>
      <div class="coupon-popup__content">
        <coupon v-for="(item, index) in unreceivedCoupons" :key="index" :coupon="item" />
      </div>
      <div class="coupon-empty" v-show="!unreceivedCoupons.length">
        <img class="empty-img" src='static/images/coupon_empty.png'>
        <div class="empty-text">暂无优惠券</div>
      </div>
    </van-popup>
  </e-panel>
</template>

<script>
import coupon from '@/containers/components/e-coupon/e-coupon.vue';
import miniCoupon from '@/containers/components/e-mini-coupon/e-mini-coupon.vue';

export default {
  name: 'onsale',
  components: {
    coupon,
    miniCoupon,
  },
  props: ['unreceivedCoupons', 'miniCoupons'],
  data () {
    return {
      couponListShow: false,
    }
  }
}
</script>
