<template>
  <div class="relative e-coupon__body">
    <img :src="currentBackgroundImg[0]" :srcset="currentBackgroundImg[1]" class="w-full" />
    
    <div class="absolute top-0 bottom-0 left-0 right-0 flex items-center justify-between pl-16 pr-16"
      :class="[num > 1 && 'pr-16']">
      <div class="flex flex-col justify-center text-text-1" style="height: 100%;">
        <div class="pt-4 text-24" v-html="priceHtml(item)" />
        <div class="text-10">{{ scopeFilter(item) }}</div>
      </div>

      <div v-if="num == 1" class="flex flex-col justify-center h-full text-text-1 w-148">
        <div class="mb-4 font-medium text-overflow text-14">{{ item.name }}</div>
        
        <!-- 兼容老版本优惠券无有效期功能或者非有效期模式 -->
        <span v-if="!item.deadlineMode || item.deadlineMode === 'time'" class="font-normal text-12">
          {{ timeExpire(item.createdTime, item.deadline) }}
        </span>
        
        <!-- 新版优惠券功能，有效期模式且用户未领取 -->
        <span v-if="item.deadlineMode === 'day' && !item.currentUserCoupon" class="font-normal text-12">
          {{ $t('e.validWithinDayAfterReceiving', { number: item.fixedDay }) }}
        </span>
        
        <!-- 有效期模式且用户已经领取 -->
        <span v-if="item.deadlineMode === 'day' && item.currentUserCoupon" class="font-normal text-12">
          {{ timeExpire(item.createdTime, item.currentUserCoupon.deadline) }}
        </span>
      </div>

      <div v-if="!feedback" class="coupon-button">{{ $t('e.getCoupons') }}</div>
      <div v-else-if="['unreceive', 'unused'].includes(couponStatus)" :class="['coupon-button', couponStatus]" @click="handleClick(item)">
        {{ item.currentUserCoupon ? $t('e.toUse') : $t('e.getCoupons') }}
      </div>
      <div v-else style="color: transparent;">{{ $t('e.getCoupons') }}</div>
    </div>
  </div>
</template>

<script>
import couponMixin from '@/mixins/coupon';

// num === 1 单张优惠券
// feedback 是否需要接受操作反馈
export default {
  mixins: [couponMixin],
  props: ['item', 'num', 'feedback'],
  computed: {
    couponStatus() {
      if (!this.feedback) return 'unreceive';

      const { currentUserCoupon, unreceivedNum } = this.item;

      if (currentUserCoupon && ['used', 'using'].includes(currentUserCoupon.status)) {
        return 'used';
      }

      if (currentUserCoupon && currentUserCoupon.status === 'receive') {
        return 'unused';
      }

      if (!currentUserCoupon && unreceivedNum == 0) {
        return 'finished';
      }

      if (!currentUserCoupon && unreceivedNum > 0) {
        return 'unreceive';
      }
      
      return 'unreceive'
    },
    currentBackgroundImg() {
      const baseUrl = 'static/images/coupon';
      const imgName = this.num === 1 ? `s-${this.couponStatus}` : this.couponStatus

      return [`${baseUrl}/${imgName}.png`, `${baseUrl}/${imgName}@2x.png`];
    }
  },
};
</script>
