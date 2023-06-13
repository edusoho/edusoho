<template>
  <div
    class="flex flex-col justify-between price-item"
    :class="{ active: isActive }"
    @click="clickPrice()"
  >
    <span class="first-tag" v-if="item.type === 'first'">
      {{ item.tag }}
    </span>
    <div class="font-bold text-14" style="color: #000;">{{ item.title }}</div>
    <div class="flex items-center justify-between">
      <div class="text-14 mr-20" style="color: #FF7A34;">{{ price }}</div>
      <van-radio-group :value="activePriceId">
        <van-radio :name="item.id" checked-color="#EAB86A" :icon-size="16"></van-radio>
      </van-radio-group>
    </div>
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

<style lang="scss" scoped>
  .price-item {
    position: relative;
    height: 80px;
    padding: 12px;
    background-color: #fff;
    border-radius: 8px;
    border: solid 2px transparent;
  }

  .first-tag {
    position: absolute;
    right: -2px;
    top: -11px;
    height: 22px;
    padding: 0 4px;
    color: #000;
    font-weight: 400;
    text-align: center;
    line-height: 22px;
    font-size: 12px;
    background-color: #EAB86A;
    border-radius: 6px;
  }

  .active {
    background-color: #FFE9CC;
    border-color: #E7B15C;
  }
</style>
