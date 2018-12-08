<template>
  <van-tag class="mini-coupon ml10" type="danger">{{ priceHtml(item, false) }}</van-tag>
</template>

<script>
import couponMixin from '@/mixins/coupon'

export default {
  name: 'e-mini-coupon',
  mixins: [couponMixin],
  props: {
    item: {
      type: Object,
      default: () => {
        return {}
      }
    },
  },
}
</script>
