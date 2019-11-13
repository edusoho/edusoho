<template>
  <div :class="['e-coupon__body', couponStatus]">
    <div class="e-coupon__header clearfix">
      <span class="e-coupon__price" v-html="priceHtml(item)"></span>
      <div class="e-coupon__name" v-show="num == 1">
        <div class="text-overflow text-14 coupon-name">{{ item.name }}</div>
         <!-- 兼容老版本优惠券无有效期功能或者非有效期模式 -->
          <span v-if="!item.deadlineMode || item.deadlineMode==='time'" class="text-10">{{ timeExpire(item.createdTime,item.deadline) }}</span>
          <!-- 新版优惠券功能 -->
            <!-- 有效期模式且用户未领取 -->
            <span v-if="item.deadlineMode==='day' && !item.currentUserCoupon" class="text-10">领取后{{item.fixedDay}}天内有效</span>
            <!-- 有效期模式且用户已经领取 -->
            <span v-if="item.deadlineMode==='day' && item.currentUserCoupon" class="text-10">{{ timeExpire(item.createdTime, item.currentUserCoupon.deadline) }}</span>
      </div>
      <div v-if="feedback">
        <div class="stamp" v-if="!(item.unreceivedNum != 0 && !item.currentUserCoupon)"></div>
        <span class="coupon-button" @click="handleClick(item)">{{ item.currentUserCoupon ? '去使用' : '领券' }}</span>
      </div>
      <div v-else>
        <span class="coupon-button">领券</span>
      </div>
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
    props: ['item', 'num', 'feedback'],
    computed: {
      couponStatus() {
        // 后台不判断优惠券状态
        if (!this.feedback) return '';
        let currentUserCoupon = this.item.currentUserCoupon;
        if (this.item.unreceivedNum == 0 && !currentUserCoupon) {
          return 'coupon-received-all';
        }
        if (currentUserCoupon &&
          (currentUserCoupon.status === 'used' || currentUserCoupon.status === 'using')) {
          return 'coupon-used'
        }
      }
    },
    mixins: [couponMixin],
  }
</script>
