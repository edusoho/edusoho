<template>
  <div class="e-coupon">
    <div class="e-coupon__title">优惠券</div>
    <div :class="['e-coupon__container', 'clearfix', couponNum]" v-show="coupons.length">
      <div :class="['e-coupon__body', Number(item.unreceivedNum) == 0 ? 'coupon-received-all' : '']" v-for="item in coupons">
        <div class="e-coupon__header clearfix">
          <span class="e-coupon__price" v-html="priceHtml(item)"></span>
          <div class="e-coupon__name" v-show="coupons.length == 1">
            <div class="text-overflow text-14 coupon-name">{{ item.name }}</div>
            <span class="text-10">{{ timeExpire(item) }}</span>
          </div>
          <div :class="[item.currentUserCoupon ? 'coupon-received' : 'coupon-unreceived']">
            <div class="stamp"></div>
            <a href="javascript:0;" class="coupon-button">{{ item.currentUserCoupon ? '去使用' : '领券' }}</a>
          </div>
        </div>
        <div class="e-coupon__middle"></div>
        <div class="e-coupon__bottom text-overflow">
          可用范围：{{ scopeFilter(item.targetType) }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      coupons: {
        type: Array,
        default: []
      },
    },
    computed: {
      couponNum() {
        return this.coupons.length > 1 ? 'e-coupon-multi' : 'e-coupon-single';
      }
    },
    methods: {
      scopeFilter(type) {
        if (type === 'class') {
          return '全部班级';
        } else if (type === 'course') {
          return '全部课程';
        }
        return '全站';
      },
      timeExpire(item) {
        const createdTime = item.createdTime.slice(0, 10);
        const deadline = item.deadline.slice(0, 10);
        return `${createdTime}至${deadline}`;
      },
      priceHtml(item) {
        const intPrice = parseInt(item.rate);
        const pointPrice = Number(item.rate).toFixed(2).split('.')[1];
        const typeText = item.type === 'discount' ? '折' : '元';
        return `${intPrice}<span class="text-14">.${pointPrice + typeText}</span>`;
      }
    }
  }
</script>
