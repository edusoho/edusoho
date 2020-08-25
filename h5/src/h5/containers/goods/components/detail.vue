<template>
  <div class="detail-info" v-if="goods.id">
    <p class="detail-info__title text-overflow">{{ goods.title }}</p>

    <div
      v-if="goods.discount && currentSku.price != 0"
      class="detail-info__price"
    >
      <div class="clearfix">
        <div class="pull-left">
          优惠价
          <span
            v-if="currentSku.displayPriceObj.currency === 'RMB'"
            class="price"
            >{{ currentSku.displayPriceObj.amount | formatPrice }}元
          </span>
          <span
            v-if="currentSku.displayPriceObj.currency === 'coin'"
            class="price"
            >{{ currentSku.displayPriceObj.coinAmount | formatPrice
            }}<span class="detail-right__price__unit">{{
              currentSku.displayPriceObj.coinName
            }}</span>
          </span>
        </div>
        <div class="pull-right study-num">
          {{ goods.product.target.studentNum }}人在学
        </div>
      </div>
    </div>
    <div v-if="!goods.discount" class="detail-info__price">
      <div class="clearfix">
        <div class="pull-left">
          价格
          <span
            v-if="currentSku.displayPriceObj.currency === 'RMB'"
            class="price"
            >{{ currentSku.displayPriceObj.amount | formatPrice }}元
          </span>
          <span
            v-if="currentSku.displayPriceObj.currency === 'coin'"
            class="price"
            >{{ currentSku.displayPriceObj.coinAmount | formatPrice
            }}<span class="detail-right__price__unit">{{
              currentSku.displayPriceObj.coinName
            }}</span></span
          >
        </div>
        <div class="pull-right study-num">
          {{ goods.product.target.studentNum }}人在学
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    goods: {
      type: Object,
      default: () => {},
    },
    currentSku: {
      type: Object,
      default: () => {},
    },
  },
  filters: {
    formatPrice(input) {
      return (Math.round(input * 100) / 100).toFixed(2);
    },
  },
};
</script>
