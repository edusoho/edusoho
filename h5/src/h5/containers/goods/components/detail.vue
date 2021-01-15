<template>
  <div class="detail-info" v-if="goods.id">
    <p class="detail-info__title" :class="isShare && 'detail-info__title--pr'">
      <span class="certificate-icon" v-if="currentSku.hasCertificate">证</span
      >{{ goods.title }}
      <i
        class="iconfont icon-fenxiang goods-share"
        @click="onShare"
        v-if="isShare"
      ></i>
    </p>

    <div
      v-if="goods.discount && currentSku.displayPrice != 0"
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
          <i class="iconfont icon-renqi"></i>
          {{ goods.product.target.studentNum }}人
        </div>
      </div>
    </div>
    <div
      v-if="!goods.discount || currentSku.displayPrice == 0"
      class="detail-info__price"
    >
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
        <div
          v-if="goodsSetting.show_number_data === 'join'"
          class="pull-right study-num"
        >
          <i class="iconfont icon-people"></i>
          {{ goods.product.target.studentNum }}人
        </div>
        <div
          v-else-if="goodsSetting.show_number_data === 'visitor'"
          class="pull-right study-num"
        >
          <i class="iconfont icon-visibility"></i>
          {{ goods.hitNum }}人
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
    goodsSetting: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      isShare: false, // 是否显示分享按钮
    };
  },
  filters: {
    formatPrice(input) {
      return (Math.round(input * 100) / 100).toFixed(2);
    },
  },
  methods: {
    onShare() {
      // 分享
    },
  },
};
</script>
