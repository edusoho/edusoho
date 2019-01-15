<template>
  <e-panel title="优惠">
    <!-- 砍价 -->
    <van-cell v-if="activities.cut" class="course-detail__cell" is-link @click="">
      <template slot="title">
        <span class="text-12">砍价：</span>
        <van-tag class="ml5" style="background-color: #ffaa00">砍价</van-tag>
        <span class="text-12 dark">最后48小时砍价活动，最低可砍至0元！</span>
      </template>
    </van-cell>
    <!-- 拼团 -->
    <van-cell v-if="activities.groupon" class="course-detail__cell" is-link @click="">
      <template slot="title">
        <span class="text-12">拼团：</span>
        <van-tag class="ml5" style="background-color: #ffaa00">拼团</van-tag>
        <span class="text-12 dark">跟好友一起买更划算哦！</span>
      </template>
    </van-cell>
    <!-- 优惠券 -->
    <van-cell v-if="unreceivedCoupons.length" class="course-detail__cell" is-link @click="couponListShow = true">
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
  props: ['unreceivedCoupons', 'miniCoupons', 'activities'],
  data () {
    return {
      couponListShow: false,
    }
  }
}
</script>
