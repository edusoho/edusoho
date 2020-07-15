<template>
  <div class="detail-info" v-if="goods.id">
    <p class="detail-info__title text-overflow">{{ goods.title }}</p>

    <div class="detail-info__price">
      <div class="clearfix">
        <div class="pull-left">
          优惠价
          <span class="price">{{ currentSku.price }}</span>
          元
        </div>
        <div class="pull-right study-num">
          {{ currentSku.maxJoinNum }}人在学
        </div>
      </div>
      <p class="original-price">
        <s
          >价格&nbsp;<span>{{ currentSku.price }}</span
          >元</s
        >
      </p>
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
};
</script>
