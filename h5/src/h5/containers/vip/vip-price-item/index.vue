<template>
  <div class="vip-price-item" @click="handleClick">
    <div class="vip-price-item__buy-type mb15 text-14">{{ item.time }}</div>
    <div class="red">¥ {{ item.price }}</div>
    <i class="select-icon h5-icon h5-icon-zhengque"></i>
  </div>
</template>

<script>
export default {

  name: 'vip-popup',
  props: {
    item: {
      type: Object,
      default: () => {
        return {};
      },
    },
    index: {
      type: Number,
      default: -1,
    },
    vipBuyType: {
      type: String,
      default: 'month'
    }
  },
  created() {
    // 默认选择一年或12个月
    const year = this.vipBuyType === 'year';
    if ((!year && this.index === 2) || (year && this.index === 0)) {
      this.handleClick();
    }
  },
  methods: {
    handleClick() {
      this.$emit('selectItem', {
        num: this.item.num,
        unit: this.item.unit
      })
    }
  }
}
</script>
