<template>
  <div class="column-item pull-left">
    <div class="column-item__img">
      <img :src="item.cover.middle" alt="">
    </div>
    <div class="column-item__info">
      <div class="column-item__title text-overflow">{{ item.title || item.courseSetTitle }}</div>
      <div class="column-item__price">{{ item.price }} {{ 'cny' | trans }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ItemBankListItem',

  props: {
    item: {
      type: Object,
      required: true
    }
  }
}
</script>

<style lang="less" scoped>
.column-item {
  overflow: hidden;
  margin-top: 16px;
  width: 164px;
  border-radius: 8px;
  box-shadow: 0px 2px 16px 0px rgba(0, 0, 0, 0.05);

  &:nth-of-type(odd) {
    margin-right: 15px;
  }

  &__img {
    width: 100%;
    height: 93px;
    background-color: #eee;

    img {
      width: 100%;
      height: 100%;
    }
  }

  &__info {
    position: relative;
    padding: 8px;
    width: 100%;
    height: 80px;
    background-color: #fff;
  }

  &__title {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    line-height: 20px;
  }

  &__price {
    position: absolute;
    right: 8px;
    bottom: 8px;
    font-size: 12px;
    font-weight: 500;
    line-height: 16px;
    color: #FF7A34;
  }
}
</style>
