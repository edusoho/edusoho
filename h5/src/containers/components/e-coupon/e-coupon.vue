<template>
  <div class="coupon-container e-coupon" @click="onSelect">
    <div class="e-coupon__container clearfix e-coupon-single">
      <div :key="index" class="e-coupon__body">
        <div class="e-coupon__header clearfix">
          <span class="e-coupon__price" v-html="priceHtml(coupon)"></span>
          <div class="e-coupon__name">
            <div class="text-overflow text-14 coupon-name">{{ coupon.name || '优惠券' }}</div>
            <span class="text-10">{{ timeExpire(coupon) }}</span>
          </div>
          <div class="stamp" v-if="coupon.currentUserCoupon"></div>
          <div class="e-coupon__select-circle" v-if="showSelecet">
            <i class="select-icon" :class="index === active ? 'h5-icon h5-icon-check' : ''"></i>
          </div>
          <span class="coupon-button" @click="couponHandle(coupon)" v-if="showButton && !coupon.currentUserCoupon">领券</span>
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
    couponHandle(coupon, index) {
      this.$emit('couponHandle', coupon);
    },
  }
}
</script>

