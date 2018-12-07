<template>
  <div class="coupon-container" @click="onSelect">
    <div class="e-coupon__container clearfix e-coupon-single">
      <div :key="index" :class="['e-coupon__body', Number(coupon.unreceivedNum) == 0 ? 'coupon-received-all' : '']">
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
          <span class="coupon-button" v-if="showButton">{{ coupon.currentUserCoupon ? '去使用' : '领券' }}</span>
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
export default {
  name: 'e-coupon',
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
    timeExpire(item) {
      let createdTime = '';
      let deadline = '';

      if (!item.createdTime) {
        deadline = item.deadline.slice(0, 10);
        return `有效期截止：${deadline}`;
      }
      createdTime = item.createdTime.slice(0, 10);
      deadline = item.deadline.slice(0, 10);
      return `${createdTime}至${deadline}`;
    },
    priceHtml(item) {
      const intPrice = parseInt(item.rate);
      let pointPrice = `.${Number(item.rate).toFixed(2).split('.')[1]}`;
      pointPrice = `${pointPrice == 0 ? '' : pointPrice}`;
      const typeText = item.type === 'discount' ? '折' : '元';
      return `${intPrice}<span class="text-14">${pointPrice + typeText}</span>`;
    },
    scopeFilter(item) {
      const { targetType, target } = item

      if (targetType === 'classroom') {
        return target ? target.title : '全部班级';
      }
      if (targetType === 'course' && !target) {
        return target ? target.title : '全部班级';
      }
      if (targetType === 'vip') {
        return '会员'
      }
      return '全部商品';
    },
    onSelect() {
      this.$emit('chooseItem',
      {
        index: this.index,
        itemData: this.coupon
      })
    },
  }
}
</script>

