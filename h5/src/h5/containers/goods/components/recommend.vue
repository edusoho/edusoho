<template>
  <div class="info-learn">
    <div class="info-learn__header clearfix">
      <slot class="header-title pull-left" name="title"></slot>
      <span class="header-more pull-right" @click="onMore"
        >{{ $t('enter.more') }}<i class="iconfont icon-About"></i
      ></span>
    </div>
    <div class="info-learn__body">
      <template v-if="recommendGoods.length">
        <div
          class="body-item"
          v-for="goods in recommendGoods"
          :key="goods.id"
          @click="onJump(goods.id)"
        >
          <div class="body-item__img">
            <img :src="goods.images.large" alt="" />
          </div>
          <div class="body-item__content">
            <p class="content-title text-overflow">{{ goods.title }}</p>
            <p
              class="content-price text-overflow"
              :class="{
                'is-free': Number(goods.minDisplayPriceObj.amount) == 0,
              }"
              v-if="
                goods.minDisplayPriceObj.amount ==
                  goods.maxDisplayPriceObj.amount
              "
            >
              {{
                Number(goods.maxDisplayPriceObj.amount) == 0
                  ? $t('goods.free')
                  : goods.minDisplayPriceObj.currency === 'RMB'
                  ? `${goods.maxDisplayPriceObj.amount}${$t('goods.cny')}`
                  : goods.minDisplayPriceObj.coinAmount +
                    goods.minDisplayPriceObj.coinName
              }}
            </p>
            <p class="content-price text-overflow" v-else>
              <span
                v-if="goods.minDisplayPriceObj.currency === 'RMB'"
                class="price"
                >{{ goods.minDisplayPriceObj.amount | formatPrice }}{{ $t('goods.cny') }}</span
              >
              <span
                v-if="goods.minDisplayPriceObj.currency === 'coin'"
                class="price"
                >{{ goods.minDisplayPriceObj.coinAmount | formatPrice }}
              </span>
              <span
                v-if="goods.minDisplayPriceObj.currency === 'coin'"
                class="detail-right__price__unit"
              >
                {{ goods.minDisplayPriceObj.coinName }}
              </span>
            </p>
          </div>
        </div>
      </template>
      <div v-else>{{ $t('goods.thereAreNoRecommendedProductsYet') }}</div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    recommendGoods: {
      type: Array,
      default: () => [],
    },
    goods: {
      type: Object,
      default: () => {},
    },
  },
  methods: {
    onJump(id) {
      if (id == this.$route.params.id) return;
      this.$router.push({
        path: `/goods/${id}/show`,
      });
    },
    onMore() {
      this.$router.push({
        name: this.goods.type === 'course' ? 'more_course' : 'more_class',
      });
    },
  },
  filters: {
    formatPrice(input) {
      return (Math.round(input * 100) / 100).toFixed(2);
    },
  },
};
</script>
