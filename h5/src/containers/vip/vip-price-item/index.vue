<template>
  <div class="vip-price-item">
    <div class="vip-price-item__buy-type mb15 text-14">{{ item.time }}</div>
    <div class="red">¥ {{ item.price }}</div>
    <i class="select-icon h5-icon h5-icon-zhengque"></i>
  </div>
</template>

<script>
export default {

  name: 'vip-popup',
  props: {
    active: {
      type: Boolean,
      default: false,
    },
    item: {
      type: Object,
      default: () => {
        return {};
      },
    },
  },
  methods: {
  }
}
</script>
