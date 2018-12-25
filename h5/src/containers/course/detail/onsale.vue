<template>
  <e-panel title="优惠" v-if="unreceivedCoupons.length">
    <van-cell class="course-detail__cell" is-link @click="couponListShow = true">
      <template slot="title">
        <span class="text-12">领券：</span>
        <mini-coupon :item="item" v-for="(item, index) in miniCoupons" :key="index" />
      </template>
    </van-cell>

    <e-popup class="coupon-popup white-background" :show.sync="couponListShow" title="优惠券">
      <coupon v-for="(item, index) in unreceivedCoupons" :key="index" :index="index" :coupon="item" :showButton="true"
        @couponHandle="couponHandle($event)" />
      <div class="coupon-empty" v-show="!unreceivedCoupons.length">
        <img class="empty-img" src='static/images/coupon_empty.png'>
        <div class="empty-text">暂无优惠券</div>
      </div>
    </e-popup>
  </e-panel>
</template>

<script>
import EPopup from '@/components/popup';
import coupon from '@/containers/components/e-coupon/e-coupon';
import miniCoupon from '@/containers/components/e-mini-coupon/e-mini-coupon';
import getCouponMixin from '@/mixins/coupon/getCouponHandler';

export default {
  name: 'onsale',
  mixins: [getCouponMixin],
  components: {
    coupon,
    miniCoupon,
    EPopup,
  },
  props: ['unreceivedCoupons', 'miniCoupons'],
  data () {
    return {
      couponListShow: false,
    }
  }
}
</script>
