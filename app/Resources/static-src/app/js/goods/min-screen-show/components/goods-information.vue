<template>
  <div class="goods-information">
    <p class="goods-information__title text-overflow">{{ details.title }}</p>
    <div class="goods-information__price">
      <div class="clearfix">
        <div class="pull-left">
          优惠价
          <span class="price">{{ currentPlan.price }}</span>
          元
        </div>
        <div class="pull-right study-num">{{ currentPlan.joinedNum }}人在学</div>
      </div>
      <p class="original-price"><s>价格&nbsp;<span>399.00</span>元</s></p>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    details: {
      type: Object,
      default: () => {}
    },
    currentPlan: {
      type: Object,
      default: () => {}
    }
  }
}
</script>