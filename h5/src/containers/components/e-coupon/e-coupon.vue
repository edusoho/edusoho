<template>
  <div class="coupon-container e-coupon" @click="onSelect">
    <div class="e-coupon__container clearfix e-coupon-single">
      <div :key="index" class="e-coupon__body">
        <div class="e-coupon__header clearfix">
          <span class="e-coupon__price" v-html="priceHtml(coupon)"></span>
          <div class="e-coupon__name">
            <div class="text-overflow text-14 coupon-name">{{ coupon.name || '优惠券' }}</div>
            <!-- 兼容老版本优惠券无有效期功能或者非有效期模式 -->
              <span v-if="!coupon.deadlineMode || coupon.deadlineMode==='time'" class="text-10">{{ timeExpire(coupon.createdTime,coupon.deadline) }}</span>
            <!-- 新版优惠券功能 -->
              <!-- 有效期模式且用户未领取 -->
              <span v-if="coupon.deadlineMode==='day' && !coupon.currentUserCoupon" class="text-10">领取后{{coupon.fixedDay}}天内有效</span>
              <!-- 有效期模式且用户已经领取 -->
              <span v-if="coupon.deadlineMode==='day' && coupon.currentUserCoupon" class="text-10">{{ timeExpire(coupon.createdTime, coupon.currentUserCoupon.deadline) }}</span>
          </div>
          <div class="stamp" v-if="coupon.currentUserCoupon"></div>
          <div class="e-coupon__select-circle" v-if="showSelecet">
            <i class="select-icon" :class="index === active ? 'h5-icon h5-icon-check' : ''"></i>
          </div>
          <span class="coupon-button" @click="couponHandle" v-if="showButton && !coupon.currentUserCoupon">领券</span>
        </div>
        <div class="e-coupon__middle"></div>
        <div class="e-coupon__bottom text-overflow">
          可用范围：{{ scopeFilter(coupon) }}
        </div>
     </div>
    </div>
  </div>
</template>

<script>
import couponMixin from '@/mixins/coupon'

export default {
  name: 'e-coupon',
  mixins: [couponMixin],
  props: {
    showButton: {
      type: Boolean,
      default: true,
    },
    showSelecet: {
      type: Boolean,
      default: false,
    },
    coupon: {
      type: Object,
      default: () => {
        return {}
      }
    },
    index: {
      type: Number,
      default: -1,
    },
    active: {
      type: Number,
      default: -1,
    },
  },
  created(){

  },
  data () {
    return {

    }
  },
  methods: {
    onSelect() {
      if (!this.showSelecet) return

      this.$emit('chooseItem', {
        index: this.index,
        itemData: this.coupon
      })
    },
    couponHandle() {
      this.$emit('couponHandle', this.coupon);
    },
  }
}
</script>

