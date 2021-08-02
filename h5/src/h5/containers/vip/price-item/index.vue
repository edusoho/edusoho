<template>
  <div
    class="vip-price-item"
    :class="{ active: isActive }"
    @click="clickPrice()"
  >
    <span class="vip-price-item__new" v-if="item.type === 'first'">
      {{ item.tag }}
    </span>
    <div class="vip-price-item__title">{{ item.title }}</div>
    <div class="vip-price-item__price">{{ price }}</div>
  </div>
</template>

<script>
export default {
  name: 'PriceItem',
  props: {
    item: {
      type: Object,
      default: () => {
        return {};
      },
    },
    activePriceId: [Number, String],
  },
  computed: {
    price() {
      const { currency, amount, coinAmount, coinName } = this.item.price;
      if (currency == 'RMB') {
        return `${amount}${this.$t('vip.cny')}`;
      }
      return `${coinAmount}${coinName}`;
    },

    isActive() {
      return this.item.id == this.activePriceId;
    },
  },
  methods: {
    clickPrice() {
      this.$emit('clickPriceItem', this.item);
    },
  },
};
</script>
