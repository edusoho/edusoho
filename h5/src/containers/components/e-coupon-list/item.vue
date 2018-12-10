<template>
  <div :class="['e-coupon__body', couponStatus]">
    <div class="e-coupon__header clearfix">
      <span class="e-coupon__price" v-html="priceHtml(item)"></span>
      <div class="e-coupon__name" v-show="num == 1">
        <div class="text-overflow text-14 coupon-name">{{ item.name }}</div>
        <span class="text-10">{{ timeExpire(item) }}</span>
      </div>
      <div class="stamp" v-if="!(item.unreceivedNum != 0 && !item.currentUserCoupon)"></div>
      <span class="coupon-button" @click="handleClick(item)">{{ item.currentUserCoupon ? '去使用' : '领券' }}</span>
    </div>
    <div class="e-coupon__middle"></div>
    <div class="e-coupon__bottom text-overflow">
      可用范围：{{ scopeFilter(item) }}
    </div>
  </div>
</template>

<script>
  import couponMixin from '@/mixins/coupon'

  export default {
    props: ['item', 'num', 'index'],
    computed: {
      couponStatus() {
        let currentUserCoupon = this.item.currentUserCoupon;
        if (this.item.unreceivedNum == 0 && !currentUserCoupon) {
          return 'coupon-received-all';
        }
        if (currentUserCoupon && currentUserCoupon.status === 'used') {
          return 'coupon-used'
        }
      }
    },
    mixins: [couponMixin],
  }
</script>
